<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\ObjectHandler;

use Mikemirten\Component\JsonApi\Document\ResourceObject;

/**
 * Interface of an object-handler
 *
 * @package Mikemirten\Bundle\JsonApiBundle\ObjectHandler
 */
interface ObjectHandlerInterface
{
    /**
     * Handle the object
     *
     * @param  mixed $object
     * @return ResourceObject
     */
    public function handle($object): ResourceObject;

    /**
     * Resolve if provided class is supported by handler
     *
     * @param  string $class
     * @return bool
     */
    public function supports(string $class): bool;
}