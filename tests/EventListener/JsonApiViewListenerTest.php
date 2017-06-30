<?php

namespace Mikemirten\Bundle\JsonApiBundle\EventListener;

use Mikemirten\Bundle\JsonApiBundle\Builder\DocumentBuilder;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiDocumentView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiIteratorView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiObjectView;
use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\Document\ErrorObject;
use Mikemirten\Component\JsonApi\Document\ResourceObject;
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * @group   view-listener
 * @package Mikemirten\Bundle\JsonApiBundle\EventListener
 */
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

        $builder  = $this->createMock(DocumentBuilder::class);
        $listener = new JsonApiViewListener($builder);

        $this->assertNull($listener->onKernelView($event));
    }

    public function testDocument()
    {
        $document = $this->createMock(AbstractDocument::class);

        $document->method('toArray')
            ->willReturn(['data' => 'qwerty']);

        $event   = $this->createEvent($document, '{"data":"qwerty"}');
        $builder = $this->createMock(DocumentBuilder::class);

        $listener = new JsonApiViewListener($builder);
        $listener->onKernelView($event);
    }

    public function testResource()
    {
        $resource = $this->createMock(ResourceObject::class);

        $resource->method('toArray')
            ->willReturn(['resource_data' => 'qwerty']);

        $event   = $this->createEvent($resource, '{"jsonapi":{"version":"1.0"},"data":{"resource_data":"qwerty"}}');
        $builder = $this->createMock(DocumentBuilder::class);

        $listener = new JsonApiViewListener($builder);
        $listener->onKernelView($event);
    }

    public function testError()
    {
        $error = $this->createMock(ErrorObject::class);

        $error->method('toArray')
            ->willReturn(['error_data' => 'qwerty']);

        $event   = $this->createEvent($error, '{"errors":[{"error_data":"qwerty"}],"jsonapi":{"version":"1.0"}}');
        $builder = $this->createMock(DocumentBuilder::class);

        $listener = new JsonApiViewListener($builder);
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

        $event   = $this->createEvent($view, '{"data":"qwerty"}');
        $builder = $this->createMock(DocumentBuilder::class);

        $listener = new JsonApiViewListener($builder);
        $listener->onKernelView($event);
    }

    public function testObjectView()
    {
        $view = $this->createMock(JsonApiObjectView::class);

        $view->method('getStatusCode')
            ->willReturn(200);

        $document = $this->createMock(SingleResourceDocument::class);

        $document->expects($this->once())
            ->method('toArray')
            ->willReturn([
                'data' => ['test' => 'qwerty']
            ]);

        $event   = $this->createEvent($view, '{"data":{"test":"qwerty"}}');
        $builder = $this->createMock(DocumentBuilder::class);

        $builder->expects($this->once())
            ->method('build')
            ->with($view)
            ->willReturn($document);

        $listener = new JsonApiViewListener($builder);
        $listener->onKernelView($event);
    }

    public function testIteratorView()
    {
        $view = $this->createMock(JsonApiIteratorView::class);

        $view->method('getStatusCode')
            ->willReturn(200);

        $document = $this->createMock(SingleResourceDocument::class);

        $document->expects($this->once())
            ->method('toArray')
            ->willReturn([
                'data' => [['test' => 'qwerty']]
            ]);

        $event   = $this->createEvent($view, '{"data":[{"test":"qwerty"}]}');
        $builder = $this->createMock(DocumentBuilder::class);

        $builder->expects($this->once())
            ->method('build')
            ->with($view)
            ->willReturn($document);

        $listener = new JsonApiViewListener($builder);
        $listener->onKernelView($event);
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