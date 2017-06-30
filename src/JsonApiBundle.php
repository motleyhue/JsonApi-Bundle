<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle;

use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler\DocumentHydratorCompilerPass;
use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler\ObjectMapperCompilerPass;
use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler\ViewListenerCompilerPass;
use Mikemirten\Bundle\JsonApiBundle\DependencyInjection\JsonApiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DocumentHydratorCompilerPass());
        $container->addCompilerPass(new ObjectMapperCompilerPass());
    }
}