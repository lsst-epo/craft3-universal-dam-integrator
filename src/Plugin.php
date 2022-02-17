<?php
namespace rosas\dam;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Volumes;
use yii\base\Event;
use craft\web\twig\variables\CraftVariable;
use rosas\dam\volumes\DAMVolume;
use rosas\dam\services\Assets;
use craft\services\Assets as CraftAssets;
use yii\base\Behavior;
use craft\events\GetAssetThumbUrlEvent;
use craft\events\GetAssetUrlEvent;

class Plugin extends \craft\base\Plugin
{
    public static $plugin;

    public $hasCpSettings = true;

    public function __construct($id, $parent = null, array $config = []) {
        $config["components"] = [
            'assets' => Assets::class
        ];
        parent::__construct($id, $parent, $config);
    }

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Bind Assets service to be scoped to this plugin
        $this->setComponents([
            'assets' => \rosas\dam\services\Assets::class,
        ]);
        
        // Add a tag for the settings page for testing services
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $e) {
            /** @var CraftVariable $variable */
            $tag = $e->sender;
    
            // Attach a service:
            $tag->set('metaSave', services\Assets::class);
        });

        // Handler: Assets::EVENT_GET_ASSET_THUMB_URL
        Event::on(
            \craft\services\Assets::class,
            \craft\services\Assets::EVENT_GET_ASSET_THUMB_URL,
            function (GetAssetThumbUrlEvent $event) {
                Craft::debug(
                    '\craft\services\Assets::EVENT_GET_ASSET_THUMB_URL',
                    __METHOD__
                );
                // Return the URL to the asset URL or null to let Craft handle it
                $event->url = Plugin::$plugin->assets->handleGetAssetThumbUrlEvent($event);
            }
        );

        // Register DAM remote volume type
        Event::on(
            Volumes::class,
            Volumes::EVENT_REGISTER_VOLUME_TYPES,
                function(RegisterComponentTypesEvent $event) {
                $event->types[] = DAMVolume::class;
            }
        );

        // Register getAssetUrl event  
        Event::on(
            CraftAssets::class,
            CraftAssets::EVENT_GET_ASSET_URL,
                function(GetAssetUrlEvent $event) {
                    $event->url = Plugin::$plugin->assets->getUrl($event);
                }
            );
    }

    protected function settingsHtml() {
        return \Craft::$app->getView()->renderTemplate(
            'universal-dam-integrator/settings',
            [ 'settings' => $this->getSettings() ]
        );
    }

    protected function createSettingsModel()
    {
        return new \rosas\dam\models\Settings();
    }
}