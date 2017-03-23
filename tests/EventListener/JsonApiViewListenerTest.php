<?php

namespace Mikemirten\Bundle\JsonApiBundle\EventListener;

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