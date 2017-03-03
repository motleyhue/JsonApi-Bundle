<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle;

use Mikemirten\Bundles\JsonApiBundle\DependencyInjection\JsonApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JsonApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new JsonApiExtension();
    }
}