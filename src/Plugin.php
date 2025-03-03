<?php

// Declare the namespace for this plugin
namespace craigclement\craftbrokenlinks;

// Import necessary Craft CMS classes and components
use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\Cp;
use yii\base\Event;

/**
 * Broken Links Plugin
 */
class Plugin extends BasePlugin
{
    // Define the plugin's schema version for migrations and updates
    public string $schemaVersion = '1.0.0';

    /**
     * Initialize the plugin.
     */
    public function init(): void
    {
        // Call the parent class's initialization method
        parent::init();

        // ✅ Register the service so it can be accessed globally
        Craft::$app->setComponents([
            'brokenLinksService' => [
                'class' => \craigclement\craftbrokenlinks\services\BrokenLinksService::class,
            ],
        ]);

        // ✅ Register a Control Panel (CP) route for the plugin's index page
        Event::on(
            UrlManager::class,                       
            UrlManager::EVENT_REGISTER_CP_URL_RULES, 
            function (RegisterUrlRulesEvent $event) {
                $event->rules['brokenlinks'] = 'brokenlinks/broken-links/index';
            }
        );

        // ✅ Register a front-end route for the crawling action
        Event::on(
            UrlManager::class,                        
            UrlManager::EVENT_REGISTER_SITE_URL_RULES, 
            function (RegisterUrlRulesEvent $event) {
                $event->rules['brokenlinks/run-crawl'] = 'brokenlinks/broken-links/run-crawl';
            }
        );

        // ✅ Add a navigation item to the Craft Control Panel
        Event::on(
            Cp::class,                            
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,     
            function (RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'url' => 'brokenlinks',            
                    'label' => 'Broken Links',        
                    'icon' => '@appicons/link.svg',   
                ];
            }
        );

        // Log that the plugin has loaded successfully
        Craft::info('Broken Links plugin loaded', __METHOD__);
    }
}
