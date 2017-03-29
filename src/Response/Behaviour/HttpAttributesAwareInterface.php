<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response\Behaviour;

/**
 * Interface of an object aware of HTTP-attributes (Status code, headers).
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Response\Behaviour
 */
interface HttpAttributesAwareInterface
{
    /**
     * Set status code
     *
     * @param  int $code
     * @return mixed
     */
    public function setStatusCode(int $code);

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * Set header
     *
     * @param  string $name
     * @param  string $value
     * @return mixed
     */
    public function setHeader(string $name, string $value);

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders(): array;
}