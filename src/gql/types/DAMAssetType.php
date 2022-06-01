<?php

namespace rosas\dam\gql\types;

use Craft;
use rosas\dam\gql\interfaces\DAMAssetInterface;
use rosas\dam\fields\DAMAsset;
use craft\gql\base\ObjectType;
use craft\helpers\Json;
use GraphQL\Type\Definition\ResolveInfo;
use rosas\dam\db\AssetMetadata;

/**
 * Class SeomaticType
 *
 * @author    nystudio107
 * @package   Seomatic
 * @since     3.2.8
 */
class DAMAssetType extends ObjectType {
    /**
     * @inheritdoc
     */
    public function __construct(array $config) {
        $config['interfaces'] = [
            DAMAssetInterface::getType(),
        ];

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo) {
	Craft::info("about to log source", "schneez");
	Craft::info(Json::encode($source), "schneez");
	Craft::info("about to log resolveInfo->fieldName", "schneez");
	Craft::info($resolveInfo->fieldName, "schneez");
	if(array_key_exists($resolveInfo->fieldName, $source)) {
	    return $source[$resolveInfo->fieldName];
	} else if($resolveInfo->fieldName == "damMetadata"){
	    Craft::info("field not found! BUT field IS damAsset", "schneez");
	    $metadata = $this->getAssetMetadataByAssetId($source->id);
	    Craft::info("logging metadata:", "schneez");
	    Craft::info(Json::encode($metadata), "schneez");
            return $metadata;
	} else {
		Craft::info("field not found AND it is not damAsset!", "schneez");

	    try {
		$resolvedValue = $source[$resolveInfo->fieldName];
		return $resolvedValue;
	    } catch (Exception $e) {
	        return null;
	    }
	}

    }

    public static function getAssetMetadataByAssetId($assetId) {
        $rows = AssetMetadata::find()
        ->where(['"assetId"' => $assetId])
        ->all();

        $res = [];
        $currentId = 0;
        foreach($rows as $row) {
            //if($currentId != intval(str_replace('"', '', $row['assetId']))) {
                //$currentId = intval(str_replace('"', '', $row['assetId']));
                // array_push($res, [$currentId => []]);
		//$res["assetId"] = $currentId;
		$metadataRow = [];
                $metadataRow["metadataKey"] = $row["dam_meta_key"];
		$metadataRow["metadataValue"] = $row["dam_meta_value"];
		array_push($res, $metadataRow);
            //} else {
                //if($currentId != 0) {
                    //$res["metadataKey"] = $row["dam_meta_key"];
                    //$res["metadataValue"] = $row["dam_meta_value"];
                //}
            //}
        }
        return $res;
    }
}
