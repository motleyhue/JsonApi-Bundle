<?php

namespace Mikemirten\Bundle\JsonApiBundle\EventListener;

use Mikemirten\Bundle\JsonApiBundle\ObjectHandler\ObjectHandlerInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiDocumentView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiIteratorView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiObjectView;
use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\Document\ErrorObject;
use Mikemirten\Component\JsonApi\Document\ResourceCollectionDocument;
use Mikemirten\Component\JsonApi\Document\ResourceObject;
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;
use Mikemirten\Component\JsonApi\Mapper\Definition\Link as LinkDefinition;
use Mikemirten\Component\JsonApi\Mapper\Handler\LinkRepository\Link as LinkData;
use Mikemirten\Component\JsonApi\Mapper\Handler\LinkRepository\RepositoryInterface;
use Mikemirten\Component\JsonApi\Mapper\Handler\LinkRepository\RepositoryProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class JsonApiViewListenerTest extends TestCase
{
    public function testSkipHandling()
    {
        $event = $this->createMock(GetResponseForControllerResultEvent::class);

        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn([]);

        $event->expects($this->never())
            ->method('setResponse');

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);

        $this->assertNull($listener->onKernelView($event));
    }

    public function testDocument()
    {
        $document = $this->createMock(AbstractDocument::class);

        $document->method('toArray')
            ->willReturn(['data' => 'qwerty']);

        $event = $this->createEvent($document, '{"data":"qwerty"}');

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);
    }

    public function testResource()
    {
        $resource = $this->createMock(ResourceObject::class);

        $resource->method('toArray')
            ->willReturn(['resource_data' => 'qwerty']);

        $event = $this->createEvent($resource, '{"jsonapi":{"version":"1.0"},"data":{"resource_data":"qwerty"}}');

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);
    }

    public function testError()
    {
        $error = $this->createMock(ErrorObject::class);

        $error->method('toArray')
            ->willReturn(['error_data' => 'qwerty']);

        $event = $this->createEvent($error, '{"errors":[{"error_data":"qwerty"}],"jsonapi":{"version":"1.0"}}');

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);
    }

    public function testDocumentView()
    {
        $document = $this->createMock(AbstractDocument::class);

        $document->method('toArray')
            ->willReturn(['data' => 'qwerty']);

        $view = $this->createMock(JsonApiDocumentView::class);

        $view->expects($this->once())
            ->method('getDocument')
            ->willReturn($document);

        $view->method('getStatusCode')
            ->willReturn(200);

        $event = $this->createEvent($view, '{"data":"qwerty"}');

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);
    }

    public function testObjectView()
    {
        $object  = new \stdClass();
        $object2 = new \stdClass();

        $view = $this->createMock(JsonApiObjectView::class);

        $view->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $view->method('getStatusCode')
            ->willReturn(200);

        $view->expects($this->once())
            ->method('getIncludedObjects')
            ->willReturn([$object2]);

        $handler = $this->createObjectHandler('stdClass', [$object, $object2], ['test' => 'qwerty']);

        $event = $this->createEvent($view, '{"included":[{"test":"qwerty"}],"jsonapi":{"version":"1.0"},"data":{"test":"qwerty"}}');

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->addObjectHandler($handler);

        $listener->onKernelView($event);
    }

    /**
     * @expectedException \LogicException
     */
    public function testUnsupportedObject()
    {
        $view = $this->createMock(JsonApiObjectView::class);

        $view->expects($this->once())
            ->method('getObject')
            ->willReturn(new \stdClass());

        $view->method('getStatusCode')
            ->willReturn(200);

        $event = $this->createMock(GetResponseForControllerResultEvent::class);

        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($view);

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);
    }

    public function testIteratorView()
    {
        $object   = new \stdClass();
        $object2  = new \stdClass();
        $iterator = new \ArrayIterator([$object]);

        $view = $this->createMock(JsonApiIteratorView::class);

        $view->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);

        $view->method('getStatusCode')
            ->willReturn(200);

        $view->expects($this->once())
            ->method('getIncludedObjects')
            ->willReturn([$object2]);

        $handler = $this->createObjectHandler('stdClass', [$object, $object2], ['test' => 'qwerty']);

        $event = $this->createEvent($view, '{"included":[{"test":"qwerty"}],"jsonapi":{"version":"1.0"},"data":[{"test":"qwerty"}]}');

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->addObjectHandler($handler);

        $listener->onKernelView($event);
    }

    public function testResourceCallback()
    {
        $object = new ResourceObject('123', 'Qwerty');

        $view = $this->createMock(JsonApiObjectView::class);

        $view->method('getObject')
            ->willReturn($object);

        $view->method('getStatusCode')
            ->willReturn(200);

        $view->method('hasResourceCallback')
            ->willReturn(true);

        $called = false;

        $view->expects($this->once())
            ->method('getResourceCallback')
            ->willReturn(function($resource) use(&$called) {
                $this->assertInstanceOf(ResourceObject::class, $resource);
                $called = true;
            });

        $event = $this->createMock(GetResponseForControllerResultEvent::class);

        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($view);

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);

        $this->assertTrue($called);
    }

    public function testDocumentCallback()
    {
        $object = new ResourceObject('123', 'Qwerty');

        $view = $this->createMock(JsonApiObjectView::class);

        $view->method('getObject')
            ->willReturn($object);

        $view->method('getStatusCode')
            ->willReturn(200);

        $view->method('hasDocumentCallback')
            ->willReturn(true);

        $called = false;

        $view->expects($this->once())
            ->method('getDocumentCallback')
            ->willReturn(function($resource) use(&$called) {
                $this->assertInstanceOf(SingleResourceDocument::class, $resource);
                $called = true;
            });

        $event = $this->createMock(GetResponseForControllerResultEvent::class);

        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($view);

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);

        $this->assertTrue($called);
    }

    public function testResourceCallbackWithIterator()
    {
        $object   = new ResourceObject('123', 'Qwerty');
        $iterator = new \ArrayIterator([$object]);

        $view = $this->createMock(JsonApiIteratorView::class);

        $view->method('getIterator')
            ->willReturn($iterator);

        $view->method('getStatusCode')
            ->willReturn(200);

        $view->method('hasResourceCallback')
            ->willReturn(true);

        $called = false;

        $view->expects($this->once())
            ->method('getResourceCallback')
            ->willReturn(function($resource) use(&$called) {
                $this->assertInstanceOf(ResourceObject::class, $resource);
                $called = true;
            });

        $event = $this->createMock(GetResponseForControllerResultEvent::class);

        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($view);

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);

        $this->assertTrue($called);
    }

    public function testDocumentCallbackWithIterator()
    {
        $object   = new ResourceObject('123', 'Qwerty');
        $iterator = new \ArrayIterator([$object]);

        $view = $this->createMock(JsonApiIteratorView::class);

        $view->method('getIterator')
            ->willReturn($iterator);

        $view->method('getStatusCode')
            ->willReturn(200);

        $view->method('hasDocumentCallback')
            ->willReturn(true);

        $called = false;

        $view->expects($this->once())
            ->method('getDocumentCallback')
            ->willReturn(function($resource) use(&$called) {
                $this->assertInstanceOf(ResourceCollectionDocument::class, $resource);
                $called = true;
            });

        $event = $this->createMock(GetResponseForControllerResultEvent::class);

        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($view);

        $provider = $this->createMock(RepositoryProvider::class);
        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);

        $this->assertTrue($called);
    }

    public function testDocumentLinks()
    {
        $object = new ResourceObject('123', 'Qwerty');

        $linkDefinition = $this->createLinkDefinition('test_name', 'test_repository', 'test_link');

        $view = $this->createMock(JsonApiObjectView::class);

        $view->method('getObject')
            ->willReturn($object);

        $view->method('getStatusCode')
            ->willReturn(200);

        $view->expects($this->once())
            ->method('getDocumentLinks')
            ->willReturn([$linkDefinition]);

        $event = $this->createEvent($view, '{"links":{"test_name":"http:\/\/test.com"},"jsonapi":{"version":"1.0"},"data":{"id":"123","type":"Qwerty"}}');

        $provider = $this->createLinkProvider('test_repository', 'test_link', 'http://test.com');

        $listener = new JsonApiViewListener($provider);
        $listener->onKernelView($event);
    }

    /**
     * Create mock of object-handler
     *
     * @param  string $type         Common for all objects
     * @param  array  $objects      List of objects expected to get handled
     * @param  array  $resourceData Common for all objects
     * @return ObjectHandlerInterface
     */
    protected function createObjectHandler(string $type, array $objects, array $resourceData): ObjectHandlerInterface
    {
        $resource = $this->createMock(ResourceObject::class);

        $resource->method('toArray')
            ->willReturn($resourceData);

        $handler = $this->createMock(ObjectHandlerInterface::class);

        $handler->expects($this->once())
            ->method('supports')
            ->with($type)
            ->willReturn(true);

        foreach ($objects as $offset => $object)
        {
            $handler->expects($this->at($offset + 1))
                ->method('handle')
                ->with($object)
                ->willReturn($resource);
        }

        return $handler;
    }

    /**
     * Create mock of link's definition
     *
     * @param  string $name
     * @param  string $repositoryName
     * @param  string $linkName
     * @return LinkDefinition
     */
    protected function createLinkDefinition(string $name, string $repositoryName, string $linkName): LinkDefinition
    {
        $link = $this->createMock(LinkDefinition::class);

        $link->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $link->expects($this->once())
            ->method('getRepositoryName')
            ->willReturn($repositoryName);

        $link->expects($this->once())
            ->method('getLinkName')
            ->willReturn($linkName);

        return $link;
    }

    /**
     * Create mock of repository provider
     *
     * @param  string $repositoryName
     * @param  string $linkName
     * @param  string $reference
     * @return RepositoryProvider
     */
    protected function createLinkProvider(string $repositoryName, string $linkName, string $reference): RepositoryProvider
    {
        $link = $this->createMock(LinkData::class);

        $link->expects($this->once())
            ->method('getReference')
            ->willReturn($reference);

        $repository = $this->createMock(RepositoryInterface::class);

        $repository->expects($this->once())
            ->method('getLink')
            ->with($linkName)
            ->willReturn($link);

        $provider = $this->createMock(RepositoryProvider::class);

        $provider->expects($this->once())
            ->method('getRepository')
            ->with($repositoryName)
            ->willReturn($repository);

        return $provider;
    }

    /**
     * Create mock of event
     *
     * @param  mixed  $result
     * @param  string $expectedContent
     * @return GetResponseForControllerResultEvent
     */
    protected function createEvent($result, string $expectedContent): GetResponseForControllerResultEvent
    {
        $event = $this->createMock(GetResponseForControllerResultEvent::class);

        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($result);

        $event->expects($this->once())
            ->method('setResponse')
            ->with($this->isInstanceOf(Response::class))
            ->willReturnCallback(
                function(Response $response) use($expectedContent)
                {
                    $this->assertSame(
                        $expectedContent,
                        $response->getContent()
                    );
                }
            );

        return $event;
    }
}