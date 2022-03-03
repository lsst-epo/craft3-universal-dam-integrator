<?php

namespace rosas\dam\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;
use craft\helpers\Json;
use rosas\dam\services\Asset;

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

    public function actionAssetCreateWebhook() {
        $damId = $this->request->getBodyParam('id');
        Asset::saveDamAsset($damId);
        
        // return $this->asJson([
        //     'assetId' => $this->request->getBodyParam('id')
        // ]);
    }
}