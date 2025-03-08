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
                }
            } catch (\Throwable $e) {
                $brokenLinks[] = $url;
            }
        }

        // Store broken links in cache for retrieval
        Craft::$app->cache->set('broken_links_results', $brokenLinks, 3600);

        Craft::info('Batch checked ' . count($this->urls) . ' URLs.', __METHOD__);
    }

    protected function defaultDescription(): string
    {
        return 'Checking batch of links for broken URLs';
    }
}
