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


        $attributesContainer->setHeader('test', 'qwerty');

        $this->assertSame(['test' => 'qwerty'], $attributesContainer->getHeaders());
    }
}