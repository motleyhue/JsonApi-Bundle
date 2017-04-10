<?php

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection;

use JMS\Serializer\Serializer;
use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler\DocumentHydratorCompilerPass;
use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler\ObjectMapperCompilerPass;
use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler\ViewListenerCompilerPass;
use Mikemirten\Component\JsonApi\HttpClient\HttpClient;
use Mikemirten\Component\JsonApi\Hydrator\DocumentHydrator;
use Mikemirten\Component\JsonApi\Mapper\ObjectMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @group   dependency-injection
 * @package Mikemirten\Bundle\JsonApiBundle\DependencyInjection
 */
class ConfigurationTest extends TestCase
{
    /**
     * Integration test of dependency-injection container:
     *
     * 1. It compiles with no errors.
     * 2. It is able to initialize and provide dependencies with no errors.
     * 3. The dependencies are instances of expected types.
     */
    public function testConfiguration()
    {
        $builder = new ContainerBuilder();
        $locator = new FileLocator(__DIR__ . '/../../src/Resources/config');
        $loader  = new YamlFileLoader($builder, $locator);

        $loader->load('services.yml');
        $this->registerMocks($builder);

        $builder->addCompilerPass(new DocumentHydratorCompilerPass());
        $builder->addCompilerPass(new ObjectMapperCompilerPass());
        $builder->addCompilerPass(new ViewListenerCompilerPass());

        $builder->compile();

        $this->assertInstanceOf(
            DocumentHydrator::class,
            $builder->get('mrtn_json_api.document_hydrator')
        );

        $this->assertInstanceOf(
            HttpClient::class,
            $builder->get('mrtn_json_api.http_client')
        );

        $this->assertInstanceOf(
            ObjectMapper::class,
            $builder->get('mrtn_json_api.object_mapper')
        );
    }

    /**
     * Register mocks of required services
     *
     * @param ContainerBuilder $builder
     */
    protected function registerMocks(ContainerBuilder $builder)
    {
        $builder->set(
            'jms_serializer',
            $this->createMock(Serializer::class)
        );

        $builder->set(
            'router',
            $this->createMock(RouterInterface::class)
        );

        $builder->set(
            'property_accessor',
            $this->createMock(PropertyAccessorInterface::class)
        );

        $builder->set(
            'event_dispatcher',
            $this->createMock(EventDispatcherInterface::class)
        );
    }
}