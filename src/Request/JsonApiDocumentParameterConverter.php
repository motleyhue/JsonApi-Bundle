<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Request;

use Mikemirten\Bundle\JsonApiBundle\Exception\InvalidDocumentTypeException;
use Mikemirten\Bundle\JsonApiBundle\Exception\InvalidMediaTypeException;
use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\Document\NoDataDocument;
use Mikemirten\Component\JsonApi\Document\ResourceCollectionDocument;
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;
use Mikemirten\Component\JsonApi\Exception\InvalidDocumentException;
use Mikemirten\Component\JsonApi\Hydrator\DocumentHydrator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Parameter converter of request's body into a JsonAPI document
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Request
 */
class JsonApiDocumentParameterConverter implements ParamConverterInterface
{
    /**
     * @var DocumentHydrator
     */
    protected $hydrator;

    /**
     * JsonApiDocumentParameterConverter constructor.
     *
     * @param DocumentHydrator $hydrator
     */
    public function __construct(DocumentHydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $content = $this->resolveRequestBody($request, $configuration);

        if ($content === null) {
            return false;
        }

        $document = $this->processDocument($content);
        $expected = $configuration->getClass();

        if ($expected === AbstractDocument::class || $document instanceof $expected) {
            $request->attributes->set($configuration->getName(), $document);
            return true;
        }

        throw new InvalidDocumentTypeException($document, $expected);
    }

    /**
     * Resolve request body
     *
     * @param  Request        $request
     * @param  ParamConverter $configuration
     * @throws BadRequestHttpException
     * @return string | null
     */
    protected function resolveRequestBody(Request $request, ParamConverter $configuration)
    {
        $isOptional = $configuration->isOptional();
        $isJsonApi  = $this->isContentTypeValid($request);

        if (! $isOptional && ! $isJsonApi) {
            throw new InvalidMediaTypeException($request);
        }

        $content = $request->getContent();

        if (! empty($content)) {
            return $content;
        }

        if (! $isOptional) {
            throw new BadRequestHttpException('Request body is empty');
        }
    }

    /**
     * Decode and hydrate document from raw content
     *
     * @param  string $content
     * @return AbstractDocument
     * @throws BadRequestHttpException
     */
    protected function processDocument(string $content): AbstractDocument
    {
        $decoded = $this->decodeContent($content);

        try {
            $document = $this->hydrator->hydrate($decoded);
        }
        catch (InvalidDocumentException $exception) {
            throw new BadRequestHttpException('Document hydration error: ' . $exception->getMessage(), $exception);
        }

        return $document;
    }

    /**
     * Is content type of request valid ?
     *
     * @param  Request $request
     * @return bool
     */
    protected function isContentTypeValid(Request $request): bool
    {
        $contentType = $request->headers->get('Content-Type', null, false);

        foreach ($contentType as $header)
        {
            if (strpos(ltrim($header), 'application/vnd.api+json') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Decode JSON
     *
     * @param  string $content
     * @return mixed
     * @throws BadRequestHttpException
     */
    protected function decodeContent(string $content): \stdClass
    {
        $decoded = json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException('Decoding error: ' . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return in_array(
            $configuration->getClass(),
            [
                NoDataDocument::class,
                AbstractDocument::class,
                SingleResourceDocument::class,
                ResourceCollectionDocument::class
            ],
            true
        );
    }
}