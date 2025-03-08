<?php

namespace craigclement\craftbrokenlinks\controllers;

use craft\web\Controller;
use yii\web\Response;
use Craft;
use craigclement\craftbrokenlinks\jobs\GenerateSitemapJob;

class BrokenLinksController extends Controller
{
    protected array|int|bool $allowAnonymous = false; // Restrict access to logged-in users

    /**
     * **Displays the main plugin page in the Control Panel.**
     */
    public function actionIndex(): Response
    {
        return $this->renderTemplate('brokenlinks/index');
    }

    /**
     * **Start the scan by adding GenerateSitemapJob to the queue.**
     */
    public function actionRunCrawl(): Response
    {
        Craft::info("Starting broken link scan request.", __METHOD__);

        // Queue the first job (Generating Sitemap)
        $queue = Craft::$app->queue;
        $jobId = $queue->push(new GenerateSitemapJob());

        if ($jobId) {
            Craft::info("GenerateSitemapJob added to queue successfully.", __METHOD__);

            return $this->asJson([
                'success' => true,
                'message' => 'Scan started! Please wait for results.',
                'jobId' => $jobId
            ]);
        }

        Craft::error("Failed to add GenerateSitemapJob to queue.", __METHOD__);

        return $this->asJson([
            'success' => false,
            'message' => 'Failed to start the scan. Try again later.'
        ]);
    }

    /**
     * **Fetch stored broken links from cache or database.**
     */
    public function actionGetResults(): Response
    {
        $results = Craft::$app->cache->get('brokenLinks_results');
    
        if ($results === false || empty($results)) {
            return $this->asJson([
                'success' => true, // ✅ Keep success true, just no results yet
                'message' => 'No results yet. Please check back later.',
                'data' => [] 
            ]);
        }
    
        return $this->asJson([
            'success' => true,
            'data' => $results
        ]);
    }
    
}
