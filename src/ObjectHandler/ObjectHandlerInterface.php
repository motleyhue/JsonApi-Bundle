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
     * Returns a list of supported classes
     *
     * @return array
     */
    public function supports(): array;
}