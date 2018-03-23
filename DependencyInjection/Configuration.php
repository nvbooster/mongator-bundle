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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * MongatorExtension configuration structure.
 *
 * Based on the DoctrineMongoDBBundle's configuration.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mongator', 'array');

        $rootNode
            ->children()
                ->scalarNode('model_dir')->defaultValue('%kernel.root_dir%/../src')->cannotBeEmpty()->end()
            ->end()
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return is_array($v) && !array_key_exists('connections', $v) && !array_key_exists('connection', $v);
                })
                ->then(function ($v) {
                    // Key that should not be rewritten to the connection config
                    $excludedKeys = array('default_connection', 'extra_config_classes_dirs', 'mapping');
                    $connection = array();
                    foreach ($v as $key => $value) {
                        if (in_array($key, $excludedKeys)) {
                            continue;
                        }
                        $connection[$key] = $value;
                        unset($v[$key]);
                    }
                    $v['default_connection'] = isset($v['default_connection']) ? (string) $v['default_connection'] : 'default';
                    $v['connections'] = array($v['default_connection'] => $connection);

                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('default_connection')->cannotBeEmpty()->end()
            ->end()
            ->fixXmlConfig('connection')
            ->append($this->getConnectionsNode())
            ->fixXmlConfig('extra_config_classes_dir')
            ->children()
                ->arrayNode('extra_config_classes_dirs')
                    ->prototype('scalar')->cannotBeEmpty()->end()
                ->end()
            ->end()
            ->append($this->getTypesNode())
        ;

        return $treeBuilder;
    }

    protected function getConnectionsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('connections');

        $connectionNode = $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
        ;

        $connectionNode
            ->children()
                ->scalarNode('class')->defaultValue('Mongator\Connection')->cannotBeEmpty()->end()
                ->scalarNode('server')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('database')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->append($this->getConnectionOptionsNode())
        ;

        return $node;
    }

    protected function getTypesNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('mapping');

        $typeNode = $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
        ;

        $typeNode
            ->beforeNormalization()
                ->ifString()
                ->then(function ($v) {
                    return array('class' => $v);
                })
            ->end()
            ->children()
                ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Adds the NodeBuilder for the "options" key of a connection.
     */
    protected function getConnectionOptionsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('options');

        $node
            ->performNoDeepMerging()
            ->addDefaultsIfNotSet() // adds an empty array of omitted
            // options go into the Mongo constructor
            // http://www.php.net/manual/en/mongo.construct.php
            ->children()
                ->booleanNode('connect')->end()
                ->scalarNode('persist')->end()
                ->scalarNode('timeout')->end()
                ->booleanNode('replicaSet')->end()
                ->scalarNode('username')->end()
                ->scalarNode('password')->end()
            ->end()
        ->end();

        return $node;
    }
}
