<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use GuzzleHttp\Client;

/**
 * Job to check a batch of URLs for broken links.
 */
class CheckBrokenLinksJob extends BaseJob
{
    public array $urls = []; // URLs to check

    public function execute($queue): void
    {
        $client = new Client(['timeout' => 5]);
        $brokenLinks = [];

        foreach ($this->urls as $url) {
            try {
                $response = $client->head($url);

                if ($response->getStatusCode() >= 400) {
                    $brokenLinks[] = [
                        'url' => $url,
                        'status' => 'Broken (' . $response->getStatusCode() . ')'
                    ];
                }
            } catch (\Throwable $e) {
                $brokenLinks[] = [
                    'url' => $url,
                    'status' => 'Unreachable',
                    'error' => $e->getMessage()
                ];
            }
        }

        // Store results in cache
        Craft::$app->cache->set('brokenLinks_results', $brokenLinks, 3600);
    }

    protected function defaultDescription(): string
    {
        return 'Checking broken links';
    }
}
