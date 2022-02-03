<?php

namespace rosas\dam\volumes;

use Craft;
use craft\base\Volume;

/**
 * It's possible this class isn't needed at all, for context:
 * https://github.com/craftcms/cms/discussions/9991
 * The extended Volume class implements FlysystemVolume and it seems
 * most of the interface functions are for image manipulation, which 
 * for phase 1 of the DAM integration has been deemed not necessary
 */

class DAMVolume extends Volume
{

    public $quickTest = "eric";

    public function init() {
        parent::init();
    }
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Canto DAM'; // return display name from settings
    }

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        // copied over from the GCS plugin:
        // if (isset($config['manualBucket'])) {
        //     if (isset($config['bucketSelectionMode']) && $config['bucketSelectionMode'] === 'manual') {
        //         $config['bucket'] = ArrayHelper::remove($config, 'manualBucket');
        //     } else {
        //         unset($config['manualBucket']);
        //     }
        // }
        parent::__construct($config);
    }


    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // copied over from GCS plugin:
        // $behaviors['parser'] = [
        //     'class' => EnvAttributeParserBehavior::class,
        //     'attributes' => [
        //         'subfolder',
        //         'projectId',
        //         'bucket',
        //     ],
        // ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        return $rules;
    }

    // /**
    //  * @inheritdoc
    //  */
    // public function getSettingsHtml()
    // {
    //     // copied over from GCS plugin:
    //     // return Craft::$app->getView()->renderTemplate('google-cloud/volumeSettings', [
    //     //     'volume' => $this,
    //     //     'periods' => array_merge(['' => ''], Assets::periodList()),
    //     // ]);
    //     // return false;
    //     return Craft::$app->getView()->renderTemplate('universal-dam-integrator/settings', [
    //         'volume' => "eric rosas",
    //         'rosas' => $this->$tester
    //     ]);
    // }

    /**
     * @inheritdoc
     */
    public function getRootUrl()
    {
        // Copied over from GCS plugin
        if (($rootUrl = parent::getRootUrl()) !== false) {
            $rootUrl .= $this->_subfolder();
        }
        return $rootUrl;
    }

    // Likely don't need this function for now:
    // /**
    //  * @inheritdoc
    //  */
    // public function deleteDir(string $path)
    // {
    //     $fileList = $this->getFileList($path, true);

    //     foreach ($fileList as $object) {
    //         try {
    //             if ($object['type'] === 'dir') {
    //                 $this->filesystem()->deleteDir($object['path']);
    //             } else {
    //                 $this->filesystem()->delete($object['path']);
    //             }
    //         } catch (\Throwable $exception) {
    //             // Even though we just listed this, the folders may or may not exist
    //             // Depending on whether the folder was created or a file like "folder/file.ext" was uploaded
    //             continue;
    //         }
    //     }

    //     try {
    //         $this->filesystem()->deleteDir($path);
    //     } catch (\Throwable $exception) {
    //         //Ignore if this was a phantom folder, too.
    //     }
    // }

    // 
    //  /**
    //  * @inheritDoc
    //  */
    // public function deleteFile(string $path)
    // {
    //     try {
    //         parent::deleteFile($path);
    //     } catch (\Throwable $exception) {
    //         Craft::$app->getErrorHandler()->logException($exception);
    //         throw new VolumeException(Craft::t('google-cloud', 'Could not delete file due to bucket’s retention policy'), 0, $exception);
    //     }
    // }

    /**
     * @inheritdoc
     * @return GoogleStorageAdapter
     */
    protected function createAdapter()
    {
        // $config = $this->_getConfigArray();

        // $client = static::client($config);
        // $bucket = $client->bucket(Craft::parseEnv($this->bucket));

        // // return new GoogleStorageAdapter($client, $bucket, $this->_subfolder() ?: null);
        // return false;
        return parent::createAdapter();
    }

    /**
     * @inheritdoc
     */
    protected function addFileMetadataToConfig(array $config): array
    {
        // copied over from GCS plugin:
        // if (!empty($this->expires) && DateTimeHelper::isValidIntervalString($this->expires)) {
        //     $expires = new DateTime();
        //     $now = new DateTime();
        //     $expires->modify('+'.$this->expires);
        //     $diff = $expires->format('U') - $now->format('U');

        //     if (!isset($config['metadata'])) {
        //         $config['metadata'] = [];
        //     }
        //     $config['metadata']['cacheControl'] = 'public,max-age='.$diff.', must-revalidate';
        // }

        return parent::addFileMetadataToConfig($config);
    }

    // Beginning of inherited class declaration

    public function getFileMetadata(string $uri): array {
        return parent::getFileSize($uri);
    }

    public function getFileSize(string $uri): ?int {
        return parent::getFileSize($uri);
    }

    public function getDateModified(string $uri): ?int {
        return parent::getDateModified($uri);
    }

    public function createFileByStream(string $path, $stream, array $config) {
        return parent::createFileByStream($path, $stream, $config);
    }

    public function updateFileByStream(string $path, $stream, array $config) {
        return parent::updateFileByStream($path, $stream, $config);
    }

    public function fileExists(string $path): bool {
        return parent::fileExists($path);
    }

    public function deleteFile(string $path) {
        return parent::deleteFile($path);
    }

    public function renameFile(string $path, string $newPath) {
        return parent::renameFile($path, $newPath);
    }

    public function copyFile(string $path, string $newPath) {
        return parent::copyFile($path, $newPath);
    }

    public function saveFileLocally(string $uriPath, string $targetPath): int {
        return parent::saveFileLocally($uriPath, $targetPath);
    }

    public function getFileStream(string $uriPath) {
        return parent::getFileStream($uriPath);
    }

    public function getFileList(string $directory, bool $recursive): array {
        return parent::getFileList($directory, $recursive);
    }

    // End of inherited class declaration

}