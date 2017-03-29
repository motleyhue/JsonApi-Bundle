<?php

namespace Mikemirten\Bundle\JsonApiBundle\EventListener;

use Mikemirten\Bundle\JsonApiBundle\ObjectHandler\ObjectHandlerInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\AbstractJsonApiView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiDocumentView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiIteratorView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiObjectView;
use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\Document\ErrorObject;
use Mikemirten\Component\JsonApi\Document\ResourceObject;
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

        $listener = new JsonApiViewListener();

        $this->assertNull($listener->onKernelView($event));
    }

    public function testDocument()
    {
        $document = $this->createMock(AbstractDocument::class);

        $document->method('toArray')
            ->willReturn(['data' => 'qwerty']);

        $event = $this->createEvent($document, '{"data":"qwerty"}');

        $listener = new JsonApiViewListener();
        $listener->onKernelView($event);
    }

    public function testResource()
    {
        $resource = $this->createMock(ResourceObject::class);

        $resource->method('toArray')
            ->willReturn(['resource_data' => 'qwerty']);

        $event = $this->createEvent($resource, '{"jsonapi":{"version":"1.0"},"data":{"resource_data":"qwerty"}}');

        $listener = new JsonApiViewListener();
        $listener->onKernelView($event);
    }

    public function testError()
    {
        $error = $this->createMock(ErrorObject::class);

        $error->method('toArray')
            ->willReturn(['error_data' => 'qwerty']);

        $event = $this->createEvent($error, '{"errors":[{"error_data":"qwerty"}],"jsonapi":{"version":"1.0"}}');

        $listener = new JsonApiViewListener();
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

        $view->method('getStatus')
            ->willReturn(200);

        $event = $this->createEvent($view, '{"data":"qwerty"}');

        $listener = new JsonApiViewListener();
        $listener->onKernelView($event);
    }

    public function testObjectView()
    {
        $object = new \stdClass();

        $view = $this->createMock(JsonApiObjectView::class);

        $view->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $view->method('getStatus')
            ->willReturn(200);

        $resource = $this->createMock(ResourceObject::class);

        $resource->method('toArray')
            ->willReturn(['test' => 'qwerty']);

        $handler = $this->createMock(ObjectHandlerInterface::class);

        $handler->expects($this->once())
            ->method('supports')
            ->with('stdClass')
            ->willReturn(true);

        $handler->expects($this->once())
            ->method('handle')
            ->with($object)
            ->willReturn($resource);

        $event = $this->createEvent($view, '{"jsonapi":{"version":"1.0"},"data":{"test":"qwerty"}}');

        $listener = new JsonApiViewListener();
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

        $view->method('getStatus')
            ->willReturn(200);

        $event = $this->createMock(GetResponseForControllerResultEvent::class);

        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($view);

        $listener = new JsonApiViewListener();
        $listener->onKernelView($event);
    }

    public function testIteratorView()
    {
        $object   = new \stdClass();
        $iterator = new \ArrayIterator([$object]);

        $view = $this->createMock(JsonApiIteratorView::class);

        $view->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);

        $view->method('getStatus')
            ->willReturn(200);

        $resource = $this->createMock(ResourceObject::class);

        $resource->method('toArray')
            ->willReturn(['test' => 'qwerty']);

        $handler = $this->createMock(ObjectHandlerInterface::class);

        $handler->expects($this->once())
            ->method('supports')
            ->with('stdClass')
            ->willReturn(true);

        $handler->expects($this->once())
            ->method('handle')
            ->with($object)
            ->willReturn($resource);

        $event = $this->createEvent($view, '{"jsonapi":{"version":"1.0"},"data":[{"test":"qwerty"}]}');

        $listener = new JsonApiViewListener();
        $listener->addObjectHandler($handler);

        $listener->onKernelView($event);
    }

    /**
     * @expectedException \LogicException
     */
    public function testUnsupportedView()
    {
        $view  = $this->createMock(AbstractJsonApiView::class);
        $event = $this->createMock(GetResponseForControllerResultEvent::class);

        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($view);

        $listener = new JsonApiViewListener();
        $listener->onKernelView($event);
    }

    /**
     * Create mock of event
     *
     * @param  $result
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