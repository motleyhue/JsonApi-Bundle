<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Response;

use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\HttpAttributesAwareInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\HttpAttributesContainer;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\IncludedObjectsAwareInterface;
use Mikemirten\Bundle\JsonApiBundle\Response\Behaviour\IncludedObjectsContainer;

/**
 * Abstract Json API view supposed to get handled and converted into a response
 *
 * @package Mikemirten\Bundle\JsonApiBundle\Response
 */
abstract class AbstractJsonApiView implements HttpAttributesAwareInterface, IncludedObjectsAwareInterface
{
    use HttpAttributesContainer;
    use IncludedObjectsContainer;
}