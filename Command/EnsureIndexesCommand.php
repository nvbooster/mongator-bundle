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

use Mongator\Mongator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * EnsureIndexesCommand.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @author nvb <nvb@aproxima.ru>
 */
class EnsureIndexesCommand extends Command
{
    protected static $defaultName = 'mongator:ensure-indexes';

    /**
     * @var Mongator
     */
    protected $mongator;

    /**
     * @param Mongator $mongator
     * @param string   $name
     */
    public function __construct(Mongator $mongator, $name = null)
    {
        $this->mongator = $mongator;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Ensure the indexes.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('ensuring the indexes');

        $this->mongator->ensureAllIndexes();
    }
}
