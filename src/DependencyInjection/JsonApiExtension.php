<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JsonApiExtension extends Extension
{
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

        if (isset($config['mappers'])) {
            $this->createMappers($config['mappers'], $container);
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
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'mrtn_json_api';
    }
}