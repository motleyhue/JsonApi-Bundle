<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Request;

use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\Document\NoDataDocument;
use Mikemirten\Component\JsonApi\Document\ResourceCollectionDocument;
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;
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

        $decoded  = $this->decodeContent($content);
        $document = $this->hydrator->hydrate($decoded);
        $expected = $configuration->getClass();

        if ($expected === AbstractDocument::class || $document instanceof $expected) {
            $request->attributes->set($configuration->getName(), $document);
            return true;
        }

        throw new BadRequestHttpException(sprintf(
            'Provided document of type "%s" does not meet expected document of type "%s"',
            get_class($document),
            $expected
        ));
    }

    /**
     * Resolve request body
     *
     * @param  Request        $request
     * @param  ParamConverter $configuration
     * @throws BadRequestHttpException
     */
    protected function resolveRequestBody(Request $request, ParamConverter $configuration)
    {
        $isOptional = $configuration->isOptional();
        $isJsonApi  = $request->headers->contains('Content-Type', 'application/vnd.api+json');

        if (! $isOptional && ! $isJsonApi) {
            throw new BadRequestHttpException(sprintf(
                'Invalid media-type of request, "application/vnd.api+json" expected, "%s" given.',
                implode(', ', (array) $request->headers->get('Content-Type'))
            ));
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