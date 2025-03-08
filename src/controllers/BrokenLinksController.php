<?php

// Define the namespace for the controller
namespace craigclement\craftbrokenlinks\controllers;

// Import necessary Craft CMS and Yii components
use craft\web\Controller;
use craigclement\craftbrokenlinks\Plugin;
use Craft;

// Define the main controller class for managing broken links
class BrokenLinksController extends Controller
{
    // Allow anonymous access to all actions in this controller
    protected array|int|bool $allowAnonymous = true;

    /**
     * **Index Action: Displays the main plugin page in the Control Panel.**
     * 
     * This action is triggered when visiting the `/brokenlinks` route in the CP.
     * 
     * @return string The rendered template.
     */
    public function actionIndex(): string
    {
        // Render the `brokenlinks/index` template (Twig file)
        return $this->renderTemplate('brokenlinks/index');
    }


    /**
 * **Run Crawl Action: Executes the link checking process asynchronously using a queue.**
 * 
 * - This action is triggered when accessing `/brokenlinks/run-crawl`.
 * - It fetches all site URLs and batches them into jobs for asynchronous processing.
 * - The queue jobs will check links in smaller batches to prevent timeouts.
 */
public function actionRunCrawl()
{
    // Set response format to JSON
    Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;

    // ✅ Retrieve the registered service instance properly from Craft's plugin system
    $service = Plugin::getInstance()->get('brokenLinksService');

    // ✅ Fetch all URLs from the site
    $urls = $service->getAllSiteUrls();

    // 🚨 Handle case where no URLs are found
    if (empty($urls)) {
        return $this->asJson([
            'success' => false,
            'message' => 'No URLs found for checking.',
        ]);
    }

    // ✅ Define batch size (number of URLs checked per job)
    $batchSize = 10; // Adjust as needed
    $batches = array_chunk($urls, $batchSize); // Splits URLs into smaller chunks

    // ✅ Push each batch as a separate queue job for asynchronous processing
    foreach ($batches as $batch) {
        Craft::$app->queue->push(new \craigclement\craftbrokenlinks\jobs\CheckBrokenLinksJob([
            'urls' => $batch // Each job processes one batch of URLs
        ]));
    }

    // ✅ Return JSON response confirming jobs were added to queue
    return $this->asJson([
        'success' => true,
        'message' => count($batches) . ' jobs added to queue.',
        'data' => [],
    ]);
}


        /**
     * **Queue Test Job Action: Confirms queue processing works.**
     * 
     * This action adds a simple test job to the queue.
     */
    public function actionQueueTestJob()
    {
        Craft::$app->queue->push(new \craigclement\craftbrokenlinks\jobs\TestJob());

        return $this->asJson([
            'success' => true,
            'message' => 'Test job added to the queue.',
        ]);
    }
    
}
