<?php

namespace Mikemirten\Bundle\JsonApiBundle\Behaviour;

use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\HttpAttributesContainer;
use PHPUnit\Framework\TestCase;

class HttpAttributesContainerTest extends TestCase
{
    public function testStatusCode()
    {
        $attributesContainer = new class {
            use HttpAttributesContainer;
        };


        $attributesContainer->setStatusCode(123);

        $this->assertSame(123, $attributesContainer->getStatusCode());
    }

    public function testHeaders()
    {
        $attributesContainer = new class {
            use HttpAttributesContainer;
        };

        $this->assertFalse($attributesContainer->hasHeader('test'));

        $attributesContainer->setHeader('test', 'qwerty');

        $this->assertTrue($attributesContainer->hasHeader('test'));
        $this->assertSame(['test' => 'qwerty'], $attributesContainer->getHeaders());
    }

    public function testEmptyHeaders()
    {
        $attributesContainer = new class {
            use HttpAttributesContainer;
        };

        $this->assertSame([], $attributesContainer->getHeaders());
    }

    public function testSetHeaders()
    {
        $attributesContainer = new class {
            use HttpAttributesContainer;
        };

        $attributesContainer->setHeaders(['test' => 'qwerty']);

        $this->assertSame(['test' => 'qwerty'], $attributesContainer->getHeaders());
    }
}