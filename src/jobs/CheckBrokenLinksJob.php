<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use GuzzleHttp\Client;

class CheckBrokenLinksJob extends BaseJob
{
    public array $urls = [];

    public function execute($queue): void
    {
        $client = new Client(['timeout' => 5]);
        $brokenLinks = [];

        foreach ($this->urls as $url) {
            try {
                $response = $client->head($url);
                if ($response->getStatusCode() >= 400) {
                    $brokenLinks[] = $url;
                    Craft::info("Broken link detected: $url", __METHOD__);
                }
            } catch (\Throwable $e) {
                $brokenLinks[] = $url;
                Craft::info("Request failed, marking as broken: $url", __METHOD__);
            }
        }

        // Log broken links before caching
        if (!empty($brokenLinks)) {
            Craft::info("Broken links found: " . json_encode($brokenLinks), __METHOD__);
        } else {
            Craft::info("No broken links found in this batch", __METHOD__);
        }

        // Store broken links in cache (with corrected key)
        Craft::$app->cache->set('brokenLinks_results', $brokenLinks, 3600);
        Craft::info('Stored broken links in cache', __METHOD__);

        Craft::info('Batch checked ' . count($this->urls) . ' URLs.', __METHOD__);
    }

    protected function defaultDescription(): string
    {
        return 'Checking batch of links for broken URLs';
    }
}
