<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use craigclement\craftbrokenlinks\services\BrokenLinksService;

class GenerateSitemapJob extends BaseJob
{
    public function execute($queue): void
    {
        $service = Craft::$app->getModule('brokenlinks')->get('brokenLinksService');

        // Get all site URLs
        $urls = $service->fetchAllSiteUrls(); // A new method we'll create

        // Store in a Craft cache or database table for next job
        Craft::$app->cache->set('brokenLinks_urls', $urls, 3600); // Cache for 1 hour

        // Add the "Check Broken Links" job to queue
        Craft::$app->queue->push(new CheckBrokenLinksJob());
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Generating Sitemap for Broken Links Checker');
    }
}
