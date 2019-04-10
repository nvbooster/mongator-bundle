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
use Mongator\Cache\APCCache;
use Mongator\Cache\ArrayCache;
use Mongator\Cache\FilesystemCache;
use Mongator\Extension\Core;
use Mongator\MongatorBundle\Command\GenerateCommand;
use Mongator\MongatorBundle\Extension\CustomType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Form\Form;
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

        if (! empty($config['fields_cache_driver'])) {
            $cacheService = $this->getCacheService($container, $config['fields_cache_driver']);
            $mongatorDefiniton->addMethodCall('setFieldsCache', [new Reference($cacheService)]);
        }
        if (! empty($config['data_cache_driver'])) {
            $cacheService = $this->getCacheService($container, $config['data_cache_driver']);
            $mongatorDefiniton->addMethodCall('setDataCache', [new Reference($cacheService)]);
        }

        if (class_exists(Form::class)) {
            $defaultDefinition = new Definition();

            $defaultDefinition
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(false)
            ;

            $loader->registerClasses($definition, 'Mongator\\MongatorBundle\\Form\\', '../../Form/*');
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

    /**
     * @param ContainerBuilder $container
     * @param array            $cacheConfig
     *
     * @return string
     */
    private function getCacheService(ContainerBuilder $container, $cacheConfig)
    {
        $serviceId = sprintf('mongator.cache.%s', substr(md5(time().mt_rand()), 24));

        switch ($cacheConfig['type']) {
            case 'service':
                if (empty($cacheConfig['id'])) {
                    throw new InvalidConfigurationException('Service id must be defined');
                }
                $container->setAlias($serviceId, new Alias($cacheConfig['id'], false));

                break;
            case 'class':
                if (empty($cacheConfig['class'])) {
                    throw new InvalidConfigurationException('Class name must be defined');
                }
                $container->setDefinition($serviceId, $this->getCacheServiceDefinition($cacheConfig['class']));

                break;
            case 'array':
                $container->setDefinition($serviceId, $this->getCacheServiceDefinition(ArrayCache::class));

                break;
            case 'apc':
                $container->setDefinition($serviceId, $this->getCacheServiceDefinition(APCCache::class));

                break;
            case 'filesystem':
                $container->setDefinition($serviceId, $definition = $this->getCacheServiceDefinition(FilesystemCache::class));
                $definition->addArgument('%kernel.cache_dir%/mongator/cache');

                break;
            case 'memcached':
                $container->setDefinition($serviceId, $definition = $this->getCacheServiceDefinition(FilesystemCache::class));

                $memcached = new Definition(\Memcached::class);
                $memcached->setPublic(false);
                $memcached->addMethodCall('addServer', [
                    (! empty($cacheConfig['host'])) ? $cacheConfig['host'] : 'localhost',
                    (! empty($cacheConfig['port'])) ? $cacheConfig['host'] : 11211,
                ]);

                $container->setDefinition($memcachedServiceId = $serviceId.'.provider', $memcached);

                $definition->addArgument(new Reference($memcachedServiceId));

                break;
            case 'redis':
                $container->setDefinition($serviceId, $definition = $this->getCacheServiceDefinition(FilesystemCache::class));

                $memcached = new Definition(\Redis::class);
                $memcached->setPublic(false);
                $memcached->addMethodCall('connect', [
                    (! empty($cacheConfig['host'])) ? $cacheConfig['host'] : 'localhost',
                    (! empty($cacheConfig['port'])) ? $cacheConfig['host'] : 6379,
                ]);
                if (! empty($cacheConfig['database'])) {
                    $memcached->addMethodCall('select', $cacheConfig['database']);
                }
                if (! empty($cacheConfig['password'])) {
                    $memcached->addMethodCall('auth', $cacheConfig['password']);
                }

                $container->setDefinition($memcachedServiceId = $serviceId.'.provider', $memcached);

                $definition->addArgument(new Reference($memcachedServiceId));

                break;
        }

        return $serviceId;
    }

    /**
     * @param string $class
     *
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    private function getCacheServiceDefinition($class)
    {
        $definition = new Definition($class);
        $definition->setPublic(false);
        $definition->setAutowired(true);
        $definition->setAutoconfigured(true);

        return $definition;
    }
}
