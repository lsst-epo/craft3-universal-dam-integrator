<?php

namespace rosas\dam\models;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    
    public $appId;
    public $secretKey;
    public $authEndpoint;
    public $retrieveAssetMetadataEndpoint;
    public $damVolume;

    public function init() {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function __construct(array $config = []) {
        parent::__construct($config);
    }


    public function getAppId(): string {
        return App::parseEnv($this->secretKey);
    }

    public function rules()
    {
        return [
            [['authEndpoint', 'appId', 'secretKey', 'retrieveAssetMetadataEndpoint', 'damVolume'], 'required']
        ];
    }

    public function getVolumes() {
        // return print_r(Craft::$app->getVolumes()->getAllVolumes()[0]["name"]);
        $rawVolumes = Craft::$app->getVolumes()->getAllVolumes();
        $vols = [];
        array_push($vols, array(
            "label" => "- Select Volume -",
            "value" => ""
        ));
        foreach($rawVolumes as $vol) {
            array_push($vols, array(
                "label" => $vol["name"],
                "value" => $vol["handle"]
            ));
        }
        return $vols;
    }

    public function getVolumeId() {
        if($this->damVolume != null) {
            return Craft::$app->getVolumes()->getVolumeByHandle($this->damVolume)["id"];
        } else {
            return null;
        }
        
    }
}