<?php
namespace rosas\dam\services;

use Craft;
use yii\base\Component;
use craft\elements\Asset;
use rosas\dam\services\Elements;
use craft\helpers\Json;

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
        //$success = $elements->saveElement($newAsset, false);
        //echo "\n\n Rosas - success : " . $success . "\n\n";


    }

    /**
     * Test harness function for authenticating (grabbing an auth token)
     * from the Canto API
     */ 
    public function testAuth() {
        // https://docs.guzzlephp.org/en/stable/quickstart.html
        $client = Craft::createGuzzleClient([
        'base_uri' => 'https://oauth.canto.com',
        ]);

        $response = $client->post('/oauth/api/oauth2/token?app_id=d1dc81ae5d9f45e2a57dd2b6c5d19e19&app_secret=437696e1503146df83130ba0d646c3626167b560ea9b4fdf860d832620260dff&grant_type=client_credentials');
        $body = $response->getBody();

        // Depending on the API...
        $data = Json::decodeIfJson($body);
        return $body;
    }


    // public function testAssetQuery() {
    //     return Craft::$app->getElements()->getElementById(1409);
    // }
}