<?php

namespace rosas\dam\fields;

use Craft;
use craft\base\Field;
use craft\base\ElementInterface;
use craft\helpers\Json;
use rosas\dam\controllers\AssetSyncController;
use rosas\dam\db\AssetMetadata;

class DAMAsset extends Field {

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
            'namespacedId' => $namespacedId,
        ];

        if(array_key_exists("damAsset", $element) && $element->damAsset != null) {
            $metadata = $this->getAssetMetadataByAssetId(intval($element->damAsset));
            $templateVals['assetId'] = $element->damAsset;
        }

        if(array_key_exists("thumbnailUrl", $metadata)) {
            $templateVals['thumbnailUrl'] = $metadata["thumbnailUrl"];
        }

        return Craft::$app->getView()->renderTemplate($this->inputTemplate, $templateVals);
    }

    public static function getAssetMetadataByAssetId($assetId) {
        $rows = AssetMetadata::find()
        ->where(['"assetId"' => $assetId])
        ->all();

        $res = [];
        $currentId = 0;
        foreach($rows as $row) {
            if($currentId != intval(str_replace('"', '', $row['assetId']))) {
                $currentId = intval(str_replace('"', '', $row['assetId']));
                // array_push($res, [$currentId => []]);
                $res["assetId"] = $currentId;
                $res[$row["dam_meta_key"]] = $row["dam_meta_value"];
            } else {
                if($currentId != 0) {
                    $res[$row["dam_meta_key"]] = $row["dam_meta_value"];
                }
            }
        }
        return $res;
    }

}