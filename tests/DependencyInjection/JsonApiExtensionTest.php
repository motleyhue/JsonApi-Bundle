<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @group   dependency-injection
 * @package Mikemirten\Bundle\JsonApiBundle\DependencyInjection
 */
class JsonApiExtensionTest extends TestCase
{
    /**
     * @dataProvider configurationProvider
     *
     * @param array $config
     */
    public function testLoadExtension(array $config)
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
                $this->isInstanceOf(DefinitionDecorator::class)
            )
            ->willReturnCallback(
                function(string $id, DefinitionDecorator $definition)
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

    public function configurationProvider(): array
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
}