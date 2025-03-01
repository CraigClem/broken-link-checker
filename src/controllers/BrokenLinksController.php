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
        // Push the job to the queue
        Craft::$app->queue->push(new CheckBrokenLinksJob());

        return $this->asJson([
            'success' => true,
            'message' => 'Crawl has started. You can monitor progress in Utilities > Queue Manager.',
        ]);
    }
}
