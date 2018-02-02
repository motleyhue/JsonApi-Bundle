<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @group   dependency-injection
 * @package Mikemirten\Bundle\JsonApiBundle\DependencyInjection
 */
class JsonApiExtensionTest extends TestCase
{
    /**
     * @dataProvider mappersProvider
     *
     * @param array $config
     */
    public function testLoadExtensionWithMappers(array $config)
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->method('findTaggedServiceIds')
            ->with('mrtn_json_api.object_mapper.handler')
            ->willReturn([
                'test_handler_id' => [
                    ['alias' => 'test_handler_name']
                ]
            ]);

        $container->expects($this->once())
            ->method('setDefinition')
            ->with(
                'mrtn_json_api.object_mapper.default',
                $this->isInstanceOf(ChildDefinition::class)
            )
            ->willReturnCallback(
                function(string $id, ChildDefinition $definition)
                {
                    $this->assertSame(
                        'mrtn_json_api.object_mapper.abstract',
                        $definition->getParent()
                    );

                    $calls = $definition->getMethodCalls();

                    $this->assertCount(1, $calls);
                    $this->assertArrayHasKey(0, $calls, 'Expected call at offset #0 has not present.');
                    $this->assertSame('addHandler', $calls[0][0]);
                    $this->assertArrayHasKey(0, $calls[0][1], 'Expected argument at offset #0 has not present.');
                    $this->assertInstanceOf(Reference::class, $calls[0][1][0]);
                    $this->assertSame('test_handler_id', (string) $calls[0][1][0]);
                }
            );

        $loader = $this->createMock(LoaderInterface::class);

        $extension = new JsonApiExtension($loader);
        $extension->load($config, $container);
    }

    /**
     * @dataProvider endpointClientsProvider
     *
     * @param array $config
     */
    public function testLoadExtensionWithClients(array $config)
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->at(0))
            ->method('getParameter')
            ->with('kernel.environment')
            ->willReturn('prod');

        $container->expects($this->at(1))
            ->method('setAlias')
            ->with('mrtn_json_api.object_mapper.definition_provider');

        $container->expects($this->at(2))
            ->method('getParameter')
            ->with('mrtn_json_api.route_repository.class')
            ->willReturn('Test\\RouteRepository');

        $container->expects($this->at(3))
            ->method('getParameter')
            ->with('mrtn_json_api.resource_client.class')
            ->willReturn('Test\\ResourceClient');

        $container->expects($this->at(4))
            ->method('getParameter')
            ->with('mrtn_json_api.http_client.decorator.event_dispatcher.class')
            ->willReturn('Test\\EventDispatcherDecorator');

        $container->expects($this->at(5))
            ->method('setDefinition')
            ->with(
                'mrtn_json_api.http_client.decorator.event_dispatcher.test_client',
                $this->isInstanceOf(Definition::class)
            )
            ->willReturnCallback(
                function(string $id, Definition $definition)
                {
                    $this->assertSame('Test\\EventDispatcherDecorator', $definition->getClass());
                    $this->assertFalse($definition->isPublic());

                    $this->assertInstanceOf(Reference::class, $definition->getArgument(0));
                    $this->assertSame('mrtn_json_api.http_client', (string) $definition->getArgument(0));

                    $this->assertInstanceOf(Reference::class, $definition->getArgument(1));
                    $this->assertSame('event_dispatcher', (string) $definition->getArgument(1));

                    $this->assertSame(
                        'mrtn_json_api.resource_client.test_client.request',
                        $definition->getArgument(2)
                    );

                    $this->assertSame(
                        'mrtn_json_api.resource_client.test_client.response',
                        $definition->getArgument(3)
                    );

                    $this->assertSame(
                        'mrtn_json_api.resource_client.test_client.exception',
                        $definition->getArgument(4)
                    );
                }
            );

        $container->expects($this->at(6))
            ->method('setDefinition')
            ->with(
                'mrtn_json_api.route_repository.test_client',
                $this->isInstanceOf(Definition::class)
            )
            ->willReturnCallback(
                function(string $id, Definition $definition)
                {
                    $this->assertSame('Test\\RouteRepository', $definition->getClass());
                    $this->assertFalse($definition->isPublic());

                    $this->assertSame('https://test.service.com', $definition->getArgument(0));
                    $this->assertSame(
                        [
                            'test_resource' => [
                                'path'    => '/v1/test/{id}',
                                'methods' => ['GET', 'DELETE']
                            ]
                        ],
                        $definition->getArgument(1)
                    );
                }
            );

        $container->expects($this->at(7))
            ->method('setDefinition')
            ->with(
                'mrtn_json_api.resource_client.test_client',
                $this->isInstanceOf(Definition::class)
            )
            ->willReturnCallback(
                function(string $id, Definition $definition)
                {
                    $this->assertSame('Test\\ResourceClient', $definition->getClass());
                    $this->assertTrue($definition->isPublic());

                    $this->assertInstanceOf(Reference::class, $definition->getArgument(0));
                    $this->assertSame(
                        'mrtn_json_api.http_client.decorator.event_dispatcher.test_client',
                        (string) $definition->getArgument(0)
                    );

                    $this->assertInstanceOf(Reference::class, $definition->getArgument(1));
                    $this->assertSame(
                        'mrtn_json_api.route_repository.test_client',
                        (string) $definition->getArgument(1)
                    );
                }
            );

        $loader = $this->createMock(LoaderInterface::class);

        $extension = new JsonApiExtension($loader);
        $extension->load($config, $container);
    }

    public function mappersProvider(): array
    {
        return [[[
            'mrtn_json_api' => [
                'mappers' => [
                    'default' => [
                        'handlers' => ['test_handler_name']
                    ]
                ]
            ]
        ]]];
    }

    public function endpointClientsProvider(): array
    {
        return [[[
            'mrtn_json_api' => [
                'mappers' => [],

                'resource_clients' => [
                    'test_client' => [
                        'base_url'  => 'https://test.service.com',
                        'resources' => [
                            'test_resource' => [
                                'path'    => '/v1/test/{id}',
                                'methods' => [
                                    'GET'    => [],
                                    'DELETE' => []
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]]];
    }
}