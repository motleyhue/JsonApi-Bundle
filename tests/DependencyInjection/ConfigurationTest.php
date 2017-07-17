<?php

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection;

use JMS\Serializer\Serializer;
use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler\DocumentHydratorCompilerPass;
use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler\ObjectMapperCompilerPass;
use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler\ViewListenerCompilerPass;
use Mikemirten\Bundle\JsonApiBundle\EventListener\JsonApiViewListener;
use Mikemirten\Bundle\JsonApiBundle\HttpClient\ResourceBasedClient;
use Mikemirten\Component\JsonApi\HttpClient\Decorator\SymfonyEvent\RequestEvent;
use Mikemirten\Component\JsonApi\HttpClient\Decorator\SymfonyEvent\ResponseEvent;
use Mikemirten\Component\JsonApi\HttpClient\HttpClient;
use Mikemirten\Component\JsonApi\Hydrator\DocumentHydrator;
use Mikemirten\Component\JsonApi\Mapper\ObjectMapper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
     *
     * @dataProvider getConfiguration
     *
     * @param array $configuration
     */
    public function testConfiguration(array $configuration)
    {
        $builder = new ContainerBuilder();

        $this->registerMocks($builder);

        $builder->addCompilerPass(new DocumentHydratorCompilerPass());
        $builder->addCompilerPass(new ObjectMapperCompilerPass());

        $builder->registerExtension(new JsonApiExtension());
        $builder->loadFromExtension(JsonApiExtension::ALIAS, $configuration);

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
            $builder->get('mrtn_json_api.object_mapper.default')
        );

        $this->assertInstanceOf(
            JsonApiViewListener::class,
            $builder->get('mrtn_json_api.kernel_view.listener')
        );

        $this->assertInstanceOf(
            ResourceBasedClient::class,
            $builder->get('mrtn_json_api.resource_client.test_client')
        );
    }

    /**
     * Integration test of events dispatching
     * of assembled structure of a resource-based http-client.
     *
     * @dataProvider getConfiguration
     *
     * @param array $configuration
     */
    public function testEventDispatching(array $configuration)
    {
        $builder = new ContainerBuilder();

        $this->registerMocks($builder);

        $builder->removeDefinition('mrtn_json_api.http_client.guzzle');

        $dispatcher = $builder->get('event_dispatcher');

        $dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(
                'mrtn_json_api.resource_client.test_client.request',
                $this->isInstanceOf(RequestEvent::class)
            );

        $dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(
                'mrtn_json_api.http_client.request',
                $this->isInstanceOf(RequestEvent::class)
            );

        $dispatcher->expects($this->at(2))
            ->method('dispatch')
            ->with(
                'mrtn_json_api.http_client.response',
                $this->isInstanceOf(ResponseEvent::class)
            );

        $dispatcher->expects($this->at(3))
            ->method('dispatch')
            ->with(
                'mrtn_json_api.resource_client.test_client.response',
                $this->isInstanceOf(ResponseEvent::class)
            );

        $builder->addCompilerPass(new DocumentHydratorCompilerPass());
        $builder->addCompilerPass(new ObjectMapperCompilerPass());

        $builder->registerExtension(new JsonApiExtension());
        $builder->loadFromExtension(JsonApiExtension::ALIAS, $configuration);

        $builder->addCompilerPass($this->getGuzzleMockCompilerPass());
        $builder->compile();

        $builder->get('mrtn_json_api.resource_client.test_client')->get('test_resource');
    }

    /**
     * Get compiler pass handles mock of guzzle client
     * Necessary to override existing real service of guzzle to avoid real HTTP-requests
     *
     * @return CompilerPassInterface
     */
    protected function getGuzzleMockCompilerPass(): CompilerPassInterface
    {
        $guzzle   = $this->createMock('GuzzleHttp\ClientInterface');
        $response = $this->createMock(ResponseInterface::class);

        $response->method('getStatusCode')
            ->willReturn(200);

        $response->method('getHeader')
            ->willReturn([]);

        $guzzle->expects($this->once())
            ->method('send')
            ->willReturn($response);

        return new class($guzzle) implements CompilerPassInterface
        {
            /**
             * @var \GuzzleHttp\ClientInterface
             */
            protected $guzzle;

            /**
             * Constructor.
             *
             * @param \GuzzleHttp\ClientInterface $guzzle
             */
            public function __construct(\GuzzleHttp\ClientInterface $guzzle)
            {
                $this->guzzle = $guzzle;
            }

            /**
             * {@inheritdoc}
             */
            public function process(ContainerBuilder $builder)
            {
                $builder->set('mrtn_json_api.http_client.guzzle', $this->guzzle);
            }
        };
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

    /**
     * Get configuration for the bundle's extension
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        return [[[
            'resource_clients' => [
                'test_client' => [
                    'base_url'  => 'https://test.com',
                    'resources' => [
                        'test_resource' => [
                            'path'    => '/v1/test',
                            'methods' => [
                                'GET'  => [],
                                'POST' => []
                            ]
                        ]
                    ]
                ]
            ]
        ]]];
    }
}