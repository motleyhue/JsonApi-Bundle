<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response;

/**
 * Abstract Json API view supposed to get handled and converted into a response
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Response
 */
abstract class AbstractJsonApiView
{
    /**
     * Status code
     *
     * @var int
     */
    protected $status = 200;

    /**
     * Response headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}