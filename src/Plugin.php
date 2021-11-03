<?php
namespace rosas\dam;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Volumes;
use yii\base\Event;
use rosas\dam\volumes\DAMVolume;
use rosas\dam\services\Assets;

class Plugin extends \craft\base\Plugin
{
    public static $plugin;

    public $hasCpSettings = true;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'assets' => \rosas\dam\services\Assets::class,
        ]);
        //$this->assets->testMetaSave();
    }

    protected function settingsHtml() {
        // Lazy testing mechanism for now, just trigger metaSave upon retrieving this plugin's settings page
        $this->assets->testMetaSave();

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