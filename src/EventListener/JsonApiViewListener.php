<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\EventListener;

use Mikemirten\Bundle\JsonApiBundle\ObjectHandler\ObjectHandlerInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\AbstractJsonApiView;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\HttpAttributesAwareInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\IncludedObjectsAwareInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\IncludedObjectsContainer;
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
     * Object-handlers resolved by supported class
     *
     * @var ObjectHandlerInterface[]
     */
    protected $resolvedObjectHandlers = [];

    /**
     * Add object-handler
     *
     * @param ObjectHandlerInterface $handler
     */
    public function addObjectHandler(ObjectHandlerInterface $handler)
    {
        $this->objectHandlers[] = $handler;
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

        // An object for serialization wrapped into view
        if ($result instanceof JsonApiObjectView) {
            return $this->handleObjectView($result);
        }

        // An iterator of objects for serialization wrapped into view
        if ($result instanceof JsonApiIteratorView) {
            return $this->handleIteratorView($result);
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
     * Handle single object Json API view
     *
     * @param  JsonApiObjectView $view
     * @return Response
     */
    protected function handleObjectView(JsonApiObjectView $view): Response
    {
        $resource = $this->handleObject($view->getObject());
        $this->handleResourceCallback($view, $resource);

        $document = new SingleResourceDocument($resource);
        $document->setJsonApi(new JsonApiObject());

        $this->handleIncludedResources($document, $view);
        $this->handleDocumentCallback($view, $document);

        $response = $this->createResponse($document);

        $this->handleHttpAttributes($response, $view);
        return $response;
    }

    /**
     * Handle a callback after a resource-object has created.
     *
     * @param AbstractJsonApiView $view
     * @param ResourceObject      $resource
     */
    protected function handleResourceCallback(AbstractJsonApiView $view, ResourceObject $resource)
    {
        if ($view->hasResourceCallback()) {
            $callback = $view->getResourceCallback();
            $callback($resource);
        }
    }

    /**
     * Handle a callback after a document has created.
     *
     * @param AbstractJsonApiView $view
     * @param AbstractDocument    $document
     */
    protected function handleDocumentCallback(AbstractJsonApiView $view, AbstractDocument $document)
    {
        if ($view->hasDocumentCallback()) {
            $callback = $view->getDocumentCallback();
            $callback($document);
        }
    }

    /**
     * Handle supposed to be included to document resources
     *
     * @param AbstractDocument              $document
     * @param IncludedObjectsAwareInterface $view
     */
    protected function handleIncludedResources(AbstractDocument $document, IncludedObjectsAwareInterface $view)
    {
        foreach ($view->getIncludedObjects() as $object)
        {
            $resource = $this->handleObject($object);
            $document->addIncludedResource($resource);
        }
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
            $this->handleResourceCallback($view, $resource);

            $document->addResource($resource);
        }

        $this->handleIncludedResources($document, $view);
        $this->handleDocumentCallback($view, $document);

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
     * Handle object
     *
     * @param  $object
     * @return ResourceObject
     */
    protected function handleObject($object): ResourceObject
    {
        if ($object instanceof ResourceObject) {
            return $object;
        }

        $class = get_class($object);

        return $this->getHandler($class)->handle($object);
    }

    /**
     * Get handler supports given class
     *
     * @param  string $class
     * @return ObjectHandlerInterface
     * @throws \LogicException
     */
    protected function getHandler(string $class): ObjectHandlerInterface
    {
        if (isset($this->resolvedObjectHandlers[$class])) {
            return $this->resolvedObjectHandlers[$class];
        }

        foreach ($this->objectHandlers as $handler) {
            if ($handler->supports($class)) {
                $this->resolvedObjectHandlers[$class] = $handler;
                return $handler;
            }
        }

        throw new \LogicException(sprintf('Class "%s" is not supported by known handles.', $class));
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