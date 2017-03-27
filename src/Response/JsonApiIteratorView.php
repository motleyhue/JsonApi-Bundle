<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response;

/**
 * Iterator Json API view
 * Works with an iterator supposed to provide data for handling (serialization as an option)
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Response
 */
class JsonApiIteratorView extends AbstractJsonApiView implements \IteratorAggregate
{
    /**
     * Iterator supposed to provide data for handling
     * 
     * @var \Traversable
     */
    protected $iterator;

    /**
     * JsonApiIteratorView constructor.
     *
     * @param \Traversable $iterator
     * @param int          $status
     * @param array        $headers
     */
    public function __construct(\Traversable $iterator, int $status = 200, array $headers = [])
    {
        $this->iterator = $iterator;
        $this->status   = $status;
        $this->headers  = $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->iterator;
    }
}