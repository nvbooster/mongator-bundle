<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * MongatorBundle.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorMondatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('mongator.mondator')) {
            return;
        }

        $mondatorDefinition = $container->getDefinition('mongator.mondator');

        // core
        $definition = new Definition('Mongator\Extension\Core');
        $definition->addArgument(array(
            'metadata_factory_class'  => $container->getParameter('mongator.metadata_factory.class'),
            'metadata_factory_output' => $container->getParameter('mongator.metadata_factory.output'),
            'default_behaviors'       => $container->hasParameter('mongator.default_behaviors')
                                       ? $container->getParameter('mongator.default_behaviors')
                                       : array(),
        ));
        $container->setDefinition('mongator.extension.core', $definition);

        $mondatorDefinition->addMethodCall('addExtension', array(new Reference('mongator.extension.core')));

        // bundles
        $definition = new Definition('Mongator\MongatorBundle\Extension\Bundles');
        $container->setDefinition('mongator.extension.bundles', $definition);

        $mondatorDefinition->addMethodCall('addExtension', array(new Reference('mongator.extension.bundles')));

        // validation
        $definition = new Definition('Mongator\MongatorBundle\Extension\DocumentValidation');
        $container->setDefinition('mongator.extension.document_validation', $definition);

        $mondatorDefinition->addMethodCall('addExtension', array(new Reference('mongator.extension.document_validation')));

        // custom
        foreach ($container->findTaggedServiceIds('mongator.mondator.extension') as $id => $attributes) {
            $mondatorDefinition->addMethodCall('addExtension', array(new Reference($id)));
        }
    }
}
