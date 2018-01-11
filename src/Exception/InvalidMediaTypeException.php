<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Exception;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Invalid request media-type exception
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Exception
 */
class InvalidMediaTypeException extends BadRequestHttpException
{
    /**
     * InvalidMediaTypeException constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $message = sprintf(
            'Invalid media-type of request, "application/vnd.api+json" expected, "%s" given.',
            implode(', ', $request->headers->get('Content-Type', null, false))
        );

        parent::__construct($message);
    }
}