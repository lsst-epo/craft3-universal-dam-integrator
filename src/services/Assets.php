<?php
namespace rosas\dam\services;

use Craft;
use yii\base\Component;
use craft\elements\Asset;
use rosas\dam\services\Elements;

class Assets extends Component
{
    public function testMetaSave() {
        //echo "\n\n Rosas - calling testMetaSave from settingsHtml() call";
        Craft::info("\n\n Rosas - in the testMetaSave function!", "rosas");
        // Test saving a brand new asset
        $newAsset = new Asset();
        $newAsset->avoidFilenameConflicts = true;
        $newAsset->setScenario(Asset::SCENARIO_CREATE);


        $newAsset->filename = "this-came-from-the-plugin.png";
        //$newAsset->newFolderId = 17;
        $newAsset->folderId = 17;
        $newAsset->setVolumeId(6); 
        $newAsset->kind = "image";
        $newAsset->firstSave = true;
        $newAsset->propagateAll = false; //changed from true for debugging purposes
        // $newAsset->newLocation = '/var/www/html/storage/runtime/temp/new-eric.jpg';
        // $newAsset->tempFilePath = '/var/www/html/storage/runtime/temp/temp-eric.jpg';


        $elements = new Elements();
        // Don't validate required custom fields
        $success = $elements->saveElement($newAsset, false);
        echo "\n\n Rosas - success : " . $success . "\n\n";
        //Craft::$app->getElements()->saveElement($newAsset, false);
    }
}