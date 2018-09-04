<?php

namespace Mongator\MongatorBundle\Tests\Command;

use Mongator\MongatorBundle\Command\EnsureIndexesCommand;

class EnsureIndexesCommandTest extends CommandTestCase
{
    public function testEnsureIndexesCommand()
    {
        $this->application->add(new EnsureIndexesCommand());

        $command = $this->application->find('mongator:ensure-indexes');
        $command->setContainer($this->getMockContainer());

        $tester = $this->getCommandTester($command);
        $tester->execute(
            array_merge(array('command' => $command->getName()), [])
        );
    }

    private function getMockContainer()
    {
        $mongator = \Mockery::mock('mongator');
        $mongator
            ->shouldReceive('ensureAllIndexes')
            ->once()
            ->withNoArgs();

        $container = \Mockery::mock('Symfony\Component\DependencyInjection\Container');
        $container
            ->shouldReceive('get')
            ->once()
            ->with('mongator')
            ->andReturn($mongator);

        return $container;
    }
}
