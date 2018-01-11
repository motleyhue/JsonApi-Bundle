<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Request\Exception;

use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Invalid document type exception
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Exception
 */
class InvalidDocumentTypeException extends BadRequestHttpException
{
    /**
     * InvalidDocumentTypeException constructor.
     *
     * @param AbstractDocument $document
     * @param string           $expected
     */
    public function __construct(AbstractDocument $document, string $expected)
    {
        $message = sprintf(
            'Provided document of type "%s" does not meet expected document of type "%s"',
            get_class($document),
            $expected
        );

        parent::__construct($message);
    }
}