<?php

namespace Mongator\MongatorBundle\Command;

use Mandango\Mondator\Mondator;
use Mongator\Type\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
class ShowTypesCommand extends Command
{
    protected static $defaultName = 'mongator:show-types';

    /**
     * Mondator required to ensure that all types are loaded
     *
     * @param Mondator $mondator
     * @param string   $name
     */
    public function __construct(Mondator $mondator, $name = null)
    {
        parent::__construct($name ?: self::$defaultName);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setDescription('Show registered mongator types')
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reflectionClass = new \ReflectionClass(Container::class);
        $map = $reflectionClass->getStaticProperties()['map'];

        if (!$map) {
            $output->writeln('<error>No types registered.</error>');

            return 1;
        }
        $tableBody = [];

        foreach ($map as $alias => $class) {

            $tableBody[] = [$alias, $class];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['alias', 'class name'])
            ->setRows($tableBody);

        $table->render($output);
    }
}
