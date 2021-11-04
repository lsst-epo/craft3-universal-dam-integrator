<?php

namespace rosas\dam\models;

use craft\base\Model;

class Settings extends Model
{
    public $authEndpoint = '';
    public $apiKey = '';
    public $retrieveAllAssetsEndpoint = '';
    public $retrieveOneAssetEndpoint = '';
    public $volumeDisplayName = '';

    public function rules()
    {
        return [
            [['authEndpoint', 'apiKey', 'retrieveAllAssetsEndpoint', 'retrieveOneAssetEndpoint', 'volumeDisplayName'], 'required'],
        ];
    }
}