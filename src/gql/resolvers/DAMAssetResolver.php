<?php

namespace rosas\dam\gql\resolvers;

use Craft;
use craft\gql\base\ElementResolver;
use rosas\dam\elements\Asset as AssetElement;
use rosas\dam\elements\db\DAMAssetQuery;
use GraphQL\Type\Definition\ResolveInfo;
use craft\helpers\Gql as GqlHelper;

class DAMAssetResolver extends ElementResolver {

    /**
     * Copied from  craft\gql\resolvers\elements\Asset;
     */
    public static function prepareQuery($source, array $arguments, $fieldName = null)
    {
        // If this is the beginning of a resolver chain, start fresh
        if ($source === null) {
            $query = AssetElement::find(); // From this plugin's overriden Asset class
            // If not, get the prepared element query
        } else {
            $query = $source->$fieldName;
        }

        // If it's preloaded, it's preloaded.
        if (is_array($query)) {
            return $query;
        }

        foreach ($arguments as $key => $value) {
            $query->$key($value);
        }

        // $pairs = GqlHelper::extractAllowedEntitiesFromSchema('read');

        // if (!GqlHelper::canQueryAssets()) {
        //     return [];
        // }

        //$query->andWhere(['in', 'assets.volumeId', array_values(Db::idsByUids(Table::VOLUMES, $pairs['volumes']))]);

        return $query;
    }

    /**
    //  * @inheritdoc
    //  */
    // public static function resolve($source, array $arguments, $context, ResolveInfo $resolveInfo)
    // {
    //     $query = self::prepareElementQuery($source, $arguments, $context, $resolveInfo);
    //     $value = $query instanceof DAMAssetQuery ? $query->all() : $query;
    //     return GqlHelper::applyDirectives($source, $resolveInfo, $value);
    // }

}