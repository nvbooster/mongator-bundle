<?php

namespace Mongator\MongatorBundle\Tests;

use Mongator\MongatorBundle\MongatorBundle;

class MongatorBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = \Mockery::mock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $bundle = new MongatorBundle();

        $container->shouldReceive('addCompilerPass')
            ->with(\Mockery::type('Mongator\MongatorBundle\DependencyInjection\Compiler\MongatorMondatorPass'));

        $this->assertNull($bundle->build($container));
    }
}
