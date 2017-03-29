<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ViewListenerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('mrtn_json_api.kernel_view.listener');
        $extensions = $container->findTaggedServiceIds('mrtn_json_api.view_listener.object_handler');

        foreach ($extensions as $id => $tags) {
            $definition->addMethodCall('addObjectHandler', [
                new Reference($id)
            ]);
        }
    }
}