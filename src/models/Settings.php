<?php

namespace rosas\dam\models;

use craft\base\Model;

class Settings extends Model
{
    
    public $appId;
    public $secretKey;
    public $authEndpoint;
    public $retrieveAssetMetadataEndpoint;

    public function rules()
    {
        return [
            [['authEndpoint', 'appId', 'secretKey', 'retrieveAssetMetadataEndpoint'], 'required'],
        ];
    }
}