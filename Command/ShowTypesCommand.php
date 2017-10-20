<?php

namespace Mongator\MongatorBundle\Command;

use Mongator\Type\Container;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
class ShowTypesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mongator:show-types')
            ->setDescription('Show registered mongator types')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // loading mongator for DI to set up types
        $this->getContainer()->get('mongator.mondator');

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
