<?php

namespace Mikemirten\Bundle\JsonApiBundle\Builder;

use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiIteratorView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiObjectView;
use Mikemirten\Component\JsonApi\Document\LinkObject;
use Mikemirten\Component\JsonApi\Document\ResourceCollectionDocument;
use Mikemirten\Component\JsonApi\Document\ResourceObject;
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;
use Mikemirten\Component\JsonApi\Mapper\Handler\LinkRepository\RepositoryInterface;
use Mikemirten\Component\JsonApi\Mapper\Handler\LinkRepository\RepositoryProvider;
use Mikemirten\Component\JsonApi\Mapper\ObjectMapper;
use Mikemirten\Component\JsonApi\Mapper\Definition\Link as LinkDefinition;
use Mikemirten\Component\JsonApi\Mapper\Handler\LinkRepository\Link as LinkData;
use PHPUnit\Framework\TestCase;

/**
 * @group   document-builder
 * @package Mikemirten\Bundle\JsonApiBundle\Builder
 */
class DocumentBuilderTest extends TestCase
{
    public function testObjectView()
    {
        $object   = new \stdClass();
        $resource = $this->createMock(ResourceObject::class);

        $view = $this->createMock(JsonApiObjectView::class);

        $view->expects($this->once())
            ->method('getObject')
            ->willReturn($object);

        $mapper = $this->createMock(ObjectMapper::class);

        $mapper->expects($this->once())
            ->method('toResource')
            ->with($object)
            ->willReturn($resource);

        $provider = $this->createMock(RepositoryProvider::class);
        $builder  = new DocumentBuilder($mapper, $provider);
        $document = $builder->build($view);

        /** @var SingleResourceDocument $document */

        $this->assertInstanceOf(SingleResourceDocument::class, $document);
        $this->assertSame($resource, $document->getResource());
    }

    public function testObjectViewIncluded()
    {
        $object   = new \stdClass();
        $resource = $this->createMock(ResourceObject::class);

        $view = $this->createMock(JsonApiObjectView::class);

        $view->expects($this->once())
            ->method('getIncludedObjects')
            ->willReturn([$object]);

        $mapper = $this->createMock(ObjectMapper::class);

        $mapper->expects($this->at(1))
            ->method('toResource')
            ->with($object)
            ->willReturn($resource);

        $provider = $this->createMock(RepositoryProvider::class);
        $builder  = new DocumentBuilder($mapper, $provider);
        $document = $builder->build($view);

        /** @var SingleResourceDocument $document */

        $this->assertInstanceOf(SingleResourceDocument::class, $document);
        $this->assertSame($resource, $document->getIncludedResources()[0]);
    }

    public function testIteratorView()
    {
        $object   = new \stdClass();
        $iterator = new \ArrayIterator([$object]);
        $resource = $this->createMock(ResourceObject::class);

        $view = $this->createMock(JsonApiIteratorView::class);

        $view->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);

        $mapper = $this->createMock(ObjectMapper::class);

        $mapper->expects($this->once())
            ->method('toResource')
            ->with($object)
            ->willReturn($resource);

        $provider = $this->createMock(RepositoryProvider::class);
        $builder  = new DocumentBuilder($mapper, $provider);
        $document = $builder->build($view);

        /** @var ResourceCollectionDocument $document */

