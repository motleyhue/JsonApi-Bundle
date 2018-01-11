<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Request;

use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\Document\NoDataDocument;
use Mikemirten\Component\JsonApi\Document\ResourceCollectionDocument;
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;
use Mikemirten\Component\JsonApi\Hydrator\DocumentHydrator;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class JsonApiDocumentParameterConverterTest extends TestCase
{
    /**
     * @dataProvider getSupportedClasses
     *
     * @param string $class
     */
    public function testSupports(string $class)
    {
        $configuration = $this->createMock(ParamConverter::class);

        $configuration->expects($this->once())
            ->method('getClass')
            ->willReturn($class);

        $hydrator  = $this->createMock(DocumentHydrator::class);
        $converter = new JsonApiDocumentParameterConverter($hydrator);

        $converter->supports($configuration);
    }

    public function testOptionalEmptyRequest()
    {
        $configuration = $this->createConfiguration(NoDataDocument::class, true);

        $request   = $this->createRequest();
        $hydrator  = $this->createMock(DocumentHydrator::class);
        $converter = new JsonApiDocumentParameterConverter($hydrator);

        $result = $converter->apply($request, $configuration);

        $this->assertFalse($result);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Request body is empty
     */
    public function testEmptyRequest()
    {
        $configuration = $this->createConfiguration();

        $request   = $this->createRequest();
        $hydrator  = $this->createMock(DocumentHydrator::class);
        $converter = new JsonApiDocumentParameterConverter($hydrator);

        $converter->apply($request, $configuration);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessageRegExp ~Invalid media\-type of request~
     */
    public function testInvalidContentType()
    {
        $configuration = $this->createConfiguration();

        $request   = $this->createRequest('{}', false);
        $hydrator  = $this->createMock(DocumentHydrator::class);
        $converter = new JsonApiDocumentParameterConverter($hydrator);

        $converter->apply($request, $configuration);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessageRegExp ~Decoding error~
     */
    public function testInvalidBody()
    {
        $configuration = $this->createConfiguration();

        $request   = $this->createRequest('{');
        $hydrator  = $this->createMock(DocumentHydrator::class);
        $converter = new JsonApiDocumentParameterConverter($hydrator);

        $converter->apply($request, $configuration);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessageRegExp ~does not meet expected document~
     */
    public function testNotExpectedDocument()
    {
        $configuration = $this->createConfiguration();

        $document = $this->createMock(SingleResourceDocument::class);
        $hydrator = $this->createMock(DocumentHydrator::class);
        $request  = $this->createRequest('{}');

        $hydrator->expects($this->once())
            ->method('hydrate')
            ->with($this->isInstanceOf(\stdClass::class))
            ->willReturn($document);

        $converter = new JsonApiDocumentParameterConverter($hydrator);

        $converter->apply($request, $configuration);
    }

    /**
     * @dataProvider getPossibleConversions
     *
     * @param string $expectedClass
     * @param string $actualClass
     */
    public function testSucceedConversion(string $expectedClass, string $actualClass)
    {
        $configuration = $this->createConfiguration($expectedClass);

        $document = $this->createMock($actualClass);
        $hydrator = $this->createMock(DocumentHydrator::class);

        $hydrator->expects($this->once())
            ->method('hydrate')
            ->with($this->isInstanceOf(\stdClass::class))
            ->willReturn($document);

        $request = $this->createRequest('{}');

        $request->attributes->expects($this->once())
            ->method('set')
            ->with('document', $document);

        $converter = new JsonApiDocumentParameterConverter($hydrator);

        $result = $converter->apply($request, $configuration);

        $this->assertTrue($result);
    }

    /**
     * Create mock of parameter converter configuration
     *
     * @param  string $class
     * @param  bool   $optional
     * @return ParamConverter
     */
    protected function createConfiguration(string $class = NoDataDocument::class, bool $optional = false): ParamConverter
    {
        $configuration = $this->createMock(ParamConverter::class);

        $configuration->method('getClass')
            ->willReturn($class);

        $configuration->method('getName')
            ->willReturn('document');

        $configuration->method('isOptional')
            ->willReturn($optional);

        return $configuration;
    }

    /**
     * Create mock of request
     *
     * @param string $body
     * @param bool   $apiJsonHeader
     * @return Request
     */
    protected function createRequest(string $body = '', bool $apiJsonHeader = true): Request
    {
        $request = $this->createMock(Request::class);

        $request->method('getContent')
            ->willReturn($body);

        $request->attributes = $this->createMock(ParameterBag::class);
        $request->headers    = $this->createMock(HeaderBag::class);

        $request->headers->method('get')
            ->with('Content-Type')
            ->willReturn($apiJsonHeader ? ['application/vnd.api+json'] : []);

        return $request;
    }

    /**
     * Get supported classes for testing
     *
     * @return array
     */
    public function getSupportedClasses(): array
    {
        return [
            [ NoDataDocument::class             ],
            [ AbstractDocument::class           ],
            [ SingleResourceDocument::class     ],
            [ ResourceCollectionDocument::class ]
        ];
    }

    /**
     * Get possible classes conversion for testing
     *
     * @return array
     */
    public function getPossibleConversions(): array
    {
        return [
        //    Expected                           Actual
            [ NoDataDocument::class,             NoDataDocument::class             ],
            [ SingleResourceDocument::class,     SingleResourceDocument::class     ],
            [ ResourceCollectionDocument::class, ResourceCollectionDocument::class ],
            [ AbstractDocument::class,           NoDataDocument::class             ],
            [ AbstractDocument::class,           SingleResourceDocument::class     ],
            [ AbstractDocument::class,           ResourceCollectionDocument::class ],
        ];
    }
}