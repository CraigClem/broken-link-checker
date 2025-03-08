<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use craigclement\craftbrokenlinks\jobs\CheckBrokenLinksJob;

/**
 * Job to generate a sitemap of all site URLs for broken link checking.
 */
class GenerateSitemapJob extends BaseJob
{
    public function execute($queue): void
    {
        Craft::info("Running GenerateSitemapJob...", __METHOD__);

        // Get all entries in the CMS
        $entries = Craft::$app->elements->createElementQuery(\craft\elements\Entry::class)
            ->all();

        if (!$entries) {
            Craft::warning("No entries found for sitemap generation.", __METHOD__);
            return;
        }

        // Extract URLs from entries
        $urls = [];
        foreach ($entries as $entry) {
            if ($entry->getUrl()) {
                $urls[] = $entry->getUrl();
            }
        }

        // Store URLs in cache for 1 hour
        Craft::$app->cache->set('brokenLinks_urls', $urls, 3600);

        Craft::info("Stored " . count($urls) . " URLs in cache. Adding CheckBrokenLinksJob to queue.", __METHOD__);

        // Push the CheckBrokenLinksJob
        Craft::$app->queue->push(new CheckBrokenLinksJob());
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Generating Sitemap for Broken Links Checker');
    }
}
