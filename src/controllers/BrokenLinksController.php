<?php

// Define the namespace for the controller
namespace craigclement\craftbrokenlinks\controllers;

// Import necessary Craft CMS and Yii components
use craft\web\Controller;
use craigclement\craftbrokenlinks\services\BrokenLinksService;
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
     * **Run Crawl Action: Executes the link crawling process.**
     * 
     * This action is triggered when accessing the `/brokenlinks/run-crawl` route.
     * It returns the results as a JSON response.
     */
    public function actionRunCrawl()
    {
        // Set the response format to JSON
        Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
        // Get the base URL for the primary site
        $baseUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    
        // Allow an override from query params (optional)
        $baseUrl = Craft::$app->request->getQueryParam('url', $baseUrl);
    
        // Validate the URL
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Invalid URL provided.',
            ], 400);
        }
    
        try {
            // Create an instance of the BrokenLinksService
            $service = new BrokenLinksService();
    
            // Crawl the site and collect broken links
            $brokenLinks = $service->crawlSite($baseUrl);
    
            // Return a successful JSON response with the results
            return $this->asJson([
                'success' => true,
                'message' => 'Crawl completed successfully.',
                'data' => $brokenLinks,
            ]);
        } catch (\Throwable $e) {
            // Log any errors encountered during the crawl
            Craft::error("Error during crawl of {$baseUrl}: " . $e->getMessage(), __METHOD__);
    
            // Return an error response as JSON
            return $this->asJson([
                'success' => false,
                'message' => 'An error occurred during the crawl.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
