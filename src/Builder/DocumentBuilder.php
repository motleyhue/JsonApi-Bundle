<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Builder;

use Mikemirten\Component\JsonApi\Mapper\Handler\LinkRepository\RepositoryProvider;
use Mikemirten\Component\JsonApi\Mapper\ObjectMapper;
use Mikemirten\Bundle\JsonApiBundle\Response\{
    Behaviour\IncludedObjectsAwareInterface,
    AbstractJsonApiView,
    JsonApiIteratorView,
    JsonApiObjectView
};
use Mikemirten\Component\JsonApi\Document\{
    AbstractDocument,
    SingleResourceDocument,
    ResourceCollectionDocument,
    ResourceObject,
    JsonApiObject,
    LinkObject
};

/**
 * JSON API Document builder
 *
 * @package DocumentBuilder
 */
class DocumentBuilder
{
    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * Link-repositories provider
     *
     * @var RepositoryProvider
     */
    protected $repositoryProvider;

    /**
     * Builder constructor.
     *
     * @param ObjectMapper       $mapper
     * @param RepositoryProvider $repositoryProvider
     */
    public function __construct(ObjectMapper $mapper, RepositoryProvider $repositoryProvider)
    {
        $this->mapper             = $mapper;
        $this->repositoryProvider = $repositoryProvider;
    }

    /**
     * Build a document by a view
     *
     * @param  AbstractJsonApiView $view
     * @return AbstractDocument
     */
    public function build(AbstractJsonApiView $view): AbstractDocument
    {
        $document = $this->createDocument($view);

        $this->handleIncludedResources($document, $view);
        $this->handleDocumentLinks($document, $view);

        if ($view->hasDocumentCallback()) {
            ($view->getDocumentCallback())($document);
        }

        return $document;
    }

    /**
     * Create a document by a view
     *
     * @param  AbstractJsonApiView $view
     * @return AbstractDocument
     */
    protected function createDocument(AbstractJsonApiView $view): AbstractDocument
    {
        if ($view instanceof JsonApiObjectView) {
            return $this->createSingleResourceDocument($view);
        }

        if ($view instanceof JsonApiIteratorView) {
            return $this->createCollectionDocument($view);
        }

        throw new \LogicException('Unknown extension of AbstractJsonApiView cannot be processed: ' . get_class($view));
    }

    /**
     * Handle single object Json API view
     *
     * @param  JsonApiObjectView $view
     * @return SingleResourceDocument
     */
    protected function createSingleResourceDocument(JsonApiObjectView $view): SingleResourceDocument
    {
        $resource = $this->handleObject($view->getObject());

        if ($view->hasResourceCallback()) {
            ($view->getResourceCallback())($resource);
        }

        $document = new SingleResourceDocument($resource);
        $document->setJsonApi(new JsonApiObject());

        return $document;
    }

    /**
     * Handle object-iterator
     *
     * @param  JsonApiIteratorView $view
     * @return ResourceCollectionDocument
     */
    protected function createCollectionDocument(JsonApiIteratorView $view): ResourceCollectionDocument
    {
        $document = new ResourceCollectionDocument();
        $document->setJsonApi(new JsonApiObject());

        foreach ($view as $object)
        {
            $resource = $this->handleObject($object);

            if ($view->hasResourceCallback()) {
                ($view->getResourceCallback())($resource);
            }

            $document->addResource($resource);
        }

        return $document;
    }

    /**
     * Handle object
     *
     * @param  $object
     * @return ResourceObject
     */
    protected function handleObject($object): ResourceObject
    {
        if ($object instanceof ResourceObject) {
            return $object;
        }

        return $this->mapper->toResource($object);
    }

    /**
     * Handle supposed to be included to document resources
     *
     * @param AbstractDocument              $document
     * @param IncludedObjectsAwareInterface $view
     */
    protected function handleIncludedResources(AbstractDocument $document, IncludedObjectsAwareInterface $view)
    {
        foreach ($view->getIncludedObjects() as $object)
        {
            $resource = $this->handleObject($object);
            $document->addIncludedResource($resource);
        }
    }

    /**
     * Handle links of document
     *
     * @param AbstractJsonApiView $view
     * @param AbstractDocument    $document
     */
    protected function handleDocumentLinks(AbstractDocument $document, AbstractJsonApiView $view)
    {
        foreach ($view->getDocumentLinks() as $definition)
        {
            $repositoryName = $definition->getRepositoryName();
            $linkName       = $definition->getLinkName();
            $parameters     = $definition->getParameters();

            $linkData = $this->repositoryProvider
                ->getRepository($repositoryName)
                ->getLink($linkName, $parameters);

            $metadata = array_replace(
                $linkData->getMetadata(),
                $definition->getMetadata()
            );

            $link = new LinkObject($linkData->getReference(), $metadata);

            $document->setLink($definition->getName(), $link);
        }
    }
}