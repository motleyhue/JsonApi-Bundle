<?php

namespace Mikemirten\Bundle\JsonApiBundle\ObjectHandler;

use Mikemirten\Component\JsonApi\Document\ResourceObject;
use Mikemirten\Component\JsonApi\Mapper\ObjectMapper;
use PHPUnit\Framework\TestCase;

class ObjectMapperHandlerTest extends TestCase
{
    public function testHandle()
    {
        $object   = new \stdClass();
        $resource = $this->createMock(ResourceObject::class);

        $mapper = $this->createMock(ObjectMapper::class);

        $mapper->expects($this->once())
            ->method('toResource')
            ->with($object)
            ->willReturn($resource);

        $handler = new ObjectMapperHandler($mapper);
        $result  = $handler->handle($object);

        $this->assertSame($resource, $result);
    }
}