<?php

namespace Mongator\MongatorBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    protected $application;
    protected $container;
    protected $commandTester;

    protected function setUp()
    {
        $this->application = new Application();
    }

    protected function getCommandTester($command)
    {
        return new CommandTester($command);
    }
}
