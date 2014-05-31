<?php

namespace Mongator\MongatorBundle\Tests\Command;

use Mongator\MongatorBundle\Command\FixMissingReferencesCommand;

class FixMissingReferencesCommandTest extends CommandTestCase
{
    public function testFixMissingReferencesCommand()
    {
        $this->application->add(new FixMissingReferencesCommand());

        $command = $this->application->find('mongator:fix-missing-references');
        $command->setContainer($this->getMockContainer());

        $tester = $this->getCommandTester($command);
        $tester->execute(
            array_merge(array('command' => $command->getName()), array())
        );
    }

    private function getMockContainer()
    {
        $mongator = \Mockery::mock('mongator');
        $mongator
            ->shouldReceive('fixAllMissingReferences')
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
