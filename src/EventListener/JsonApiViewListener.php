<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\EventListener;

use Mikemirten\Bundle\JsonApiBundle\Builder\DocumentBuilder;
use Mikemirten\Bundle\JsonApiBundle\Response\AbstractJsonApiView;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\HttpAttributesAwareInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiDocumentView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Mikemirten\Component\JsonApi\Document\{
    AbstractDocument,
    NoDataDocument,
    SingleResourceDocument,
    ErrorObject,
    JsonApiObject,
    ResourceObject
};

/**
 * JsonApi view listener
 * Handles a JsonApi-document or its part responded by controller
 *
 * @package Mikemirten\Bundle\JsonApiBundle\EventListener
 */
class JsonApiViewListener
{
    /**
     * Document builder
     *
     * @var DocumentBuilder
     */
    protected $documentBuilder;

    /**
     * JsonApiViewListener constructor.
     *
     * @param DocumentBuilder $builder
     */
    public function __construct(DocumentBuilder $builder)
    {
        $this->documentBuilder = $builder;
    }

    /**
     * On Kernel View event handler
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();

        if (! is_object($result)) {
            return;
        }

        $response = $this->handleResult($result);

        if ($response !== null) {
            $event->setResponse($response);
        }
    }

    /**
     * Handle result
     *
     * @param  mixed $result
     * @return Response | null
     */
    protected function handleResult($result)
    {
        // Json API document
        if ($result instanceof AbstractDocument) {
            return $this->createResponse($result);
        }

        // Json API document wrapped into view
        if ($result instanceof JsonApiDocumentView) {
            return $this->handleDocumentView($result);
        }

        // An object or an iterator of objects for serialization wrapped into view
        if ($result instanceof AbstractJsonApiView) {
            return $this->handleView($result);
        }

        // A resource-object of Json API document
        if ($result instanceof ResourceObject) {
            return $this->handleResource($result);
        }

        // An error-object of Json API document
        if ($result instanceof ErrorObject) {
            return $this->handleError($result);
        }
    }

    /**
     * Handle document Json API view
     *
     * @param  JsonApiDocumentView $view
     * @return Response
     */
    protected function handleDocumentView(JsonApiDocumentView $view)
    {
        $document = $view->getDocument();
        $response = $this->createResponse($document);

        $this->handleHttpAttributes($response, $view);
        return $response;
    }

    /**
     * Handle a JSON-API view contains an object(s) for serialization
     *
     * @param  AbstractJsonApiView $view
     * @return Response
     */
    protected function handleView(AbstractJsonApiView $view)
    {
        $document = $this->documentBuilder->build($view);
        $response = $this->createResponse($document);

        $this->handleHttpAttributes($response, $view);
        return $response;
    }

    /**
     * Handle response data besides of the document itself
     * 
     * @param Response                     $response
     * @param HttpAttributesAwareInterface $view
     */
    protected function handleHttpAttributes(Response $response, HttpAttributesAwareInterface $view)
    {
        $response->setStatusCode($view->getStatusCode());
        $response->headers->add($view->getHeaders());
    }

    /**
     * Handle single resource object
     *
     * @param  ResourceObject $resource
     * @return Response
     */
    protected function handleResource(ResourceObject $resource): Response
    {
        $document = new SingleResourceDocument($resource);
        $document->setJsonApi(new JsonApiObject());

        return $this->createResponse($document);
    }

    /**
     * Handle error
     *
     * @param  ErrorObject $error
     * @return Response
     */
    protected function handleError(ErrorObject $error): Response
    {
        $document = new NoDataDocument();
        $document->setJsonApi(new JsonApiObject());
        $document->addError($error);

        return $this->createResponse($document);
    }

    /**
     * Create response
     *
     * @param  AbstractDocument $document
     * @return Response
     */
    protected function createResponse(AbstractDocument $document): Response
    {
        $encoded  = $this->encode($document->toArray());
        $response = new Response($encoded);

        $response->headers->set('Content-Type', 'application/vnd.api+json');

        return $response;
    }

    /**
     * Encode object into a json-string
     *
     * @param  mixed $object
     * @return string
     * @throws \LogicException
     */
    protected function encode($object): string
    {
        $encoded = json_encode($object);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $encoded;
        }

        throw new \LogicException('Encoding error: ' . json_last_error_msg());
    }
}