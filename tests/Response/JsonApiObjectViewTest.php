<?php

namespace Mikemirten\Bundle\JsonApiBundle;

use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiIteratorView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiObjectView;
use PHPUnit\Framework\TestCase;

class JsonApiObjectViewTest extends TestCase
{
    public function testBasics()
    {
        $object = new \stdClass();
        $view   = new JsonApiObjectView($object, 555, ['test' => 'qwerty']);

        $this->assertSame(555, $view->getStatusCode());
        $this->assertSame(['test' => 'qwerty'], $view->getHeaders());
    }

    public function testDocument()
    {
        $object = new \stdClass();
        $view   = new JsonApiObjectView($object);

        $this->assertSame($object, $view->getObject());
    }
}