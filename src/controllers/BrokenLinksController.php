<?php

namespace craigclement\craftbrokenlinks\controllers;

use craft\web\Controller;
use craigclement\craftbrokenlinks\jobs\CheckBrokenLinksJob;
use Craft;

class BrokenLinksController extends Controller
{
    protected array|int|bool $allowAnonymous = false; // Admin-only for security

    /**
     * **Display Plugin Dashboard (Control Panel UI)**
     */
    public function actionIndex()
    {
        return $this->renderTemplate('brokenlinks/index');
    }

    /**
     * **Start the crawl process using Craft's queue system**
     * 
     * Instead of running the crawl instantly, this method adds a job to the queue.
     */
    public function actionRunCrawl()
    {
        $queue = Craft::$app->queue;
    
        // ✅ Log before adding to queue
        Craft::info("Adding GenerateSitemapJob to queue", __METHOD__);
    
        // ✅ Add the first job (Generate Sitemap)
        $jobId = $queue->push(new \craigclement\craftbrokenlinks\jobs\GenerateSitemapJob());
    
        if (!$jobId) {
            Craft::error("Failed to add GenerateSitemapJob", __METHOD__);
            return $this->asJson([
                'success' => false,
                'message' => 'Failed to add job to queue.',
            ]);
        }
    
        Craft::info("Successfully added GenerateSitemapJob with ID: {$jobId}", __METHOD__);
    
        return $this->asJson([
            'success' => true,
            'message' => 'Crawl has started. You can monitor progress in Utilities > Queue Manager.',
            'data' => [],
        ]);
    }
    
}
