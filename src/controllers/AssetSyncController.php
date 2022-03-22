<?php

namespace rosas\dam\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;
use craft\helpers\Json;
use craft\records\Asset as AssetRecord;
use craft\records\Element as ElementRecord;
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
        Craft::info("'Create' webhook triggered!", "Universal DAM Integrator");
        $damId = $this->request->getBodyParam('id');
        $assetsService = new Assets();
        $res = $assetsService->saveDamAsset($damId);
        if($res == false) {
            Craft::warning("Asset creation failed, could not fetch asset from Canto!", "Universal DAM Integrator");
            return false;
        } else {
            Craft::info("'Create' webhook successful!", "Universal DAM Integrator");
            return true;
        }

    }

    /**
     * DELETE webhook controller
     */
    public function actionAssetDeleteWebhook() {
        Craft::info("'Delete' webhook triggered!", "Universal DAM Integrator");
        $damId = $this->request->getBodyParam('id');
        $ids = $this->_getAssetIdByDamId($damId);

        foreach($ids as $id) {
            // Deleting the element record cascades to the assets record which cascades to the assetMetadata record
            $element = ElementRecord::findOne($id);
            $element->delete();
        }
        Craft::info("'Delete' webhook successful!", "Universal DAM Integrator");
        return true;
    }

    /**
     * UPDATE webhook controller
     */
    public function actionAssetUpdateWebhook() {
        $damId = $this->request->getBodyParam('id');
        $assetsService = new Assets();
        $ids = $this->_getAssetIdByDamId($damId);

        if($ids != null && is_array($ids) && count($ids) > 0) {
            $assetMetadata = $assetsService->getAssetMetadata($damId);

            if($assetMetadata != null) {
                foreach($ids as $id) { // Temporary code! There shouldn't be multiple craft asset records for a single DAM ID, but during dev testing there is
                    AssetMetadata::upsert($id, $assetMetadata);
                }
            } else {
                Craft::warning("Asset update failed! No Metadata found!", "Universal DAM Integrator");
                return false;
            }
            Craft::info("'Update' webhook successful!", "Universal DAM Integrator");
            return true;
        } else { // The asset record doesn't exist for some reason, so create it
            $this->actionAssetCreateWebhook();
        }
    }

    private static function _getAssetIdByDamId($damId) {
        $rows = AssetMetadata::find()
        ->where(['dam_meta_value' => $damId, 'dam_meta_key' => 'damId'])
        ->all();

        $ids = [];
        foreach($rows as $row) {
            array_push($ids, str_replace('"', '', $row['assetId']));
        }
        return $ids;
    }
}