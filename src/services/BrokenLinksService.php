<?php

namespace craigclement\craftbrokenlinks\services;

use Craft;
use GuzzleHttp\Client;
use yii\base\Component;
use craft\queue\Queue;
use craigclement\craftbrokenlinks\jobs\CheckBrokenLinksJob;

/**
 * Service for handling broken link detection in Craft CMS.
 */
class BrokenLinksService extends Component
{
    /**
     * Fetch all URLs from all sites in Craft CMS.
     *
     * @return array List of URLs.
     */
    public function fetchAllSiteUrls(): array
    {
        $urls = [];

        // Get all sites in the system
        $sites = Craft::$app->getSites()->getAllSites();

        foreach ($sites as $site) {
            // Get all entries for this site
            $entries = Craft::$app->elements->createElementQuery(\craft\elements\Entry::class)
                ->siteId($site->id)
                ->all();

            foreach ($entries as $entry) {
                $urls[] = $entry->getUrl();
            }
        }

        return array_filter($urls); // Remove any empty values
    }

    /**
     * Add entries to the queue to be processed in batches.
     */
    public function queueCrawl(): void
    {
        $entries = Craft::$app->elements->createElementQuery(\craft\elements\Entry::class)
            ->with(['*']) // Load all relations
            ->all();

        if (!$entries) {
            Craft::info("No entries found for crawling.", __METHOD__);
            return;
        }

        $batchSize = 5; // Process 5 entries per job (can be configurable)
        $batches = array_chunk($entries, $batchSize);

        foreach ($batches as $batch) {
            Craft::$app->queue->push(new CheckBrokenLinksJob([
                'entries' => $batch,
            ]));
        }

        Craft::info("Queued " . count($batches) . " jobs for crawling broken links.", __METHOD__);
    }

    /**
     * Process a batch of entries to find broken links.
     *
     * @param array $entries Batch of Craft entries to scan.
     * @return array List of broken links.
     */
    public function processEntries(array $entries): array
    {
        $client = new Client(['timeout' => 5]);
        $brokenLinks = [];
        $visitedUrls = [];

        foreach ($entries as $entry) {
            $url = $entry->getUrl();

            if (!$url || in_array($url, $visitedUrls)) {
                continue;
            }

            $visitedUrls[] = $url;

            // Process this entry’s fields for links
            $this->processEntryLinks($client, $entry, $url, $brokenLinks);
        }

        return $brokenLinks;
    }

    /**
     * Process a single entry's fields to extract and check links.
     */
    private function processEntryLinks(Client $client, $entry, string $pageUrl, array &$brokenLinks): void
    {
        // Get all fields dynamically
        $fieldLayout = $entry->getFieldLayout();
        if (!$fieldLayout) {
            return;
        }

        foreach ($fieldLayout->getCustomFields() as $field) {
            $fieldHandle = $field->handle;
            $fieldContent = $entry->getFieldValue($fieldHandle);

            if (!$fieldContent) {
                continue;
            }

            // Extract links from field content
            preg_match_all('/<a\s+[^>]*href="([^"]*)"/i', (string)$fieldContent, $matches);
            $urls = $matches[1] ?? [];

            foreach ($urls as $url) {
                $this->checkLink($client, $url, $entry, $fieldHandle, $pageUrl, $brokenLinks);
            }
        }
    }

    /**
     * Check if a link is broken and add it to the list if needed.
     */
    private function checkLink(Client $client, string $link, $entry, string $field, string $pageUrl, array &$brokenLinks): void
    {
        $absoluteUrl = $this->resolveUrl($pageUrl, $link);

        if (!preg_match('/^https?:\/\//', $absoluteUrl)) {
            return;
        }

        try {
            $response = $client->head($absoluteUrl);

            if ($response->getStatusCode() >= 400) {
                $brokenLinks[] = [
                    'url' => $absoluteUrl,
                    'status' => 'Broken (' . $response->getStatusCode() . ')',
                    'entryId' => $entry->id,
                    'entryTitle' => $entry->title ?? $entry->slug,
                    'entryUrl' => $entry->getCpEditUrl(),
                    'field' => $field,
                    'pageUrl' => $pageUrl,
                ];
            }
        } catch (\Throwable $e) {
            $brokenLinks[] = [
                'url' => $absoluteUrl,
                'status' => 'Unreachable',
                'error' => $e->getMessage(),
                'entryId' => $entry->id,
                'entryTitle' => $entry->title ?? $entry->slug,
                'entryUrl' => $entry->getCpEditUrl(),
                'field' => $field,
                'pageUrl' => $pageUrl,
            ];
        }
    }

    /**
     * Resolve a relative URL into an absolute URL.
     */
    private function resolveUrl(string $baseUrl, string $relativeUrl): string
    {
        return (string) \GuzzleHttp\Psr7\UriResolver::resolve(
            new \GuzzleHttp\Psr7\Uri($baseUrl),
            new \GuzzleHttp\Psr7\Uri($relativeUrl)
        );
    }
}
