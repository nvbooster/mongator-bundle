<?php

namespace Mongator\MongatorBundle\DependencyInjection\Compiler;

use Mongator\Mongator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
class MongatorEventDispatcherPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ((false === $container->hasDefinition(Mongator::class))
            || (false === $container->hasDefinition('event_dispatcher'))
        ) {
            return;
        }

        $container->getDefinition(Mongator::class)
            ->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')])
        ;
    }
}
