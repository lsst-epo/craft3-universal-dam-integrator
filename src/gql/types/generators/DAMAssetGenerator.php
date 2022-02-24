<?php

namespace rosas\dam\gql\types\generators;

// use nystudio107\seomatic\gql\arguments\SeomaticArguments;
// use nystudio107\seomatic\gql\interfaces\SeomaticInterface;
// use nystudio107\seomatic\gql\types\SeomaticType;

use rosas\dam\gql\interfaces\DAMAssetInterface;
use rosas\dam\gql\types\DAMAssetType;

use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;

class DAMAssetGenerator implements GeneratorInterface
{
    /**
     * @inheritdoc
     */
    public static function generateTypes($context = null): array
    {
        $gqlTypes = [];
        $damAssetFields = DAMAssetInterface::getFieldDefinitions();
        $damAssetArgs = [];
        $typeName = self::getName();
        $damAssetType = GqlEntityRegistry::getEntity($typeName)
            ?: GqlEntityRegistry::createEntity($typeName, new DAMAssetType([
                'name' => $typeName,
                'args' => function () use ($damAssetArgs) {
                    return $damAssetArgs;
                },
                'fields' => function () use ($damAssetFields) {
                    return $damAssetFields;
                },
                'description' => 'This entity has all the enhanced asset fields',
            ]));

        $gqlTypes[$typeName] = $damAssetType;
        TypeLoader::registerType($typeName, function () use ($seomaticType) {
            return $damAssetType;
        });

        return $gqlTypes;
    }

    /**
     * @inheritdoc
     */
    public static function getName($context = null): string
    {
        return 'DAMAssetType';
    }
}