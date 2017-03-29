<?php

namespace Mikemirten\Bundle\JsonApiBundle;

use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiIteratorView;
use PHPUnit\Framework\TestCase;

class JsonApiIteratorViewTest extends TestCase
{
    public function testBasics()
    {
        $iterator = new \ArrayIterator([]);
        $view     = new JsonApiIteratorView($iterator, 555, ['test' => 'qwerty']);

        $this->assertSame(555, $view->getStatusCode());
        $this->assertSame(['test' => 'qwerty'], $view->getHeaders());
    }

    public function testDocument()
    {
        $iterator = new \ArrayIterator([]);
        $view     = new JsonApiIteratorView($iterator);

        $this->assertInstanceOf('IteratorAggregate', $view);
        $this->assertSame($iterator, $view->getIterator());
    }
}