        $this->assertInstanceOf(ResourceCollectionDocument::class, $document);
        $this->assertSame($resource, $document->getFirstResource());
    }

    public function testIteratorViewIncluded()
    {
        $object   = new \stdClass();
        $resource = $this->createMock(ResourceObject::class);

        $view = $this->createMock(JsonApiIteratorView::class);

        $view->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator());

        $view->expects($this->once())
            ->method('getIncludedObjects')
            ->willReturn([$object]);

        $mapper = $this->createMock(ObjectMapper::class);

        $mapper->expects($this->once())
            ->method('toResource')
            ->with($object)
            ->willReturn($resource);

        $provider = $this->createMock(RepositoryProvider::class);
        $builder  = new DocumentBuilder($mapper, $provider);
        $document = $builder->build($view);

        /** @var ResourceCollectionDocument $document */

        $this->assertInstanceOf(ResourceCollectionDocument::class, $document);
        $this->assertSame($resource, $document->getIncludedResources()[0]);
    }

    public function testResourceCallback()
    {
        $object = new \stdClass();
        $view   = $this->createMock(JsonApiObjectView::class);

        $view->method('getObject')
            ->willReturn($object);

        $view->method('hasResourceCallback')
            ->willReturn(true);

        $called = false;

        $view->expects($this->once())
            ->method('getResourceCallback')
            ->willReturn(function($resource) use(&$called) {
                $this->assertInstanceOf(ResourceObject::class, $resource);
                $called = true;
            });

        $mapper   = $this->createMock(ObjectMapper::class);
        $provider = $this->createMock(RepositoryProvider::class);

        $builder = new DocumentBuilder($mapper, $provider);
        $builder->build($view);

        $this->assertTrue($called);
    }

    public function testDocumentCallback()
    {
        $object = new \stdClass();
        $view   = $this->createMock(JsonApiObjectView::class);

        $view->method('getObject')
            ->willReturn($object);

        $view->method('hasDocumentCallback')
            ->willReturn(true);

        $called = false;

        $view->expects($this->once())
            ->method('getDocumentCallback')
            ->willReturn(function($resource) use(&$called) {
                $this->assertInstanceOf(SingleResourceDocument::class, $resource);
                $called = true;
            });

        $mapper   = $this->createMock(ObjectMapper::class);
        $provider = $this->createMock(RepositoryProvider::class);

        $builder = new DocumentBuilder($mapper, $provider);
        $builder->build($view);

        $this->assertTrue($called);
    }

    public function testResourceCallbackWithIterator()
    {
        $object   = new \stdClass();
        $iterator = new \ArrayIterator([$object]);

        $view = $this->createMock(JsonApiIteratorView::class);

        $view->method('getIterator')
            ->willReturn($iterator);

        $view->method('hasResourceCallback')
            ->willReturn(true);

        $called = false;

        $view->expects($this->once())
            ->method('getResourceCallback')
            ->willReturn(function($resource) use(&$called) {
                $this->assertInstanceOf(ResourceObject::class, $resource);
                $called = true;
            });

        $mapper   = $this->createMock(ObjectMapper::class);
        $provider = $this->createMock(RepositoryProvider::class);

        $builder = new DocumentBuilder($mapper, $provider);
        $builder->build($view);

        $this->assertTrue($called);
    }

    public function testDocumentCallbackWithIterator()
    {
        $object   = new \stdClass();
        $iterator = new \ArrayIterator([$object]);

        $view = $this->createMock(JsonApiIteratorView::class);

        $view->method('getIterator')
            ->willReturn($iterator);

        $view->method('hasDocumentCallback')
            ->willReturn(true);

        $called = false;

        $view->expects($this->once())
            ->method('getDocumentCallback')
            ->willReturn(function($resource) use(&$called) {
                $this->assertInstanceOf(ResourceCollectionDocument::class, $resource);
                $called = true;
            });

        $mapper   = $this->createMock(ObjectMapper::class);
        $provider = $this->createMock(RepositoryProvider::class);

        $builder = new DocumentBuilder($mapper, $provider);
        $builder->build($view);

        $this->assertTrue($called);
    }

    public function testDocumentLinks()
    {
        $object = new \stdClass();

        $linkDefinition = $this->createLinkDefinition('test_name', 'test_repository', 'test_link');

        $view = $this->createMock(JsonApiObjectView::class);

        $view->method('getObject')
            ->willReturn($object);

        $view->expects($this->once())
            ->method('getDocumentLinks')
            ->willReturn([$linkDefinition]);

        $mapper   = $this->createMock(ObjectMapper::class);
        $provider = $this->createLinkProvider('test_repository', 'test_link', 'http://test.com');

        $builder  = new DocumentBuilder($mapper, $provider);
        $document = $builder->build($view);

        $links = $document->getLinks();

        $this->assertCount(1, $links);
        $this->assertArrayHasKey('test_name', $links);
        $this->assertInstanceOf(LinkObject::class, $links['test_name']);
        $this->assertSame('http://test.com', $links['test_name']->getReference());
    }

    /**
     * Create mock of repository provider
     *
     * @param  string $repositoryName
     * @param  string $linkName
     * @param  string $reference
     * @return RepositoryProvider
     */
    protected function createLinkProvider(string $repositoryName, string $linkName, string $reference): RepositoryProvider
    {
        $link = $this->createMock(LinkData::class);

        $link->expects($this->once())
            ->method('getReference')
            ->willReturn($reference);

        $repository = $this->createMock(RepositoryInterface::class);

        $repository->expects($this->once())
            ->method('getLink')
            ->with($linkName)
            ->willReturn($link);

        $provider = $this->createMock(RepositoryProvider::class);

        $provider->expects($this->once())
            ->method('getRepository')
            ->with($repositoryName)
            ->willReturn($repository);

        return $provider;
    }

    /**
     * Create mock of link's definition
     *
     * @param  string $name
     * @param  string $repositoryName
     * @param  string $linkName
     * @return LinkDefinition
     */
    protected function createLinkDefinition(string $name, string $repositoryName, string $linkName): LinkDefinition
    {
        $link = $this->createMock(LinkDefinition::class);

        $link->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $link->expects($this->once())
            ->method('getRepositoryName')
            ->willReturn($repositoryName);

        $link->expects($this->once())
            ->method('getLinkName')
            ->willReturn($linkName);

        return $link;
    }
}