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

use Model\Mapping\MetadataFactory;
use Mongator\Mongator;
use Mongator\Extension\Core;
use Mongator\MongatorBundle\Command\GenerateCommand;
use Mongator\MongatorBundle\Extension\CustomType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * MongatorBundle.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @author nvb <nvb@aproxima.ru>
 */
class MongatorExtension extends Extension implements PrependExtensionInterface
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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->getDefinition(GenerateCommand::class)
            ->addMethodCall('configureModelDir', [$config['model_dir']])
            ->addMethodCall('addExtraDirs', ['%kernel.root_dir%/Resources/config/mongator'])
            ->addMethodCall('addExtraDirs', [$config['model_dir']])
        ;

        $mongatorDefiniton = $container->getDefinition(Mongator::class);
        // default_connection
        $mongatorDefiniton->addMethodCall('setDefaultConnectionName', [$config['default_connection']]);

        // connections
        foreach ($config['connections'] as $name => $connection) {
            $definition = new Definition($connection['class'], [
                $connection['server'],
                $connection['database'],
                $connection['options'],
            ]);
            $definition->setPublic(false);

            $connectionDefinitionName = sprintf('mongator.%s_connection', $name);
            $container->setDefinition($connectionDefinitionName, $definition);

            // ->setConnection
            $mongatorDefiniton->addMethodCall('setConnection', [
                $name,
                new Reference($connectionDefinitionName),
            ]);
        }

        $container
            ->register(Core::class)
            ->setPublic(false)
            ->addArgument([
                'metadata_factory_class'  => MetadataFactory::class,
                'metadata_factory_output' => $config['model_dir'],
            ])
            ->addTag('mongator.mondator.extension', ['priority' => 255])
        ;

        $customTypeExtensionDefinition = $container->getDefinition(CustomType::class);
        foreach ($config['mapping'] as $key => $type) {
            $customTypeExtensionDefinition
                ->addMethodCall('addCustomType', [$key, $type['class']]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['JMSSerializerBundle'])) {
            $container->prependExtensionConfig('jms_serializer', [
                'metadata' => [
                    'directories' => [
                        'mongator' => [
                            'namespace_prefix' => 'Mongator\\Document',
                            'path' => '@MongatorBundle/Resources/config/jms_serializer',
                        ],
                    ],
                ],
            ]);
        }
    }
}
