<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Class JsonApiConfiguration
 *
 * @package Mikemirten\Bundle\JsonApiBundle\DependencyInjection
 */
class JsonApiConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder  = new TreeBuilder();
        $children = $builder->root(JsonApiExtension::ALIAS)->children();

        $this->processMappers($children);
        $this->processHttpClient($children);
        $this->processResourceClients($children);

        return $builder;
    }

    /**
     * Process mappers
     *
     * @param NodeBuilder $builder
     */
    protected function processMappers(NodeBuilder $builder)
    {
        $builder->arrayNode('mappers')
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
    }

    /**
     * Process http-client
     *
     * @param NodeBuilder $builder
     */
    protected function processHttpClient(NodeBuilder $builder)
    {
        $builder->arrayNode('http_client')
            ->children()
                ->scalarNode('guzzle_service')
                ->cannotBeEmpty();
    }

    /**
     * Process resource-based clients
     *
     * @param NodeBuilder $builder
     */
    protected function processResourceClients(NodeBuilder $builder)
    {
        $children = $builder->arrayNode('resource_clients')
            ->prototype('array')
                ->children()
                    ->scalarNode('base_url')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()

                    ->arrayNode('decorators')
                        ->prototype('scalar')
                        ->end()
                    ->end();

        $this->processEndpoints($children);
    }

    /**
     * Process endpoints of client
     *
     * @param NodeBuilder $builder
     */
    protected function processEndpoints(NodeBuilder $builder)
    {
        $builder->arrayNode('resources')
            ->prototype('array')
                ->children()
                    ->scalarNode('path')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()

                    ->arrayNode('methods')
                        ->prototype('array')
                            ->children();
    }
}