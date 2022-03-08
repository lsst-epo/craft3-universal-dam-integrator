<?php

namespace rosas\dam\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;
use craft\helpers\Json;
use rosas\dam\services\Assets;
use rosas\dam\db\AssetMetadata;
use rosas\dam\models\Constants;

class AssetSyncController extends Controller {

    const ALLOW_ANONYMOUS_NEVER = 0;
    const ALLOW_ANONYMOUS_LIVE = 1;
    const ALLOW_ANONYMOUS_OFFLINE = 2;

    public $enableCsrfValidation = false;

    /**
     * @var int|bool|int[]|string[] Whether this controller’s actions can be accessed anonymously.
     *
     * This can be set to any of the following:
     *
     * - `false` or `self::ALLOW_ANONYMOUS_NEVER` (default) – indicates that all controller actions should never be
     *   accessed anonymously
     * - `true` or `self::ALLOW_ANONYMOUS_LIVE` – indicates that all controller actions can be accessed anonymously when
     *    the system is live
     * - `self::ALLOW_ANONYMOUS_OFFLINE` – indicates that all controller actions can be accessed anonymously when the
     *    system is offline
     * - `self::ALLOW_ANONYMOUS_LIVE | self::ALLOW_ANONYMOUS_OFFLINE` – indicates that all controller actions can be
     *    accessed anonymously when the system is live or offline
     * - An array of action IDs (e.g. `['save-guest-entry', 'edit-guest-entry']`) – indicates that the listed action IDs
     *   can be accessed anonymously when the system is live
     * - An array of action ID/bitwise pairs (e.g. `['save-guest-entry' => self::ALLOW_ANONYMOUS_OFFLINE]` – indicates
     *   that the listed action IDs can be accessed anonymously per the bitwise int assigned to it.
     */
    public $allowAnonymous = self::ALLOW_ANONYMOUS_LIVE;

    /**
     * CREATE webhook controller
     */
    public function actionAssetCreateWebhook() {
        $damId = $this->request->getBodyParam('id');
        $assetsService = new Assets();
        $assetsService->saveDamAsset($damId);
    }

    /**
     * DELETE webhook controller
     */
    public function actionAssetDeleteWebhook() {
        $damId = $this->request->getBodyParam('id');
        $assetMetadata = Assets::getAssetMetadata($damId);
    }

    /**
     * UPDATE webhook controller
     */
    public function actionAssetUpdateWebhook() {
        $damId = $this->request->getBodyParam('id');
        $assetsService = new Assets();
        $assetMetadata = $assetService->getAssetMetadata($damId);
        // do something
        $ids = $this->_getAssetIdByDamId($damId);

        foreach($ids as $id) {
            // Upsert logic
            $table = "universaldamintegrator_asset_metadata";
            // $time = $this->beginCommand("upsert into $table");
            // $this->db->createCommand()->upsert($table, $insertColumns, $updateColumns, $params, $includeAuditColumns)->execute();
            // $this->endCommand($time);

            // $assetMetadataModel = new AssetMetadata();
            // $assetMetadataModel->__set("assetId", $id);
            // $assetMetadataModel->__set("dam_meta_key", "updated_from_webhook");
            // $assetMetadataModel->__set("dam_meta_value", true);
            //$assetMetadataModel->save();

            
            // Beginning of db insert code
            AssetMetadata::upsert($id, $assetMetadata);
        }

        return "success!";
    }

    private static function _getAssetIdByDamId($damId) {
        $rows = AssetMetadata::find()
        ->where(['dam_meta_value' => $damId, 'dam_meta_key' => 'damId'])
        ->all();

        $ids = [];
        foreach($rows as $row) {
            array_push($ids, str_replace('"', '', $row['assetId']));
        }
        Craft::info("schnauz - about to log ids:", "rosas");
        Craft::info($ids, "rosas");
        return $ids;
    }
}