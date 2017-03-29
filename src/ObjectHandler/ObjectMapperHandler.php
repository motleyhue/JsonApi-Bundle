<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\ObjectHandler;

use Mikemirten\Component\JsonApi\Document\ResourceObject;
use Mikemirten\Component\JsonApi\Mapper\ObjectMapper;

/**
 * Handler-bridge to the object-mapper of JsonAPI component.
 *
 * @package Mikemirten\Bundle\JsonApiBundle\ObjectHandler
 */
class ObjectMapperHandler implements ObjectHandlerInterface
{
    /**
     * JsonAPI resource object mapper
     *
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * ObjectMapperHandler constructor.
     *
     * @param ObjectMapper $mapper
     */
    public function __construct(ObjectMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($object): ResourceObject
    {
        return $this->mapper->toResource($object);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $class): bool
    {
        return true;
    }
}