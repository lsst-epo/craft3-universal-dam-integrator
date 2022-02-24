<?php

namespace rosas\dam\gql\types;

use rosas\dam\gql\interfaces\Asset;

use craft\gql\base\ObjectType;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class SeomaticType
 *
 * @author    nystudio107
 * @package   Seomatic
 * @since     3.2.8
 */
class DAMAssetType extends ObjectType
{
    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $config['interfaces'] = [
            DAMAssetInterface::getType(),
        ];

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        // $fieldName = SeomaticInterface::GRAPH_QL_FIELDS[$resolveInfo->fieldName];

        return $source[$fieldName];
        //return DAMASsetInterface::getFieldDefinitions();
    }
}