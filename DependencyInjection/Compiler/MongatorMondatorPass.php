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

use Mandango\Mondator\Mondator;
use Mongator\MongatorBundle\ConfigurationManager;
use Mongator\MongatorBundle\Extension\CustomType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;

/**
 * MongatorBundle.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @author nvb <nvb@aproxima.ru>
 */
class MongatorMondatorPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition(Mondator::class)) {
            return;
        }

        $customTypeExtensionDefinition = $container->getDefinition(CustomType::class);
        foreach ($container->findTaggedServiceIds('mongator.type') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $customTypeExtensionDefinition
                    ->addMethodCall('addCustomType', [$attributes['alias'], $container->getDefinition($id)->getClass()]);
            }
        }

        $mondatorDefinition = $container->getDefinition(Mondator::class);
        foreach ($this->findAndSortTaggedServices('mongator.mondator.extension', $container) as $reference) {
            $mondatorDefinition->addMethodCall('addExtension', [$reference]);
        }

        // configuration providers
        $configurationManagerDefinition = $container->getDefinition(ConfigurationManager::class);
        foreach ($container->findTaggedServiceIds('mongator.mondator.configprovider') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $configurationManagerDefinition
                    ->addMethodCall('addProvider', [$container->getDefinition($id), empty($attributes['priority']) ? 10 : $attributes['priority']]);
            }
        }
    }
}
