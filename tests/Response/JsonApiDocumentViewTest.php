<?php

namespace Mikemirten\Bundle\JsonApiBundle;

use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiDocumentView;
use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use PHPUnit\Framework\TestCase;

class JsonApiDocumentViewTest extends TestCase
{
    public function testBasics()
    {
        $document = $this->createMock(AbstractDocument::class);
        $view     = new JsonApiDocumentView($document, 555, ['test' => 'qwerty']);

        $this->assertSame(555, $view->getStatusCode());
        $this->assertSame(['test' => 'qwerty'], $view->getHeaders());
    }

    public function testDocument()
    {
        $document = $this->createMock(AbstractDocument::class);
        $view     = new JsonApiDocumentView($document);

        $this->assertSame($document, $view->getDocument());
    }


}