<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use craigclement\craftbrokenlinks\jobs\CheckBrokenLinksJob;

class GenerateSitemapJob extends BaseJob
{
    public function execute($queue): void
    {
        Craft::info("Generating Sitemap for Broken Links", __METHOD__);

        $service = Craft::$app->get('brokenLinksService');

        if (!$service) {
            Craft::error('BrokenLinksService not found', __METHOD__);
            return;
        }

        $urls = $service->fetchAllSiteUrls();

        if (empty($urls)) {
            Craft::warning('No URLs found for sitemap generation.', __METHOD__);
            return;
        }

        Craft::$app->cache->set('brokenLinks_urls', $urls, 3600);

        Craft::info('Sitemap generated successfully. Pushing CheckBrokenLinksJob to queue.', __METHOD__);

        Craft::$app->queue->push(new CheckBrokenLinksJob());
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Generating Sitemap for Broken Links Checker');
    }
}
