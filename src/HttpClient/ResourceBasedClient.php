<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\HttpClient;

use GuzzleHttp\Psr7\Request;
use Mikemirten\Bundle\JsonApiBundle\Routing\RouteRepository;
use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\HttpClient\HttpClientInterface;
use Mikemirten\Component\JsonApi\HttpClient\JsonApiRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Resources-based HTTP client
 * Supports JsonApi requests
 *
 * @package Mikemirten\Bundle\JsonApiBundle\HttpClient
 *
 * @method ResponseInterface get(string $resource, array $parameters = [], array $headers = [])
 * @method ResponseInterface head(string $resource, array $parameters = [], array $headers = [])
 * @method ResponseInterface post(string $resource, array $parameters = [], array $headers = [], $body = null)
 * @method ResponseInterface patch(string $resource, array $parameters = [], array $headers = [], $body = null)
 * @method ResponseInterface put(string $resource, array $parameters = [], array $headers = [], $body = null)
 * @method ResponseInterface delete(string $resource, array $parameters = [], array $headers = [])
 * @method ResponseInterface options(string $resource, array $parameters = [], array $headers = [])
 *
 */
class ResourceBasedClient
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var RouteRepository
     */
    protected $repository;

    /**
     * ResourceBasedClient constructor.
     *
     * @param HttpClientInterface $client
     * @param RouteRepository     $repository
     */
    public function __construct(HttpClientInterface $client, RouteRepository $repository)
    {
        $this->client     = $client;
        $this->repository = $repository;
    }

    /**
     * Send a request
     *
     * @param  string $method
     * @param  string $resource
     * @param  array  $parameters
     * @param  array  $headers
     * @param  mixed  $body
     *
     * @return ResponseInterface
     */
    public function request(string $method, string $resource, array $parameters = [], array $headers = [], $body = null): ResponseInterface
    {
        $uri = $this->repository->generate($resource, $parameters);

        $request = ($body instanceof AbstractDocument)
            ? new JsonApiRequest($method, $uri, $headers, $body)
            : new Request($method, $uri, $headers, $body);

        return $this->client->request($request);
    }

    /**
     * Send a request using name of method as an HTTP-method
     *
     * $client->get('user', ['id' => 1])
     * is equal to:
     * $client->request('GET', 'user', ['id' => 1])
     *
     * @param  string $name
     * @param  array  $arguments
     * @return ResponseInterface
     */
    public function __call(string $name, array $arguments): ResponseInterface
    {
        array_unshift($arguments, strtoupper($name));

        return call_user_func_array([$this, 'request'], $arguments);
    }
}