<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response;

/**
 * Object Json API view
 * Passes an object to handling (serialization as an option)
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Response
 */
class JsonApiObjectView extends AbstractJsonApiView
{
    /**
     * Object supposed to be handled
     *
     * @var mixed
     */
    protected $object;

    /**
     * JsonApiObjectView constructor.
     *
     * @param mixed $object
     * @param int   $status
     * @param array $headers
     */
    public function __construct($object, int $status = 200, array $headers = [])
    {
        $this->object     = $object;
        $this->statusCode = $status;
        $this->headers    = $headers;
    }

    /**
     * Get object passed to handling
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}