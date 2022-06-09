<?php

namespace rosas\dam\fields;

use Craft;
// use craft\base\Field;
use craft\fields\Assets as AssetField;
use craft\base\ElementInterface;
use craft\helpers\Json;
use rosas\dam\controllers\AssetSyncController;
use rosas\dam\db\AssetMetadata;
use craft\gql\arguments\elements\Asset as AssetArguments;
//use craft\gql\interfaces\elements\Asset as AssetInterface;
use rosas\dam\gql\interfaces\DAMAssetInterface as AssetInterface;
//use craft\gql\resolvers\elements\Asset as AssetResolver;
use rosas\dam\gql\resolvers\DAMAssetResolver as AssetResolver;
use craft\helpers\Gql as GqlHelper;
use craft\services\Gql as GqlService;
use GraphQL\Type\Definition\Type;
use craft\services\Sections;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\ElementHelper;
use craft\helpers\Db;

class DAMAsset extends AssetField {

     /**
     * @inheritdoc
     */
    protected $settingsTemplate = 'universal-dam-integrator/dam-asset-settings';

    /**
     * @inheritdoc
     */
    protected $inputTemplate = 'universal-dam-integrator/dam-asset';

    /**
     * @inheritdoc
     */
    protected $inputJsClass = 'Craft.DamAssetSelectInput';

    public function __construct(array $config = []) {
        parent::__construct($config);
    }

    public static function displayName(): string {
        return Craft::t('app', 'DAMAsset');
    }

    public static function hasContentColumn(): bool {
        return true; // Extended class sets this to false
    }

    // Pulled from \craft\fields\Assets
    public function getContentGqlType() {
        return [
            'name' => $this->handle,
            'type' => Type::nonNull(Type::listOf(AssetInterface::getType())),
            'args' => AssetArguments::getArguments(),
            'resolve' => AssetResolver::class . '::resolve',
            'complexity' => GqlHelper::relatedArgumentComplexity(GqlService::GRAPHQL_COMPLEXITY_EAGER_LOAD),
        ];
    }

    public function getInputHtml($value, ElementInterface $element = null): string {
        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        $metadata = [];

        // Render the input template
        $templateVals =             [
            'name' => $this->handle,
            'value' => $value,
            'fieldId' => $this->id,
            'elementId' => $element->id,
            'id' => $id,
            'element' => Json::encode($element),
            'namespacedId' => $namespacedId,
        ];

        try {
            if($element->damAsset != null) {
                $assetId = $this->getDamAssetId($element->id);

                Craft::info("About to compare assetId", "UDAMI");
                Craft::info($assetId, "UDAMI");
                Craft::info(Json::encode($assetId), "UDAMI");
                if($assetId != null && is_array($assetId) && count($assetId) > 0) {
                    $assetId = $assetId[0];
                    Craft::info("about to log assetId inside of first IF", "UDAMI");
                    Craft::info($assetId, "UDAMI");
                    if(is_int(intval($assetId))) { // value will likely come back as string, but may come back as "[]"
                        Craft::info("inside of the second IF!", "UDAMI");
                        $metadata = $this->getAssetMetadataByAssetId($assetId);
                        Craft::info("about to log metadata", "UDAMI");
                        Craft::info(Json::encode($metadata), "UDAMI");
                        $templateVals['assetId'] = $assetId;
                    } else {
                        Craft::info("did NOT pass second IF!!!", "UDAMI");
                    }
                }
                
            }
        } catch(Exception $e) {
            Craft::info($e, "error");
        }

        if(array_key_exists("thumbnailUrl", $metadata)) {
            $templateVals['thumbnailUrl'] = $metadata["thumbnailUrl"];
        }

        return Craft::$app->getView()->renderTemplate($this->inputTemplate, $templateVals);
    }

    public static function getDamAssetId($elementId) {
        $field = Craft::$app->fields->getFieldByHandle("damAsset");
	    $col_name = ElementHelper::fieldColumnFromField($field);

        $damAssetId = (new Query())
                ->select([$col_name])
                ->from([Table::CONTENT])
                ->where(Db::parseParam('elementId', $elementId))
		->column();

        return $damAssetId;
    }

    public static function getAssetMetadataByAssetId($assetId) {
        $rows = AssetMetadata::find()
            ->where(['"assetId"' => $assetId])
            ->all();

        Craft::info("inside of getAssetMEtadataByAssetId", "UDAMI2");
        Craft::info(Json::encode($rows), "UDAMI2");

        $res = [];
        $currentId = 0;
        foreach($rows as $row) {
            if($currentId != intval(str_replace('"', '', $row['assetId']))) {
                $currentId = intval(str_replace('"', '', $row['assetId']));
                $res["assetId"] = $currentId;
                $res[$row["dam_meta_key"]] = $row["dam_meta_value"];
            } else {
                if($currentId != 0) {
                    $res[$row["dam_meta_key"]] = $row["dam_meta_value"];
                }
            }
        }

        Craft::info("about to log res", "UDAMI2");
        Craft::info(Json::encode($res), "UDAMI2");
        return $res;
    }
}
