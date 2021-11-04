<?php
namespace rosas\dam\services;

use Craft;
use yii\base\Component;
use craft\elements\Asset;
use rosas\dam\services\Elements;

class Assets extends Component
{
    public function init() {
        parent::init();
    }

    public function testMetaSave() {
        // Test saving a brand new asset
        $newAsset = new Asset();
        $newAsset->avoidFilenameConflicts = true;
        $newAsset->setScenario(Asset::SCENARIO_CREATE);


        $newAsset->filename = "this-came-from-the-plugin.png";
        $newAsset->folderId = 17;
        $newAsset->setVolumeId(6); 
        $newAsset->kind = "image";
        $newAsset->firstSave = true;
        $newAsset->propagateAll = false; //changed from true for debugging purposes



        $elements = new Elements();
        // Don't validate required custom fields
        $success = $elements->saveElement($newAsset, false);
        echo "\n\n Rosas - success : " . $success . "\n\n";


    public function testAssetQuery() {
        return Craft::$app->getElements()->getElementById(1409);
    }
}