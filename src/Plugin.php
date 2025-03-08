<?php

namespace craigclement\craftbrokenlinks;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\Cp;
use yii\base\Event;
use craigclement\craftbrokenlinks\services\BrokenLinksService;

class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';

    public function init(): void
    {
        parent::init();

        // Register the BrokenLinksService correctly
        $this->setComponents([
            'brokenLinksService' => BrokenLinksService::class,
        ]);

        // Register CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['brokenlinks'] = 'brokenlinks/broken-links/index';
                $event->rules['brokenlinks/get-results'] = 'brokenlinks/broken-links/get-results';
                $event->rules['brokenlinks/run-crawl'] = 'brokenlinks/broken-links/run-crawl';
            }
        );

        // Register navigation in CP
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

        Craft::info('Broken Links plugin loaded', __METHOD__);
    }
}

