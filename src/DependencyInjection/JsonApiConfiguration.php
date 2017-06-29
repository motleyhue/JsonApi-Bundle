<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class JsonApiConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder  = new TreeBuilder();
        $children = $builder->root('mrtn_json_api')->children();

        $children->arrayNode('view_listener_mapping_handler')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default_mapper')
                    ->defaultValue('default');

        $children->arrayNode('mappers')
            ->defaultValue(['default' => [
                'handlers' => [
                    'attribute',
                    'relationship',
                    'link'
                ]
            ]])
            ->prototype('array')
                ->children()
                    ->arrayNode('handlers')
                        ->prototype('scalar');

        return $builder;
    }
}