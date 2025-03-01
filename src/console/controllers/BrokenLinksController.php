<?php

namespace craigclement\craftbrokenlinks\console\controllers;

use craft\console\Controller;
use craigclement\craftbrokenlinks\jobs\CheckBrokenLinksJob;
use Craft;

class BrokenLinksController extends Controller
{
    public function actionRun()
    {
        Craft::$app->queue->push(new CheckBrokenLinksJob());
        echo "✅ Crawl started! Check progress in Queue Manager or run 'php craft queue/list'.\n";
    }
}
