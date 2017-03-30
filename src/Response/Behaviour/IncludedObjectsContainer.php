<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response\Behaviour;

/**
 * Behaviour of a container of included objects
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Response\Behaviour
 */
trait IncludedObjectsContainer
{
    /**
     * Objects supposed to be included to document
     *
     * @var array
     */
    protected $includedObjects = [];

    /**
     * Add included object to document
     *
     * @param mixed $object
     */
    public function addIncludedObject($object)
    {
        $this->includedObjects[] = $object;
    }

    /**
     * Add an iterator contains objects should be included
     *
     * @param \Traversable $iterator
     */
    public function addIncludedIterator(\Traversable $iterator)
    {
        foreach ($iterator as $object)
        {
            $this->includedObjects[] = $object;
        }
    }

    /**
     * Get objects included to document
     *
     * @return array
     */
    public function getIncludedObjects(): array
    {
        return $this->includedObjects;
    }
}