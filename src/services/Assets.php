<?php
namespace rosas\dam\services;

use \Datetime;
use Craft;
use yii\base\Component;
use craft\elements\Asset;
//use rosas\dam\elements\Asset;
use rosas\dam\services\Elements;
use craft\helpers\Json;
use craft\events\GetAssetThumbUrlEvent;
use craft\events\GetAssetUrlEvent;
use \rosas\dam\Plugin;
// use rosas\dam\mongo\MongoLog;

class Assets extends Component
{

    private $authToken;

    private $assetMetadata;

    public function __construct() {
        $this->authToken = '';
        $this->assetMetadata = '';
    }

    public function init() {
        \rosas\dam\Plugin::getInstance()->setAttribute("message", "quick test");
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

    public function testMetaSave() {
        // Ensure settings are saved before attempting any requests
        Craft::info("platypus - about to start testMetaSave() process", "rosas");
        if(isset(\rosas\dam\Plugin::getInstance()->getSettings()->retrieveAssetMetadataEndpoint) &&
           isset(\rosas\dam\Plugin::getInstance()->getSettings()->authEndpoint) &&
           isset(\rosas\dam\Plugin::getInstance()->getSettings()->secretKey) &&
           isset(\rosas\dam\Plugin::getInstance()->getSettings()->appId)) {
            Craft::info("platypus - right before the try-catch");
            try {
                Craft::info("platypus - about to call getAuthToken()", "rosas");
                $this->authToken = $this->getAuthToken();
                Craft::info("platypus - auth token: {$this->authToken}", "rosas");
                if($this->authToken != null && !empty($this->authToken)) {
                    Craft::info("platypus - inside of IF check that evaluates auth token, about to evaluate the assetMetadata", "rosas");
                    

                    $this->assetMetadata = $this->getAssetMetadata();
                    if(in_array('errorMessage', $this->assetMetadata)) {
                        Craft::info("platypus - in_array() check failed!", "rosas");
                        Craft::info($this->assetMetadata["errorMessage"], "rosas");
                        return null;
                    } else {
                        Craft::info("platypus - here is the assetMetadata variable:", "rosas");
                        Craft::info($this->assetMetadata, "rosas");
                        Craft::info(print_r($this->assetMetadata), "rosas");

                        Craft::info("platypus -- the asset SHOULD save", "rosas");

                        return $this->saveAssetMetadata();
                    }
                } else {

                }
            } catch (\Exception $e) {
                // To-do: something
            }

        } else {
            return null;
        }
        
    }

    private function saveAssetMetadata() {
        $newAsset = new Asset();
        $newAsset->avoidFilenameConflicts = true;
        $newAsset->setScenario(Asset::SCENARIO_CREATE);


        Craft::info("\n\nAbout to log assetMetadata ", "rosas");
        Craft::info($this->authToken, "rosas");
        $filename = strtolower($this->assetMetadata["url"]["directUrlOriginal"]);

        // Craft::info($this->assetMetadata, "rosas");

        // $urlObj = $this->assetMetadata["url"];

        // Craft::info(print_r($urlObj), "rosas");

        // $filename = "test";

        //$newAsset->filename = $this->assetMetadata["name"];
        Craft::info("maddie - width : " . $this->assetMetadata["width"], "rosas");
        Craft::info("maddie - height : " . $this->assetMetadata["height"], "rosas");
        $newAsset->filename = str_replace("https://rubin.canto.com/direct/", "", $filename);
        // $newAsset->setWidth(100);
        // $newAsset->setWidth($this->assetMetadata["width"]);
        // $newAsset->setHeight($this->assetMetadata["height"]);
        $newAsset->kind = "image";
        $newAsset->setHeight($this->assetMetadata["height"]);
        $newAsset->setWidth($this->assetMetadata["width"]);
        $newAsset->size = $this->assetMetadata["metadata"]["Asset Data Size (Long)"];
        $newAsset->folderId = 17;
        // $newAsset->setVolumeId(5); 
        //$newAsset->kind = "extImage({$this->assetMetadata["id"]})";
        // $newAsset->kind = "image";
        $newAsset->firstSave = true;
        $newAsset->propagateAll = false; //changed from true for debugging purposes
        $now = new DateTime();
        $newAsset->dateModified = $now->format('Y-m-d H:i:s');



        $elements = new Elements();
        //$success = $elements->saveElement($newAsset, false, true, false);
        $success = $elements->saveElement($newAsset, false, true, true, $this->assetMetadata);


        
        Craft::info("bonobo - about to log success id:", "rosas");
        Craft::info($success, "rosass");
        // Craft::info(print_r($success), "rosas");
        // Craft::info(print_r($newAsset), "rosas");

        return $success;
        //return null; // temp 
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
        Craft::info("beaver - inside the handleGetAssetThumbUrlEvent() function", "rosas");
        Craft::beginProfile('handleGetAssetThumbUrlEvent', __METHOD__);
        $url = $event->url;
        $asset = $event->asset;
        
        
        if(Plugin::getInstance()->getSettings()->damVolume != null) {
            $settingsVolID = Craft::$app->getVolumes()->getVolumeByHandle($getAssetMetadataEndpoint = Plugin::getInstance()->getSettings()->damVolume)["id"];
            if($asset->getVolumeId() == $settingsVolID) {
                Craft::info("schnoodle - inside of the eval!!!!", "rosas");
            //if(substr($asset->kind, 0, 3) == "ext") {
                // $parsedKey = substr($asset->kind, 9);
                // $parsedKey = str_replace(")", "", $parsedKey);
    
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
        return "";

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
                Craft::info("platypus - here is the body variable", "rosas");
                Craft::info($body);
                Craft::info(Json::decodeIfJson($body));

                if(!is_array(JSON::decodeIfJson($body))) {
                    Craft::info("platypus - NOT AN ARRAY", "rosas");
                    return Json::decodeIfJson("{ 'errorMessage' : 'Asset metadata retrieval failed!'}");
                } else {
                    Craft::info("platypus - YES!! it IS an array", "rosas");
                    return Json::decodeIfJson($body);
                }
            } catch (Exception $e) {
                return $e;
            }
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

        Craft::info("platypus - inside of getAuthToken() before if check", "rosas");
        if($appId != null &&
           $secretKey != null &&
           $authEndpoint != null) {
            
            // Inject appId if the token is included in the URL
            $authEndpoint = str_replace("{appId}", $appId, $authEndpoint);

            // Inject secretKey if the token is included in the URL
            $authEndpoint = str_replace("{secretKey}", $secretKey, $authEndpoint);

            // Get auth token
            try {
                Craft::info("platypus - about to attempt to snag auth token", "rosas");
                $response = $client->post($authEndpoint);
                $body = $response->getBody();
            } catch (\Exception $e) {
                Craft::info("platypus - an error occurred while attempting to retrieve the auth token!", "rosas");
                Craft::info($e);
                Craft::info(print_r($e));
                return $e->getMessage();
            }
            

            // Extract auth token from response
            if(!$validateOnly) {
                $authTokenDecoded = Json::decodeIfJson($body);
                $authToken = $authTokenDecoded["accessToken"];
        
                return $authToken;
            } else {
                Craft::info("platypus - woopsies from inside the if check", "rosas");
                return "";
            }
        } else {
            Craft::info("platypus - something wasn't populated!", "rosas");
            return "";
        }

    }

        /**
     * Returns the control panel thumbnail URL for a given asset.
     *
     * @param Asset $asset asset to return a thumb for
     * @param int $width width of the returned thumb
     * @param int|null $height height of the returned thumb (defaults to $width if null)
     * @param bool $generate whether to generate a thumb in none exists yet
     * @param bool $fallbackToIcon whether to return the URL to a generic icon if a thumbnail can't be generated
     * @return string
     * @throws NotSupportedException if the asset can't have a thumbnail, and $fallbackToIcon is `false`
     */
    // public function getThumbUrl(Asset $asset, int $width, int $height = null, bool $generate = false, bool $fallbackToIcon = true): string
    // {
    //     Craft::info("platypus - hoo ha!!!!!!", "rosas");
    //     return "";
        // if ($height === null) {
        //     $height = $width;
        // }

        // // Maybe a plugin wants to do something here
        // // todo: remove the `size` key in 4.0
        // if ($this->hasEventHandlers(self::EVENT_GET_ASSET_THUMB_URL)) {
        //     $event = new GetAssetThumbUrlEvent([
        //         'asset' => $asset,
        //         'width' => $width,
        //         'height' => $height,
        //         'size' => max($width, $height),
        //         'generate' => $generate,
        //     ]);
        //     $this->trigger(self::EVENT_GET_ASSET_THUMB_URL, $event);

        //     // If a plugin set the url, we'll just use that.
        //     if ($event->url !== null) {
        //         return $event->url;
        //     }
        // }

        // return UrlHelper::actionUrl('assets/thumb', [
        //     'uid' => $asset->uid,
        //     'width' => $width,
        //     'height' => $height,
        //     'v' => $asset->dateModified->getTimestamp(),
        // ], null, false);
    // }

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
        Craft::info("beaver - inside the getUrl function", "rosas");
        $asset = $event->asset;
        // Craft::info($asset, "rosas");
        // Craft::info(JSON::encode($asset), "rosas");
        Craft::info("beaver - filename:", "rosas");
        Craft::info($asset->filename, "rosas");
        $url = $event->url;
        // $volume = $this->getVolume();

        // if (!$volume->hasUrls || !$this->folderId) {
        //     return null;
        // }

        // $mimeType = $this->getMimeType();
        // $generalConfig = Craft::$app->getConfig()->getGeneral();

        // if (
        //     ($mimeType === 'image/gif' && !$generalConfig->transformGifs) ||
        //     ($mimeType === 'image/svg+xml' && !$generalConfig->transformSvgs)
        // ) {
        //     return Assets::generateUrl($volume, $this);
        // }

        // // Normalize empty transform values
        // $transform = $transform ?: null;

        // if (is_array($transform)) {
        //     if (isset($transform['width'])) {
        //         $transform['width'] = round((float)$transform['width']);
        //     }
        //     if (isset($transform['height'])) {
        //         $transform['height'] = round((float)$transform['height']);
        //     }
        //     $assetTransformsService = Craft::$app->getAssetTransforms();
        //     $transform = $assetTransformsService->normalizeTransform($transform);
        // }

        // if ($transform === null && $this->_transform !== null) {
        //     $transform = $this->_transform;
        // }

        // try {
        //     return Craft::$app->getAssets()->getAssetUrl($this, $transform, $generateNow);
        // } catch (VolumeObjectNotFoundException $e) {
        //     Craft::error("Could not determine asset's URL ($this->id): {$e->getMessage()}");
        //     Craft::$app->getErrorHandler()->logException($e);
        //     return UrlHelper::actionUrl('not-found', null, null, false);
        // }
        return $url;
    }



}