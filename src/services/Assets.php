<?php
namespace rosas\dam\services;

use Craft;
use yii\base\Component;
use craft\elements\Asset;
use rosas\dam\services\Elements;
use craft\helpers\Json;
use craft\events\GetAssetThumbUrlEvent;

class Assets extends Component
{

    private $authToken;

    private $assetMetadata;

    public function __construct() {
        $this->authToken = '';
        $this->assetMetadata = '';
    }

    public function init() {
        parent::init();
    }

    public function testMetaSave() {
        // Ensure settings are saved before attempting any requests
        if(isset(\rosas\dam\Plugin::getInstance()->getSettings()->retrieveAssetMetadataEndpoint) &&
           isset(\rosas\dam\Plugin::getInstance()->getSettings()->authEndpoint) &&
           isset(\rosas\dam\Plugin::getInstance()->getSettings()->secretKey) &&
           isset(\rosas\dam\Plugin::getInstance()->getSettings()->appId)) {
            $this->authToken = $this->getAuthToken();
            $this->assetMetadata = $this->getAssetMetadata();
            return $this->saveAssetMetadata();
        } else {
            return null;
        }
        
    }

    private function saveAssetMetadata() {
        // Test saving a brand new asset
        $newAsset = new Asset();
        $newAsset->avoidFilenameConflicts = true;
        $newAsset->setScenario(Asset::SCENARIO_CREATE);


        $newAsset->filename = $this->assetMetadata["name"];
        $newAsset->setWidth(100);
        $newAsset->width = 100;
        $newAsset->size = $this->assetMetadata["metadata"]["Asset Data Size (Long)"];
        $newAsset->folderId = 17;
        $newAsset->setVolumeId(6); 
        $newAsset->kind = "extImage({$this->assetMetadata["id"]})";
        $newAsset->firstSave = true;
        $newAsset->propagateAll = false; //changed from true for debugging purposes




        $elements = new Elements();
        $success = $elements->saveElement($newAsset, false, true, false);
        return $success;
    }

    /**
     * Handle responding to EVENT_GET_ASSET_THUMB_URL events
     *
     * @param GetAssetThumbUrlEvent $event
     *
     * @return null|string
     */
    public function handleGetAssetThumbUrlEvent(GetAssetThumbUrlEvent $event)
    {
        Craft::beginProfile('handleGetAssetThumbUrlEvent', __METHOD__);
        $url = $event->url;
        $asset = $event->asset;
        if($asset->kind != "image") {
            $parsedKey = substr($asset->kind, 9);
            $parsedKey = str_replace(")", "", $parsedKey);

            $this->authToken = $this->getAuthToken();
            $client = Craft::createGuzzleClient();
            $getAssetMetadataEndpoint = \rosas\dam\Plugin::getInstance()->getSettings()->retrieveAssetMetadataEndpoint;
            try {
                $bearerToken = "Bearer {$this->authToken}";
                $response = $client->request("GET", $getAssetMetadataEndpoint, ['headers' => ["Authorization" => $bearerToken]]);
                $body = $response->getBody();
        
                //Depending on the API...
                $url = Json::decodeIfJson($body)["url"]["directUrlPreview"];
            } catch (Exception $e) {
                return $e;
            }

        }
        Craft::endProfile('handleGetAssetThumbUrlEvent', __METHOD__);

        return $url;

    }

    /**
     * Get asset metadata
     */ 
    private function getAssetMetadata() {
        $client = Craft::createGuzzleClient();
        $getAssetMetadataEndpoint = \rosas\dam\Plugin::getInstance()->getSettings()->retrieveAssetMetadataEndpoint;

        if(!isset($this->authToken)) {
            $this->authToken = $this->getAuthToken();
        } else {
            try {
                $bearerToken = "Bearer {$this->authToken}";
                $response = $client->request("GET", $getAssetMetadataEndpoint, ['headers' => ["Authorization" => $bearerToken]]);
                $body = $response->getBody();
        
                //Depending on the API...
                return Json::decodeIfJson($body);
            } catch (Exception $e) {
                return $e;
            }
        }
    }

    /**
     *  Private function for using the app ID and secret key to get an auth token
     */ 
    private function getAuthToken() : string {
        $client = Craft::createGuzzleClient();
        $appId = \rosas\dam\Plugin::getInstance()->getSettings()->appId;
        $secretKey = \rosas\dam\Plugin::getInstance()->getSettings()->secretKey;
        $authEndpoint = \rosas\dam\Plugin::getInstance()->getSettings()->authEndpoint;

        // Inject appId if the token is included in the URL
        $authEndpoint = str_replace("{appId}", $appId, $authEndpoint);

        // Inject secretKey if the token is included in the URL
        $authEndpoint = str_replace("{secretKey}", $secretKey, $authEndpoint);

        // Get auth token
        $response = $client->post($authEndpoint);
        $body = $response->getBody();

        // Extract auth token from response
        $authTokenDecoded = Json::decodeIfJson($body);
        $authToken = $authTokenDecoded["accessToken"];

        return $authToken;
        
    }

}