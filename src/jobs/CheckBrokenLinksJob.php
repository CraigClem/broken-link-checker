<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use craigclement\craftbrokenlinks\services\BrokenLinksService;

class CheckBrokenLinksJob extends BaseJob
{
    public function execute($queue): void
    {
        // ✅ Retrieve the service properly from the registered service in Plugin.php
        $service = Craft::$app->get('brokenLinksService');

        if (!$service instanceof BrokenLinksService) {
            Craft::error('BrokenLinksService not found in Craft::$app', __METHOD__);
            return;
        }

        // ✅ Get stored URLs from cache, ensuring it's always an array
        $urls = Craft::$app->cache->get('brokenLinks_urls');

        if (!is_array($urls)) {
            Craft::warning('No URLs found in cache. Skipping broken links check.', __METHOD__);
            return; // Exit early if no URLs are available
        }

        // ✅ Process in batches to prevent timeouts
        $batchSize = 10; // Configurable later
        $chunks = array_chunk($urls, $batchSize);

        foreach ($chunks as $batch) {
            if (!empty($batch)) {
                $service->checkUrlsForBrokenLinks($batch);
            }
        }

        // ✅ Clear cache after processing
        Craft::$app->cache->delete('brokenLinks_urls');

        Craft::info('Broken Links check completed successfully.', __METHOD__);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Checking Broken Links');
    }
}
