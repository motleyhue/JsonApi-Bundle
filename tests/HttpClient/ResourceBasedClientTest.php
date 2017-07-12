<?php

namespace Mikemirten\Bundle\JsonApiBundle\HttpClient;

use GuzzleHttp\Psr7\Uri;
use Mikemirten\Bundle\JsonApiBundle\Routing\RouteRepository;
use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\HttpClient\HttpClientInterface;
use Mikemirten\Component\JsonApi\HttpClient\JsonApiRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @group   http-client
 * @package Mikemirten\Bundle\JsonApiBundle\HttpClient
 */
class ResourceBasedClientTest extends TestCase
{
    public function testRequest()
    {
        $client     = $this->createMock(HttpClientInterface::class);
        $repository = $this->createMock(RouteRepository::class);
        $response   = $this->createMock(ResponseInterface::class);

        $repository->expects($this->once())
            ->method('generate')
            ->with('test_route', ['id' => 123])
            ->willReturn('https://test.com/users/123');

        $client->expects($this->once())
            ->method('request')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturnCallback(
                function(RequestInterface $request) use($response)
                {
                    $uri = $request->getUri();

                    $this->assertSame('POST', $request->getMethod());
                    $this->assertInstanceOf(Uri::class, $uri);

                    $this->assertSame('https', $uri->getScheme());
                    $this->assertSame('test.com', $uri->getHost());
                    $this->assertSame('/users/123', $uri->getPath());
                    $this->assertTrue($request->hasHeader('X-Header'));
                    $this->assertSame(['qwerty'], $request->getHeader('X-Header'));
                    $this->assertSame(
                        '{"name":"something"}',
                        $request->getBody()->getContents()
                    );

                    return $response;
                }
            );

        $resourceClient = new ResourceBasedClient($client, $repository);

        $result = $resourceClient->request(
            'POST',
            'test_route',
            ['id' => 123],
            ['X-Header' => 'qwerty'],
            '{"name":"something"}'
        );

        $this->assertSame($response, $result);
    }

    /**
     * @depends testRequest
     */
    public function testRequestByMethod()
    {
        $client     = $this->createMock(HttpClientInterface::class);
        $repository = $this->createMock(RouteRepository::class);
        $response   = $this->createMock(ResponseInterface::class);

        $repository->method('generate')
            ->willReturn('https://test.com/users');

        $client->expects($this->once())
            ->method('request')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturnCallback(
                function(RequestInterface $request) use($response)
                {
                    $this->assertSame('DELETE', $request->getMethod());

                    return $response;
                }
            );

        $resourceClient = new ResourceBasedClient($client, $repository);

        $result = $resourceClient->delete('test_route');

        $this->assertSame($response, $result);
    }

    /**
     * @depends testRequest
     */
    public function testRequestWithDocument()
    {
        $client     = $this->createMock(HttpClientInterface::class);
        $repository = $this->createMock(RouteRepository::class);
        $response   = $this->createMock(ResponseInterface::class);
        $document   = $this->createMock(AbstractDocument::class);

        $repository->method('generate')
            ->willReturn('https://test.com/users');

        $client->expects($this->once())
            ->method('request')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturnCallback(
                function(RequestInterface $request) use($response, $document)
                {
                    $this->assertInstanceOf(JsonApiRequest::class, $request);
                    $this->assertSame($document, $request->getDocument());

                    return $response;
                }
            );

        $resourceClient = new ResourceBasedClient($client, $repository);

        $result = $resourceClient->request('POST', 'test_route', [], [], $document);

        $this->assertSame($response, $result);
    }
}