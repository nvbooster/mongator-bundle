<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\Command;

use Mongator\MongatorBundle\Util;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * GenerateCommand.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class GenerateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mongator:generate')
            ->setDescription('Generate classes from config classes')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('processing config classes');

        $container = $this->getContainer();
        $outputDir = $container->getParameter('mongator.model_dir');

        $configClasses = array();
        // application + extra
        foreach (array_merge(
            array($container->getParameter('kernel.root_dir').'/config/mongator'),
            $container->getParameter('mongator.extra_config_classes_dirs')
        ) as $dir) {
            if (is_dir($dir)) {
                $finder = new Finder();
                foreach ($finder->files()->name('*.yml')->followLinks()->in($dir) as $file) {
                    foreach ((array) Yaml::parse($file) as $class => $configClass) {
                        // class
                        if (0 !== strpos($class, 'Model\\')) {
                            throw new \RuntimeException('The Mongator documents must been in the "Model\" namespace.');
                        }

                        // config class
                        $configClass['output']           = $outputDir;
                        $configClass['bundle_output']    = null;
                        $configClass['bundle_name']      = null;
                        $configClass['bundle_namespace'] = null;

                        $configClasses[$class] = $configClass;
                    }
                }
            }
        }

        // bundles
        $configClassesPending = array();
        foreach ($container->get('kernel')->getBundles() as $bundle) {
            $bundleModelNamespace = 'Model\\'.$bundle->getName();

            if (is_dir($dir = $bundle->getPath().'/Resources/config/mongator')) {
                $finder = new Finder();
                foreach ($finder->files()->name('*.yml')->followLinks()->in($dir) as $file) {
                    foreach ((array) Yaml::parse(file_get_contents($file)) as $class => $configClass) {
                        // class
                        if (0 !== strpos($class, 'Model\\')) {
                            throw new \RuntimeException('The mongator documents must been in the "Model\" namespace.');
                        }
                        if (0 !== strpos($class, $bundleModelNamespace)) {
                            unset($configClass['output'], $configClass['bundle_name'], $configClass['bundle_output']);
                            $configClassesPending[] = array('class' => $class, 'config_class' => $configClass);
                            continue;
                        }

                        // config class
                        $configClass['output']           = $outputDir;
                        $configClass['bundle_output']    = $outputDir;
                        $configClass['bundle_name']      = $bundle->getName();
                        $configClass['bundle_namespace'] = $bundle->getNamespace();

                        if (isset($configClasses[$class])) {
                            $previousConfigClass = $configClasses[$class];
                            unset($configClasses[$class]);
                            $configClasses[$class] = Util::arrayDeepMerge($previousConfigClass, $configClass);
                        } else {
                            $configClasses[$class] = $configClass;
                        }
                    }
                }
            }
        }

        // merge bundles
        foreach ($configClassesPending as $pending) {
            if (!isset($configClasses[$pending['class']])) {
                throw new \RuntimeException(sprintf('The class "%s" does not exist.', $pending['class']));
            }

            $configClasses[$pending['class']] = Util::arrayDeepMerge($pending['config_class'], $configClasses[$pending['class']]);
        }

        $output->writeln('generating classes');

        $mondator = $container->get('mongator.mondator');
        $mondator->setConfigClasses($configClasses);
        $mondator->process();
    }
}
