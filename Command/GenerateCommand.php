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

use Mandango\Mondator\Mondator;
use Mongator\MongatorBundle\ConfigurationManager;
use Mongator\MongatorBundle\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * GenerateCommand.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @author nvb <nvb@aproxima.ru>
 */
class GenerateCommand extends Command
{
    protected static $defaultName = 'mongator:generate';

    /**
     * @var string
     */
    protected $modelDir;

    /**
     * @var array
     */
    protected $extraDirs;

    /**
     * @var ConfigurationManager
     */
    protected $configManager;

    /**
     * @var Mondator
     */
    protected $mondator;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @param Mondator             $mondator
     * @param ConfigurationManager $configManager
     * @param string               $modelDir
     * @param KernelInterface      $kernel
     * @param array                $extraDirs
     * @param string               $name
     */
    public function __construct(Mondator $mondator, ConfigurationManager $configManager, KernelInterface $kernel, $name = null)
    {
        $this->mondator = $mondator;
        $this->configManager = $configManager;
        $this->kernel = $kernel;
        $this->extraDirs = [];

        parent::__construct($name);
    }

    /**
     * @param string $modelDir
     */
    public function configureModelDir($modelDir)
    {
        $this->modelDir = $modelDir;
    }

    /**
     * @param string|array $dirs
     */
    public function addExtraDirs($dirs)
    {
        if (!$dirs) {
            return;
        }
        if (!is_array($dirs)) {
            $dirs = [$dirs];
        }

        $this->extraDirs = array_merge($this->extraDirs, $dirs);
    }

    /**
     * @param string|array $dirs
     */
    protected function setExtraDirs($dirs)
    {
        if (!$dirs) {
            $dirs = [];
        } elseif (!is_array($dirs)) {
            $dirs = [$dirs];
        }

        $this->extraDirs = $dirs;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Generate classes from config classes')
            ->addOption('bundle-models', 'b', InputOption::VALUE_NONE, 'Generate intermediate models inside bundles')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->modelDir) {
            throw new \RuntimeException('Model dir is not configured');
        }
        $output->writeln('processing config classes');

        $intermediate = $input->getOption('bundle-models');

        $outputDir = $this->modelDir;

        $configClasses = [];
        foreach ($this->extraDirs as $dir) {
            if (is_dir($dir)) {
                $finder = new Finder();
                foreach ($finder->files()->name('*.yaml')->name('*.yml')->followLinks()->in($dir) as $file) {
                    foreach ((array) Yaml::parse(file_get_contents($file)) as $class => $configClass) {
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
        $configClassesPending = [];
        foreach ($this->kernel->getBundles() as $bundle) {
            $bundleModelNamespace = 'Model\\'.$bundle->getName();

            if (is_dir($dir = $bundle->getPath().'/Resources/config/mongator')) {
                $finder = new Finder();
                foreach ($finder->files()->name('*.yaml')->name('*.yml')->followLinks()->in($dir) as $file) {
                    foreach ((array) Yaml::parse(file_get_contents($file)) as $class => $configClass) {
                        // class
                        if (0 !== strpos($class, 'Model\\')) {
                            throw new \RuntimeException('The mongator documents must been in the "Model\" namespace.');
                        }
                        if (0 !== strpos($class, $bundleModelNamespace)) {
                            unset($configClass['output'], $configClass['bundle_name'], $configClass['bundle_output']);
                            $configClassesPending[] = ['class' => $class, 'config_class' => $configClass];
                            continue;
                        }

                        // config class
                        $configClass['output']           = $outputDir;
                        $configClass['bundle_output']    = $bundle->getPath();
                        $configClass['bundle_name']      = $bundle->getName();
                        $configClass['bundle_namespace'] = $bundle->getNamespace();
                        $configClass['bundle_models']    = $intermediate;

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

        $configManager = $this->configManager;
        foreach ($configManager->getConfiguration() as $class => $configClass) {
            $configClass['output'] = $outputDir;
            if (!key_exists('bundle_models', $configClass)) {
                $configClass['bundle_models'] = $intermediate;
            }

            if (empty($configClasses[$class])) {
                $configClasses[$class] = $configClass;
            } else {
                $configClasses[$class] = Util::arrayDeepMerge($configClasses[$class], $configClass);
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

        $mondator = $this->mondator;
        $mondator->setConfigClasses($configClasses);
        $mondator->process();
    }
}
