<?php

namespace Mongator\MongatorBundle\Tests\Form;


use Mongator\MongatorBundle\Form\MongatorExtension;

class MongatorExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $mongator;
    private $extension;

    protected function setUp()
    {
        $this->mongator = \Mockery::mock('Mongator\Mongator');
        $this->mongator->shouldReceive('getMetadataFactory')
            ->andReturn(\Mockery::mock('\Mongator\MetadataFactory'));
        $this->extension = new MongatorExtension($this->mongator);
    }

    public function testGetTypeExtensions()
    {
        $this->assertInstanceOf(
            'Mongator\MongatorBundle\Form\Type\MongatorDocumentType',
            $this->extension->getType('mongator_document')
        );
    }

    public function testGetTypeGuesser()
    {
        $this->assertInstanceOf(
            'Mongator\MongatorBundle\Form\MongatorTypeGuesser',
            $this->extension->getTypeGuesser()
        );
    }
}
