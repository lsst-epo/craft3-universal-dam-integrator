<?php

namespace rosas\dam\db;

use Craft;
use craft\db\ActiveRecord;
// use rosas\dam\models\Constants;

class AssetMetadata extends ActiveRecord{
    
    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return "{{universaldamintegrator_asset_metadata}}";
    }

    public static function upsert($id, $assetMetadata) {
        $db = Craft::$app->getDb();

        foreach(\rosas\dam\models\Constants::ASSET_METADATA_FIELDS as $key => $value) {
            if(array_key_exists($value[0], $assetMetadata)) {
                $metaVal = $assetMetadata[$value[0]];
                if(count($value) > 1) {
                    $metaVal = $metaVal[$value[1]];
                }
                $metaVal = ($metaVal == null) ? "" : $metaVal;
            } else {
                $metaVal = "";
            }

            $rows = self::find() // check if the record alread exists
                ->where(['dam_meta_key' => $key, 'id' => $id])
                ->all();

            if($rows == null || count($rows) > 0) { // insert
                $db->createCommand()
                    ->insert('{{%universaldamintegrator_asset_metadata}}',  [
                        'assetId' => $id,
                        'dam_meta_key' => $key,
                        'dam_meta_value' => $metaVal
                    ])
                    ->execute();
            } else { //update
                $db->createCommand()
                    ->update('{{%universaldamintegrator_asset_metadata}}',  [
                        'assetId' => $id,
                        'dam_meta_key' => $key,
                        'dam_meta_value' =>  $metaVal
                    ])
                    ->execute();
            }
        }
    }

}