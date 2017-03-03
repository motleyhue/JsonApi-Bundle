<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DocumentHydratorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('mrtn_json_api.document_hydrator');
        $extensions = $container->findTaggedServiceIds('mrtn_json_api.document_hydrator.extension');

        foreach ($extensions as $id => $tags) {
            $definition->addMethodCall('registerExtension', [
                new Reference($id)
            ]);
        }
    }
}