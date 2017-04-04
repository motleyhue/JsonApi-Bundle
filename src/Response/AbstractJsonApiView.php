<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response;

use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\HttpAttributesAwareInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\HttpAttributesContainer;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\IncludedObjectsAwareInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\IncludedObjectsContainer;

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
    protected $postResourceCallback;

    /**
     * Set a callback to call after a resource-object has created.
     *
     * @param callable $callback
     */
    public function setPostResourceCallback(callable $callback)
    {
        $this->postResourceCallback = $callback;
    }

    /**
     * Has a callback to call after a resource-object has created ?
     *
     * @return bool
     */
    public function hasPostResourceCallback(): bool
    {
        return $this->postResourceCallback !== null;
    }

    /**
     * Get a callback to call after a resource-object has created.
     *
     * @return callable
     */
    public function getPostResourceCallback(): callable
    {
        return $this->postResourceCallback;
    }
}