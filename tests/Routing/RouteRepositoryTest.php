<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Routing;

use PHPUnit\Framework\TestCase;

/**
 * @group   routing
 * @package Mikemirten\Bundle\JsonApiBundle\Routing
 */
class RouteRepositoryTest extends TestCase
{
    /**
     * @dataProvider getRoutesDefinition
     *
     * @param string $baseUrl
     * @param array  $routes
     * @param array  $params
     * @param string $generatedUri
     */
    public function testGenerate(string $baseUrl, array $routes, string $routeName, array $params, string $generatedUri)
    {
        $repository = new RouteRepository($baseUrl, $routes);

        $this->assertSame(
            $generatedUri,
            $repository->generate($routeName, $params)
        );
    }

    /**
     * Get definition of routes
     *
     * @return array
     */
    public function getRoutesDefinition(): array
    {
        return [
            [
                'https://test_domain.com',
                [
                    'users_collection' => [
                        'path'    => '/v1/users',
                        'methods' => ['POST']
                    ],
                    'roles_collection' => [
                        'path'    => '/v1/roles',
                        'methods' => ['POST']
                    ]
                ],
                'users_collection',
                [],
                'https://test_domain.com/v1/users'
            ],
            [
                'https://test_domain.com',
                [
                    'user' => [
                        'path'    => '/v1/users/{id}',
                        'methods' => ['GET', 'DELETE']
                    ]
                ],
                'user',
                ['id' => 1],
                'https://test_domain.com/v1/users/1'
            ]
        ];
    }
}