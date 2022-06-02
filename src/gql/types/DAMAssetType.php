<?php

namespace rosas\dam\gql\types;

use Craft;
use rosas\dam\gql\interfaces\DAMAssetInterface;
use craft\gql\types\elements\Asset as AssetType;
use craft\helpers\Json;
use craft\gql\base\ObjectType;

use GraphQL\Type\Definition\ResolveInfo;

class DAMAssetType extends AssetType {
//class DAMAssetType extends ObjectType {
    /**
     * @inheritdoc
     */
    public function __construct(array $config) {
        $config['interfaces'] = [
		DAMAssetInterface::getType(),
		"calloutImages_Asset",
		"cantoDam_Asset",
		"DAMAssetType"
        ];

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo) {
	Craft::info("About to log source", "sizzle");
	Craft::info(Json::encode($source), "sizzle");
	Craft::info("About to log resolveInfo", "sizzle");
	Craft::info(Json::encode($resolveInfo), "sizzle");
	if(array_key_exists($resolveInfo->fieldName, $source)) {
	    return $source[$resolveInfo->fieldName];
	} else {
	    return [];
	}
        //return $source[$resolveInfo->fieldName];
    }
}
