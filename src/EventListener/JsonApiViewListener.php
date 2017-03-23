<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\EventListener;

use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\Document\ErrorObject;
use Mikemirten\Component\JsonApi\Document\JsonApiObject;
use Mikemirten\Component\JsonApi\Document\NoDataDocument;
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
     * On Kernel View event handler
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();

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