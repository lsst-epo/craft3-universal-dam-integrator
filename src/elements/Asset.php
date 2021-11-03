<?php

namespace rosas\dam\elements;

use Craft;
use craft\records\Asset as AssetRecord;
use craft\base\ElementInterface;
use craft\base\Element;
//use craft\elements\Asset as AssetClass;

//class Asset extends AssetClass {
class Asset extends Element {

    /**
     * Validation scenario that should be used when the asset is only getting *moved*; not renamed.
     *
     * @since 3.7.1
     */
    const SCENARIO_REPLACE = 'replace';
    const SCENARIO_CREATE = 'create';

    /**
     * @var int|float|null Height
     */
    private $element;

    /**
     * @var int|null Folder ID
     */
    public $folderId;

    /**
     * @var int|null The ID of the user who first added this asset (if known)
     */
    public $uploaderId;

    /**
     * @var string|null Folder path
     */
    public $folderPath;

    /**
     * @var string|null Filename
     * @todo rename to private $_basename w/ getter & setter in 4.0; and getFilename() should not include the extension (to be like PATHINFO_FILENAME). We can add a getBasename() for getting the whole thing.
     */
    public $filename;

    /**
     * @var string|null Kind
     */
    public $kind;

    /**
     * @var int|null Size
     */
    public $size;

    /**
     * @var bool|null Whether the file was kept around when the asset was deleted
     */
    public $keptFile;

    /**
     * @var \DateTime|null Date modified
     */
    public $dateModified;

    /**
     * @var string|null New file location
     */
    public $newLocation;

    /**
     * @var string|null Location error code
     * @see AssetLocationValidator::validateAttribute()
     */
    public $locationError;

    /**
     * @var string|null New filename
     */
    public $newFilename;

    /**
     * @var int|null New folder id
     */
    public $newFolderId;

    /**
     * @var string|null The temp file path
     */
    public $tempFilePath;

    /**
     * @var bool Whether Asset should avoid filename conflicts when saved.
     */
    public $avoidFilenameConflicts = false;

    /**
     * @var string|null The suggested filename in case of a conflict.
     */
    public $suggestedFilename;

    /**
     * @var string|null The filename that was used that caused a conflict.
     */
    public $conflictingFilename;

    /**
     * @var bool Whether the asset was deleted along with its volume
     * @see beforeDelete()
     */
    public $deletedWithVolume = false;

    /**
     * @var bool Whether the associated file should be preserved if the asset record is deleted.
     * @see beforeDelete()
     * @see afterDelete()
     */
    public $keepFileOnDelete = false;

    /**
     * @var int|null Volume ID
     */
    private $_volumeId;

    /**
     * @var int|float|null Width
     */
    private $_width;

    /**
     * @var int|float|null Height
     */
    private $_height;

    /**
     * @var array|null Focal point
     */
    private $_focalPoint;

    /**
     * @var AssetTransform|null
     */
    private $_transform;

    /**
     * @var string
     */
    private $_transformSource = '';

    /**
     * @var VolumeInterface|null
     */
    private $_volume;

    /**
     * @var User|null
     */
    private $_uploader;

    /**
     * @var int|null
     */
    private $_oldVolumeId;


    /**
     * @inheritdoc
     * @throws Exception if the asset isn't new but doesn't have a row in the `assets` table for some reason
     */

    public function __construct() {
        parent::__construct();
    }

    public function setAsset($el) {
        $this->element = $el;
    }

    /**
     * Returns the volume’s ID.
     *
     * @return int|null
     */
    public function getVolumeId()
    {
        return (int)$this->_volumeId ?: null;
    }

    public function afterSave(bool $isNew)
    {
        Craft::getLogger()->log(" - in the overridden class!",  $category = 'rosas');
        if (!$this->propagating) {
            $isCpRequest = Craft::$app->getRequest()->getIsCpRequest();
            $sanitizeCpImageUploads = Craft::$app->getConfig()->getGeneral()->sanitizeCpImageUploads;

            if (
                \in_array($this->getScenario(), [self::SCENARIO_REPLACE, self::SCENARIO_CREATE], true) &&
                !($isCpRequest && !$sanitizeCpImageUploads)
            ) {
                Image::cleanImageByPath($this->tempFilePath);
            }

            // Relocate the file?
            // if ($this->element->newLocation !== null || $this->element->tempFilePath !== null) {
            //     $this->_relocateFile();
            // }

            // Get the asset record
            if (!$isNew) {
                $record = AssetRecord::findOne($this->$element->id);

                if (!$record) {
                    throw new Exception('Invalid asset ID: ' . $element->id);
                }
            } else {
                $record = new AssetRecord();
                $record->id = (int)$this->element->id;
             }


            $record->filename = $this->element->filename;
            $record->volumeId = $this->element->getVolumeId();
            $record->folderId = (int)$this->element->folderId;
            $record->uploaderId = (int)$this->element->uploaderId ?: null;
            $record->kind = $this->element->kind;
            $record->size = (int)$this->element->size ?: null;
            $record->width = (int)$this->_width ?: null;
            $record->height = (int)$this->_height ?: null;
            $record->dateModified = $this->element->dateModified;

            echo "\n\n record->filename : " . $record->filename . "\n\n";

            // if ($this->getHasFocalPoint()) {
            //     echo "\n\n Rosas - inside getHasFocalPoint() IF check\n\n";
            //     $focal = $this->getFocalPoint();
            //     $record->focalPoint = number_format($focal['x'], 4) . ';' . number_format($focal['y'], 4);
            // } else {
            //     echo "\n\n Rosas - NOT in the getHasFocalPoint() check, in the ELSE\n\n";
            //     $record->focalPoint = null;
            // }

            $record->save(false);
        }

        //$el = new Element();
        echo "\n\n Rosas - before parent::afterSave\n\n";        
        parent::afterSave($isNew);
        //$el->afterSave($isNew);

    }

}