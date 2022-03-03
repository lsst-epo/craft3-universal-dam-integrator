<?php

namespace rosas\dam\db;

use Craft;
use craft\db\ActiveRecord;

class AssetMetadata extends ActiveRecord{
    
    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return "{{universaldamintegrator_asset_metadata}}";
    }

}