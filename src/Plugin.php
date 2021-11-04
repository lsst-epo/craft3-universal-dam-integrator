<?php
namespace rosas\dam;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Volumes;
use yii\base\Event;
use craft\web\twig\variables\CraftVariable;
use rosas\dam\volumes\DAMVolume;
use rosas\dam\services\Assets;
use yii\base\Behavior;

class Plugin extends \craft\base\Plugin
{
    public static $plugin;

    public $hasCpSettings = true;

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