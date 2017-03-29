<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\ObjectHandler;

use Mikemirten\Component\JsonApi\Document\ResourceObject;

class JmsSerializerHandler implements ObjectHandlerInterface
{
    public function __construct()
    {
    }

    public function handle($object): ResourceObject
    {
        // TODO: Named mappers
    }

    public function supports(string $class): bool
    {

    }
}