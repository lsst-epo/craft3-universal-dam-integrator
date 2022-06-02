<?php

namespace rosas\dam\gql\resolvers;

use Craft;
use craft\gql\base\ElementResolver;
use craft\gql\resolvers\elements\Asset as AssetResolver;
// use rosas\dam\elements\Asset as AssetElement;
use craft\elements\Asset as AssetElement;
use rosas\dam\elements\db\DAMAssetQuery;
use craft\elements\db\ElementQuery;
use GraphQL\Type\Definition\ResolveInfo;
use craft\helpers\Gql as GqlHelper;
use Illuminate\Support\Collection;

class DAMAssetResolver extends ElementResolver {
//class DAMAssetResolver extends AssetResolver {

    /**
     * Copied from  craft\gql\resolvers\elements\Asset;
     */
    public static function prepareQuery($source, array $arguments, $fieldName = null)
    {
        // If this is the beginning of a resolver chain, start fresh
	if ($source === null) {
	    Craft::info("source is NOT null", "dingo");
            $query = AssetElement::find(); // From this plugin's overriden Asset class
            // If not, get the prepared element query
	} else {
	    Craft::info("logging fieldName", "dingo");
	    Craft::info($fieldName, "dingo");
            //$query = $source->$fieldName;
	    $query = "damAsset";
	}

        // If it's preloaded, it's preloaded.
        if (is_array($query)) {
            return $query;
        }

        foreach ($arguments as $key => $value) {
            $query->$key($value);
        }

        return $query;
    }

     /**
     * Prepare an element query for given resolution argument set.
     *
     * @param mixed $source
     * @param array $arguments
     * @param array|null $context
     * @param ResolveInfo $resolveInfo
     * @return ElementQuery|Collection
     */
    protected static function prepareElementQuery($source, array $arguments, $context, ResolveInfo $resolveInfo)
    {
        /** @var ArgumentManager $argumentManager */
        $argumentManager = empty($context['argumentManager']) ? Craft::createObject(['class' => ArgumentManager::class]) : $context['argumentManager'];
        $arguments = $argumentManager->prepareArguments($arguments);

        $fieldName = GqlHelper::getFieldNameWithAlias($resolveInfo, $source, $context);

        $query = static::prepareQuery($source, $arguments, $fieldName);

        // If that's already preloaded, then, uhh, skip the preloading?
        if (is_array($query)) {
            return $query;
        }

        $parentField = null;

        if ($source instanceof ElementInterface) {
            $fieldContext = $source->getFieldContext();
            $field = Craft::$app->getFields()->getFieldByHandle($fieldName, $fieldContext);

            // This will happen if something is either dynamically added or is inside an block element that didn't support eager-loading
            // and broke the eager-loading chain. In this case Craft has to provide the relevant context so the condition knows where it's at.
            if (($fieldContext !== 'global' && $field instanceof GqlInlineFragmentFieldInterface) || $field instanceof EagerLoadingFieldInterface) {
                $parentField = $field;
            }
        }

        /** @var ElementQueryConditionBuilder $conditionBuilder */
        $conditionBuilder = empty($context['conditionBuilder']) ? Craft::createObject(['class' => ElementQueryConditionBuilder::class]) : $context['conditionBuilder'];
        $conditionBuilder->setResolveInfo($resolveInfo);
        $conditionBuilder->setArgumentManager($argumentManager);

        $conditions = $conditionBuilder->extractQueryConditions($parentField);

        foreach ($conditions as $method => $parameters) {
            if (method_exists($query, $method)) {
                $query = $query->{$method}($parameters);
            }
        }

        // Apply max result config
        $maxGraphqlResults = Craft::$app->getConfig()->getGeneral()->maxGraphqlResults;

        // Reset negative limit to zero
        if ((int)$query->limit < 0) {
            $query->limit(0);
        }

        if ($maxGraphqlResults > 0) {
            $queryLimit = is_null($query->limit) ? $maxGraphqlResults : min($maxGraphqlResults, $query->limit);
            $query->limit($queryLimit);
        }

        return $query;
    }


}
