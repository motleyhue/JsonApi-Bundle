<?php

namespace Mikemirten\Bundle\JsonApiBundle;

use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiIteratorView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiObjectView;
use Mikemirten\Component\JsonApi\Mapper\Definition\Link;
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

    public function testDocumentLinks()
    {
        $object = new \stdClass();
        $view   = new JsonApiObjectView($object);

        $link = $this->createMock(Link::class);

        $link->expects($this->once())
            ->method('getName')
            ->willReturn('test');

        $view->addDocumentLink($link);

        $this->assertSame(['test' => $link], $view->getDocumentLinks());
    }
}