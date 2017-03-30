<?php

namespace Mikemirten\Bundle\JsonApiBundle\Behaviour;

use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\IncludedObjectsContainer;
use PHPUnit\Framework\TestCase;

class IncludedObjectBehaviourTest extends TestCase
{
    public function testIncluded()
    {
        $object = new \stdClass();

        $includedContainer = new class {
            use IncludedObjectsContainer;
        };

        $includedContainer->addIncludedObject($object);

        $this->assertSame([$object], $includedContainer->getIncludedObjects());
    }

    public function testEmptyIncluded()
    {
        $includedContainer = new class {
            use IncludedObjectsContainer;
        };

        $this->assertSame([], $includedContainer->getIncludedObjects());
    }

    public function testIncludedIterator()
    {
        $includedContainer = new class {
            use IncludedObjectsContainer;
        };

        $object   = new \stdClass();
        $iterator = new \ArrayIterator([$object]);

        $includedContainer->addIncludedIterator($iterator);

        $this->assertSame([$object], $includedContainer->getIncludedObjects());
    }
}