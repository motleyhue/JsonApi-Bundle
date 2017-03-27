<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response;

use Mikemirten\Component\JsonApi\Document\AbstractDocument;

/**
 * Document Json API view
 * Passes a document to a request handler along with status code and headers
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Response
 */
class JsonApiDocumentView extends AbstractJsonApiView
{
    /**
     * Json API document
     *
     * @var AbstractDocument
     */
    protected $document;

    /**
     * JsonApiDocumentResponse constructor.
     *
     * @param AbstractDocument $document
     * @param int              $status
     * @param array            $headers
     */
    public function __construct(AbstractDocument $document, int $status = 200, array $headers = [])
    {
        $this->document = $document;
        $this->status   = $status;
        $this->headers  = $headers;
    }

    /**
     * Get document
     *
     * @return AbstractDocument
     */
    public function getDocument(): AbstractDocument
    {
        return $this->document;
    }
}