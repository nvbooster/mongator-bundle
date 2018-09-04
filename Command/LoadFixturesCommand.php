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

use Mongator\DataLoader;
use Mongator\Mongator;
use Mongator\MongatorBundle\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * LoadFixturesCommand.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @author nvb <nvb@aproxima.ru>
 */
class LoadFixturesCommand extends Command
{
    protected static $defaultName = 'mongator:load-fixtures';

    /**
     * @var Mongator
     */
    protected $mongator;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @param Mongator        $mongator
     * @param KernelInterface $kernel
     * @param string          $name
     */
    public function __construct(Mongator $mongator, KernelInterface $kernel, $name = null)
    {
        $this->mongator = $mongator;
        $this->kernel = $kernel;
        parent::__construct($name);
    }
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Load fixtures.')
            ->addOption('fixtures', null, InputOption::VALUE_OPTIONAL, 'The directory or file to load data fixtures from')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Whether or not to append the data fixtures')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('processing fixtures');

        $dirOrFile = $input->getOption('fixtures');
        if ($dirOrFile) {
            $dirOrFile = [$dirOrFile];
        } else {
            $dirOrFile = [];
            // application
            if (is_dir($dir = $this->kernel->getRootDir().'/DataFixtures/Mongator')) {
                $dirOrFile[] = $dir;
            }
            // bundles
            foreach ($this->kernel->getBundles() as $bundle) {
                if (is_dir($dir = $bundle->getPath().'/DataFixtures/Mongator')) {
                    $dirOrFile[] = $dir;
                }
            }
        }

        $files = [];
        foreach ($dirOrFile as $dir) {
            if (is_file($dir)) {
                $files[] = $dir;
                continue;
            }
            if (is_dir($dir)) {
                $finder = new Finder();
                foreach ($finder->files()->name('*.yaml')->name('*.yml')->followLinks()->in($dir) as $file) {
                    $files[] = $file;
                }
                continue;
            }

            throw new \InvalidArgumentException(sprintf('"%s" is not a dir or file.', $dir));
        }

        $data = [];
        foreach ($files as $file) {
            $data = Util::arrayDeepMerge($data, (array) Yaml::parse(file_get_contents($file)));
        }

        if (!$data) {
            $output->writeln('there are no fixtures');

            return;
        }

        $output->writeln('loading fixtures');

        $dataLoader = new DataLoader($this->mongator);
        $dataLoader->load($data, !$input->getOption('append'));
    }
}
