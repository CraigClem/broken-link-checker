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
    
        // Log the final list of broken links before caching
        Craft::info("Final broken links: " . json_encode($brokenLinks), __METHOD__);
    
        // Store broken links in cache
        $cacheSet = Craft::$app->cache->set('brokenLinks_results', $brokenLinks, 3600);
        
        if ($cacheSet) {
            Craft::info("✅ Successfully stored broken links in cache.", __METHOD__);
        } else {
            Craft::warning("❌ Failed to store broken links in cache.", __METHOD__);
        }
    
        Craft::info('Batch checked ' . count($this->urls) . ' URLs.', __METHOD__);
    }
    

    protected function defaultDescription(): string
    {
        return 'Checking batch of links for broken URLs';
    }
}
