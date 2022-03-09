<?php
namespace rosas\dam\services;

use \Datetime;
use Craft;
use yii\base\Component;
use craft\elements\Asset;
use rosas\dam\services\Elements;
use craft\helpers\Json;
use craft\events\GetAssetThumbUrlEvent;
use craft\events\GetAssetUrlEvent;
use rosas\dam\elements\db\DAMAssetQuery;
use \rosas\dam\Plugin;
use rosas\dam\db\AssetMetadata;

class Assets extends Component
{

    private $authToken;

    private $assetMetadata;

    public function __construct($config = []) {
        parent::__construct($config);
    }

    public function init() {
        parent::init();
    }

    public function getVolumes() {
        Craft::info("platypus - in the getVolumes() function", "rosas");
        // return print_r(Craft::$app->getVolumes()->getAllVolumes()[0]["name"]);
        $rawVolumes = Craft::$app->getVolumes()->getAllVolumes();
        $vols = [];
        foreach($rawVolumes as $vol) {
            array_push($vols, array(
                "name" => $vol["name"],
                "handle" => $vol["handle"]
            ));
        }
        return $vols;
    }

    public function saveDamAsset($damId = "hu4hj1m04p3f940us68a1g6j3f") {
        // Ensure settings are saved before attempting any requests
        if(isset(\rosas\dam\Plugin::getInstance()->getSettings()->retrieveAssetMetadataEndpoint) &&
           isset(\rosas\dam\Plugin::getInstance()->getSettings()->authEndpoint) &&
           isset(\rosas\dam\Plugin::getInstance()->getSettings()->secretKey) &&
           isset(\rosas\dam\Plugin::getInstance()->getSettings()->appId)) {
            try {
                $this->authToken = $this->getAuthToken();
                if($this->authToken != null && !empty($this->authToken)) {
                    $this->assetMetadata = $this->getAssetMetadata($damId);
                    if(in_array('errorMessage', $this->assetMetadata)) {
                        return null;
                    } else {
                        return $this->saveAssetMetadata();
                    }
                }
            } catch (\Exception $e) {
                return $e;
            }

        } else {
            return null;
        }
        
    }

    private function saveAssetMetadata() {
        $newAsset = new Asset();
        $newAsset->avoidFilenameConflicts = true;
        $newAsset->setScenario(Asset::SCENARIO_CREATE);
        $filename = strtolower($this->assetMetadata["url"]["directUrlOriginal"]);
        $newAsset->filename = str_replace("https://rubin.canto.com/direct/", "", $filename);
        $newAsset->kind = "image";
        $newAsset->setHeight($this->assetMetadata["height"]);
        $newAsset->setWidth($this->assetMetadata["width"]);
        $newAsset->size = $this->assetMetadata["metadata"]["Asset Data Size (Long)"];
        $newAsset->folderId = 17;
        $newAsset->firstSave = true;
        $newAsset->propagateAll = false; //changed from true for debugging purposes
        $now = new DateTime();
        $newAsset->dateModified = $now->format('Y-m-d H:i:s');
        $elements = new Elements();
        $success = $elements->saveElement($newAsset, false, true, true, $this->assetMetadata);

        return $success;
    }

    /**
     * Handle responding to EVENT_GET_ASSET_THUMB_URL events
     *
     * @param GetAssetThumbUrlEvent $event
     *
     * @return null|string
     */
    public function handleGetAssetThumbUrlEvent(GetAssetThumbUrlEvent $event) {
        $url = $event->url;
        $asset = $event->asset;
    
        if(Plugin::getInstance()->getSettings()->damVolume != null) {
            $settingsVolID = Craft::$app->getVolumes()->getVolumeByHandle($getAssetMetadataEndpoint = Plugin::getInstance()->getSettings()->damVolume)["id"];
            if($asset->getVolumeId() == $settingsVolID) {
                $rows = AssetMetadata::find()
                    ->where(['assetId' => $asset->id, 'dam_meta_key' => 'thumbnailUrl'])
                    ->one();
                if($rows != null) {
                    return str_replace('"', '', $rows['dam_meta_value']);
                }
            }
        }
    }

    /**
     * Get asset metadata
     */ 
    public function getAssetMetadata($assetId) {
        try {
            $client = Craft::createGuzzleClient();
            $baseUrl = \rosas\dam\Plugin::getInstance()->getSettings()->retrieveAssetMetadataEndpoint;
            if(substr($baseUrl, (strlen($baseUrl) - 1), strlen($baseUrl)) != '/') {
                $baseUrl = $baseUrl . '/';
            }
            $getAssetMetadataEndpoint = $baseUrl . $assetId;

            if(!isset($this->authToken)) {
                $this->authToken = $this->getAuthToken();
            }

            $bearerToken = "Bearer {$this->authToken}";
            $response = $client->request("GET", $getAssetMetadataEndpoint, ['headers' => ["Authorization" => $bearerToken]]);
            $body = $response->getBody();

            if(!is_array(JSON::decodeIfJson($body))) {
                return Json::decodeIfJson("{ 'errorMessage' : 'Asset metadata retrieval failed!'}");
            } else {
                return Json::decodeIfJson($body);
            }
            
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     *  Private function for using the app ID and secret key to get an auth token
     */ 
    public function getAuthToken($validateOnly = false) : string {
        $client = Craft::createGuzzleClient();
        $appId = \rosas\dam\Plugin::getInstance()->getSettings()->appId;
        $secretKey = \rosas\dam\Plugin::getInstance()->getSettings()->secretKey;
        $authEndpoint = \rosas\dam\Plugin::getInstance()->getSettings()->authEndpoint;

        if($appId != null &&
           $secretKey != null &&
           $authEndpoint != null) {
            
            // Inject appId if the token is included in the URL
            $authEndpoint = str_replace("{appId}", $appId, $authEndpoint);

            // Inject secretKey if the token is included in the URL
            $authEndpoint = str_replace("{secretKey}", $secretKey, $authEndpoint);

            // Get auth token
            try {
                $response = $client->post($authEndpoint);
                $body = $response->getBody();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            

            // Extract auth token from response
            if(!$validateOnly) {
                $authTokenDecoded = Json::decodeIfJson($body);
                $authToken = $authTokenDecoded["accessToken"];
        
                return $authToken;
            } else {
                return "";
            }
        } else {
            return "";
        }

    }

    /**
     * Returns the element’s full URL.
     *
     * @param string|array|null $transform A transform handle or configuration that should be applied to the
     * image If an array is passed, it can optionally include a `transform` key that defines a base transform
     * which the rest of the settings should be applied to.
     * @param bool|null $generateNow Whether the transformed image should be generated immediately if it doesn’t exist. If `null`, it will be left
     * up to the `generateTransformsBeforePageLoad` config setting.
     * @return string|null
     * @throws InvalidConfigException
     */
    public function getUrl(GetAssetUrlEvent $event)
    {
        $asset = $event->asset;
        $url = $event->url;

        return $url;
    }



}