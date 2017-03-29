<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ObjectMapperCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('mrtn_json_api.object_mapper');
        $extensions = $container->findTaggedServiceIds('mrtn_json_api.object_mapper.handler');

        foreach ($extensions as $id => $tags) {
            $definition->addMethodCall('addHandler', [
                new Reference($id)
            ]);
        }
    }
}