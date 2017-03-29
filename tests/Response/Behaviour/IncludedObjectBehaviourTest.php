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
}