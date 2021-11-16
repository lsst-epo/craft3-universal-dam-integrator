<?php
namespace rosas\dam\services;

use Craft;
use yii\base\Component;
use craft\elements\Asset;
use rosas\dam\services\Elements;
use craft\helpers\Json;

class Assets extends Component
{

    private static $appId = getenv("CANTO_APP_ID");

    private static $secretKey = getenv("CANTO_SECRET_KEY");


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

        $response = $client->post('/oauth/api/oauth2/token?app_id=' . $appId . '&app_secret=' . $secretKey , '&grant_type=client_credentials');
        $body = $response->getBody();

        // Depending on the API...
        $data = Json::decodeIfJson($body);
        return $body;
    }


    // public function testAssetQuery() {
    //     return Craft::$app->getElements()->getElementById(1409);
    // }
}