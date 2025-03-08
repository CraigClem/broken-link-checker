<?php

namespace craigclement\craftbrokenlinks\services;

// Import required classes
use Craft; // Craft CMS core class
use yii\base\Component; // Base class for services
use craigclement\craftbrokenlinks\jobs\GenerateSitemapJob; // Queue job for generating a sitemap
use craigclement\craftbrokenlinks\jobs\CheckBrokenLinksJob; // Queue job for checking broken links

/**
 * Service to handle broken link detection in Craft CMS.
 */
class BrokenLinksService extends Component
{
    /**
     * Starts the crawl process by adding a GenerateSitemapJob to the queue.
     */
    public function queueCrawl(): void
    {
        Craft::info("Adding GenerateSitemapJob to queue", __METHOD__);

        // Push a job to generate the sitemap (collect all entry URLs)
        Craft::$app->queue->push(new GenerateSitemapJob());

        Craft::info("GenerateSitemapJob added successfully.", __METHOD__);
    }

    /**
     * Processes entries and queues batch jobs for checking broken links.
     *
     * @param array $urls List of site URLs from the generated sitemap.
     */
    public function queueCheckBrokenLinks(array $urls): void
    {
        $batchSize = 5; // Process 5 entries per batch (can be configurable)
        $batches = array_chunk($urls, $batchSize);

        Craft::info("Queueing " . count($batches) . " jobs for broken link checking.", __METHOD__);

        foreach ($batches as $batch) {
            Craft::$app->queue->push(new CheckBrokenLinksJob([
                'urls' => $batch,
            ]));
        }
    }
}
