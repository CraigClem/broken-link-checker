<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use GuzzleHttp\Client;

class CheckBrokenLinksJob extends BaseJob
{
    public function execute($queue): void
    {
        Craft::info("Checking broken links...", __METHOD__);

        $urls = Craft::$app->cache->get('brokenLinks_urls') ?? [];

        if (empty($urls)) {
            Craft::warning('No URLs stored in cache.', __METHOD__);
            return;
        }

        $client = new Client(['timeout' => 5]);
        $brokenLinks = [];

        foreach ($urls as $url) {
            try {
                $response = $client->head($url);
                if ($response->getStatusCode() >= 400) {
                    $brokenLinks[] = ['url' => $url, 'status' => $response->getStatusCode()];
                }
            } catch (\Throwable $e) {
                $brokenLinks[] = ['url' => $url, 'status' => 'Unreachable'];
            }
        }

        Craft::$app->cache->set('brokenLinks_results', $brokenLinks, 3600);

        Craft::info('Broken link check completed.', __METHOD__);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Checking for Broken Links');
    }
}
