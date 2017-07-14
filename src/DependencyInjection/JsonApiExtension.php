<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JsonApiExtension extends Extension
{
    const ALIAS = 'mrtn_json_api';

    /**
     * Configuration loader
     *
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * JsonApiExtension constructor.
     *
     * @param LoaderInterface $loader
     */
    public function __construct(LoaderInterface $loader = null)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new JsonApiConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = $this->loader ?? new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yml');
        $loader->load('hydrator.yml');
        $loader->load('mapper.yml');
        $loader->load('http_client.yml');

        if (! empty($config['mappers'])) {
            $this->createMappers($config['mappers'], $container);
        }

        if (! empty($config['resource_clients'])) {
            $this->createResourceClients($config['resource_clients'], $container);
        }
    }

    /**
     * Create mappers
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function createMappers(array $config, ContainerBuilder $container)
    {
        $handlers = $this->findMappingHandlers($container);

        foreach ($config as $name => $mapperDefinition)
        {
            $mapper = new DefinitionDecorator('mrtn_json_api.object_mapper.abstract');
            $mapper->addTag('mrtn_json_api.object_mapper', ['alias' => $name]);

            foreach ($mapperDefinition['handlers'] as $handlerName)
            {
                if (! isset($handlers[$handlerName])) {
                    throw new \LogicException(sprintf('Mapping handler with name "%s" has not been registered as a service.', $handlerName));
                }

                $mapper->addMethodCall('addHandler', [
                    new Reference($handlers[$handlerName])
                ]);
            }

            $container->setDefinition('mrtn_json_api.object_mapper.' . $name, $mapper);
        }
    }

    /**
     * Find mapping handler registered in container
     *
     * @param  ContainerBuilder $container
     * @return array
     */
    protected function findMappingHandlers(ContainerBuilder $container): array
    {
        $handlers = $container->findTaggedServiceIds('mrtn_json_api.object_mapper.handler');

        $found = [];

        foreach ($handlers as $id => $tags)
        {
            foreach ($tags as $tag)
            {
                if (! isset($tag['alias'])) {
                    continue;
                }

                $found[$tag['alias']] = $id;
            }
        }

        return $found;
    }

    /**
     * Create resources-based clients
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function createResourceClients(array $config, ContainerBuilder $container)
    {
        $repositoryClass = $container->getParameter('mrtn_json_api.route_repository.class');
        $clientClass     = $container->getParameter('mrtn_json_api.resource_client.class');

        foreach ($config as $name => $definition)
        {
            $this->createEventDispatcherDecorator($container, $name);

            $routes = $this->createRoutesDefinition($definition['resources']);

            $repository = new Definition($repositoryClass, [$definition['base_url'], $routes]);
            $repository->setPublic(false);

            $client = new Definition($clientClass, [
                new Reference('mrtn_json_api.http_client.decorator.event_dispatcher.' . $name),
                new Reference('mrtn_json_api.route_repository.' . $name)
            ]);

            $container->setDefinition('mrtn_json_api.route_repository.' . $name, $repository);
            $container->setDefinition('mrtn_json_api.resource_client.' . $name, $client);
        }
    }

    /**
     * Create event dispatcher decorator for resource-based http-client
     *
     * @param ContainerBuilder $container
     * @param string           $name
     */
    protected function createEventDispatcherDecorator(ContainerBuilder $container, string $name)
    {
        $class = $container->getParameter('mrtn_json_api.http_client.decorator.event_dispatcher.class');

        $decorator = new Definition($class, [
            new Reference('mrtn_json_api.http_client'),
            new Reference('event_dispatcher'),
            sprintf('mrtn_json_api.resource_client.%s.request', $name),
            sprintf('mrtn_json_api.resource_client.%s.response', $name),
            sprintf('mrtn_json_api.resource_client.%s.exception', $name)
        ]);

        $decorator->setPublic(false);

        $container->setDefinition('mrtn_json_api.http_client.decorator.event_dispatcher.' . $name, $decorator);
    }

    /**
     * Create definition of routes for a route repository by a collection of endpoints
     *
     * @param  array $resources
     * @return array
     */
    protected function createRoutesDefinition(array $resources): array
    {
        $definition = [];

        foreach ($resources as $name => $resource)
        {
            $methods = array_keys($resource['methods']);
            $methods = array_map('strtoupper', $methods);

            $definition[$name] = [
                'path'    => trim($resource['path']),
                'methods' => array_map('trim', $methods)
            ];
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }
}