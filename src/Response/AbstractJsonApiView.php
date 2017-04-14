<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response;

use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\HttpAttributesAwareInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\HttpAttributesContainer;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\IncludedObjectsAwareInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\IncludedObjectsContainer;
use Mikemirten\Component\JsonApi\Mapper\Definition\Link;

/**
 * Abstract Json API view supposed to get handled and converted into a response
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Response
 */
abstract class AbstractJsonApiView implements HttpAttributesAwareInterface, IncludedObjectsAwareInterface
{
    use HttpAttributesContainer;
    use IncludedObjectsContainer;

    /**
     * Callback to call after a resource-object has created.
     *
     * @var callable
     */
    protected $resourceCallback;

    /**
     * Callback to call after a document has created.
     *
     * @var callable
     */
    protected $documentCallback;

    /**
     * Definitions of links have to be added to document
     *
     * @var Link[]
     */
    protected $documentLinks;

    /**
     * Set a callback to call after a resource-object has created.
     *
     * @param callable $callback
     */
    public function setResourceCallback(callable $callback)
    {
        $this->resourceCallback = $callback;
    }

    /**
     * Has a callback to call after a resource-object has created ?
     *
     * @return bool
     */
    public function hasResourceCallback(): bool
    {
        return $this->resourceCallback !== null;
    }

    /**
     * Get a callback to call after a resource-object has created.
     *
     * @return callable
     */
    public function getResourceCallback(): callable
    {
        return $this->resourceCallback;
    }

    /**
     * Set a callback to call after a document has created
     *
     * @param callable $callback
     */
    public function setDocumentCallback(callable $callback)
    {
        $this->documentCallback = $callback;
    }

    /**
     * Has a callback to call after a document has created
     *
     * @return bool
     */
    public function hasDocumentCallback(): bool
    {
        return $this->documentCallback !== null;
    }

    /**
     * Get a callback to call after a document has created
     *
     * @return callable
     */
    public function getDocumentCallback(): callable
    {
        return $this->documentCallback;
    }

    /**
     * Add a definition of link have to be added to document
     *
     * @param Link $link
     */
    public function addDocumentLink(Link $link)
    {
        $name = $link->getName();

        if (isset($this->documentLinks[$name])) {
            throw new \LogicException(sprintf('Link "%s" already added to document.'));
        }

        $this->documentLinks[$name] = $link;
    }

    /**
     * Get definitions of links have to be added to document
     *
     * @return Link[]
     */
    public function getDocumentLinks(): array
    {
        return $this->documentLinks;
    }
}