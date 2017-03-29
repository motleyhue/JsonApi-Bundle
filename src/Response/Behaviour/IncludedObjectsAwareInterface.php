<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response\Behaviour;

/**
 * Interface of an object aware of included objects
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Response\Behaviour
 */
interface IncludedObjectsAwareInterface
{
    /**
     * Add included object to document
     *
     * @param $object
     */
    public function addIncludedObject($object);

    /**
     * Get objects included to document
     *
     * @return array
     */
    public function getIncludedObjects(): array;
}