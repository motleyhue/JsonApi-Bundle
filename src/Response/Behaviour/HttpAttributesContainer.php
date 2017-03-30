<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response\Behaviour;

trait HttpAttributesContainer
{
    /**
     * Status code
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Response headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Set status code
     *
     * @param  int $code
     * @return mixed
     */
    public function setStatusCode(int $code)
    {
        $this->statusCode = $code;
    }

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set header
     *
     * @param  string $name
     * @param  string $value
     * @return mixed
     */
    public function setHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Set headers
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value)
        {
            $this->headers[$name] = $value;
        }
    }

    /**
     * Has header ?
     *
     * @param  string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
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