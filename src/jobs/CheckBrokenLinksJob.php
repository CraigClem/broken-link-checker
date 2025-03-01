<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use craigclement\craftbrokenlinks\services\BrokenLinksService;

class CheckBrokenLinksJob extends BaseJob
{
    public function execute($queue): void
    {
        $service = Craft::$app->getModule('brokenlinks')->get('brokenLinksService');

        // Get stored URLs
        $urls = Craft::$app->cache->get('brokenLinks_urls') ?? [];

        // Process in batches (to prevent large jobs)
        $batchSize = 10; // Configurable later
        $chunks = array_chunk($urls, $batchSize);

        foreach ($chunks as $batch) {
            $service->checkUrlsForBrokenLinks($batch);
        }

        // Remove cache after processing
        Craft::$app->cache->delete('brokenLinks_urls');
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Checking Broken Links');
    }
}
