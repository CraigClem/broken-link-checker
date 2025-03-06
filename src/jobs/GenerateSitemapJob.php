<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use craigclement\craftbrokenlinks\services\BrokenLinksService;
use craigclement\craftbrokenlinks\jobs\CheckBrokenLinksJob; // ✅ Ensure job reference is correct

class GenerateSitemapJob extends BaseJob
{
    public function execute($queue): void
    {
        // ✅ Get the plugin service correctly
        $service = Craft::$app->get('brokenLinksService');

        if (!$service instanceof BrokenLinksService) {
            Craft::error('BrokenLinksService not found', __METHOD__);
            return;
        }

        // ✅ Get all site URLs
        $urls = $service->fetchAllSiteUrls();

        if (empty($urls)) {
            Craft::warning('No URLs found for sitemap generation.', __METHOD__);
            return;
        }

        // ✅ Store in cache for later processing
        Craft::$app->cache->set('brokenLinks_urls', $urls, 3600); // Cache for 1 hour

        // ✅ Log before adding the next job
        Craft::info('Sitemap generated successfully. Pushing CheckBrokenLinksJob to queue.', __METHOD__);

        // ✅ Push the "Check Broken Links" job
        Craft::$app->queue->push(new CheckBrokenLinksJob());
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Generating Sitemap for Broken Links Checker');
    }
}
