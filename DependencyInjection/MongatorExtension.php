<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * MongatorBundle.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorExtension extends Extension
{
    /**
     * Responds to the "mongator" configuration parameter.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('mongator.xml');

        $mongatorDefiniton = $container->getDefinition('mongator');

        // model_dir
        $container->setParameter('mongator.model_dir', $config['model_dir']);
        // extra config classes dirs
        $container->setParameter('mongator.extra_config_classes_dirs', $config['extra_config_classes_dirs']);


        // default_connection
        $mongatorDefiniton->addMethodCall('setDefaultConnectionName', array($config['default_connection']));

        // connections
        foreach ($config['connections'] as $name => $connection) {
            $definition = new Definition($connection['class'], array(
                $connection['server'],
                $connection['database'],
                $connection['options'],
            ));

            $connectionDefinitionName = sprintf('mongator.%s_connection', $name);
            $container->setDefinition($connectionDefinitionName, $definition);

            // ->setConnection
            $container->getDefinition('mongator')->addMethodCall('setConnection', array(
                $name,
                new Reference($connectionDefinitionName),
            ));
        }
    }
}
