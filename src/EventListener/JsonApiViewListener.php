<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\EventListener;

use Mikemirten\Bundle\JsonApiBundle\ObjectHandler\ObjectHandlerInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\AbstractJsonApiView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiDocumentView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiIteratorView;
use Mikemirten\Bundle\JsonApiBundle\Response\JsonApiObjectView;
use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\Document\ErrorObject;
use Mikemirten\Component\JsonApi\Document\JsonApiObject;
use Mikemirten\Component\JsonApi\Document\NoDataDocument;
use Mikemirten\Component\JsonApi\Document\ResourceCollectionDocument;
use Mikemirten\Component\JsonApi\Document\ResourceObject;
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * JsonApi view listener
 * Handles a JsonApi-document or its part responded by controller
 *
 * @package Mikemirten\Bundle\JsonApiBundle\EventListener
 */
class JsonApiViewListener
{
    /**
     * Object-handlers
     *
     * @var ObjectHandlerInterface[]
     */
    protected $objectHandlers = [];

    /**
     * Add object-handler
     *
     * @param ObjectHandlerInterface $handler
     */
    public function addObjectHandler(ObjectHandlerInterface $handler)
    {
        foreach ($handler->supports() as $class)
        {
            $this->objectHandlers[$class] = $handler;
        }
    }

    /**
     * On Kernel View event handler
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();

        if ($result instanceof AbstractJsonApiView) {
            $event->setResponse($this->handleView($result));
            return;
        }

        if ($result instanceof AbstractDocument) {
            $event->setResponse($this->createResponse($result));
            return;
        }

        if ($result instanceof ResourceObject) {
            $event->setResponse($this->handleResource($result));
            return;
        }

        if ($result instanceof ErrorObject) {
            $event->setResponse($this->handleError($result));
        }
    }

    /**
     * Handle Json API view
     *
     * @param  AbstractJsonApiView $view
     * @return Response
     */
    protected function handleView(AbstractJsonApiView $view): Response
    {
        if ($view instanceof JsonApiDocumentView) {
            return $this->handleDocumentView($view);
        }

        if ($view instanceof JsonApiObjectView) {
            return $this->handleObjectView($view);
        }

        if ($view instanceof JsonApiIteratorView) {
            return $this->handleIteratorView($view);
        }

        throw new \LogicException(sprintf('Unsupported extension "%s" of JsonAPI View given.', get_class($view)));
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

        $this->handleResponseExtras($response, $view);
        return $response;
    }

    /**
     * Handle single object Json API view
     *
     * @param  JsonApiObjectView $view
     * @return Response
     */
    protected function handleObjectView(JsonApiObjectView $view): Response
    {
        $resource = $this->handleObject($view->getObject());
        $response = $this->handleResource($resource);

        $this->handleResponseExtras($response, $view);
        return $response;
    }

    /**
     * Handle object-iterator
     *
     * @param  JsonApiIteratorView $view
     * @return Response
     */
    protected function handleIteratorView(JsonApiIteratorView $view): Response
    {
        $document = new ResourceCollectionDocument();
        $document->setJsonApi(new JsonApiObject());

        foreach ($view as $object)
        {
            $resource = $this->handleObject($object);

            $document->addResource($resource);
        }

        $response = $this->createResponse($document);

        $this->handleResponseExtras($response, $view);
        return $response;
    }

    /**
     * Handle response data besides of the document itself
     * 
     * @param Response            $response
     * @param AbstractJsonApiView $view
     */
    protected function handleResponseExtras(Response $response, AbstractJsonApiView $view)
    {
        $response->setStatusCode($view->getStatus());
        $response->headers->add($view->getHeaders());
    }

    /**
     * Handle object
     *
     * @param  $object
     * @return ResourceObject
     */
    protected function handleObject($object): ResourceObject
    {
        $class = get_class($object);
        
        if (isset($this->objectHandlers[$class])) {
            return $this->objectHandlers[$class]->handle($object);
        }

        throw new \LogicException(sprintf('Unsupported instance of "%s" given.', $class));
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