<?php

namespace rosas\dam\gql\interfaces;

use Craft;
use GraphQL\Type\Definition\Type;
// use craft\gql\base\InterfaceType as BaseInterfaceType;
use craft\gql\interfaces\Element as ElementInterface;
use craft\gql\TypeManager;
use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\InterfaceType;
use craft\gql\types\generators\AssetType;
use craft\helpers\Json;

use rosas\dam\gql\types\generators\DAMAssetGenerator;
use rosas\dam\elements\Asset;

class DAMAssetInterface extends ElementInterface {

    /**
     * @inheritdoc
     */
    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        } //else if ($type = GqlEntityRegistry::getEntity(self::get)

        $type = GqlEntityRegistry::createEntity(self::class, new InterfaceType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by all assets.',
            'resolveType' => self::class . '::resolveElementTypeName'
            // 'resolveType' => function($value) {
            //     Craft::info("tardy - inside of anon function");
            //     Craft::info($value);

            //     //return GqlEntityRegistry::getEntity("AssetInterface");
            //     // return GqlEntityRegistry::getEntity('DAMAssetType');
            //     return new Asset();
            // }
        ]));

        //AssetType::generateTypes();
        DAMAssetGenerator::generateTypes();

        return $type;
    }

    

    /**
     * @inheritdoc
     */
    public static function getTypeGenerator(): string
    {
        return DAMAssetGenerator::class;
        //return AssetType::class;
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'DAMAssetInterface';
        //return 'Element';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {
        return TypeManager::prepareFieldDefinitions(array_merge(parent::getFieldDefinitions(), self::getConditionalFields(), [
            'dam_meta_key' => [
                'name' => 'dam_meta_key',
                'type' => Type::string(),
                'description' => 'Gets the key from the dam metadata table.'
            ],
            'volumeId' => [
                'name' => 'volumeId',
                'type' => Type::int(),
                'description' => 'The ID of the volume that the asset belongs to.',
            ],
        ]), self::getName());
    }
}