<?php

namespace Mongator\MongatorBundle\Tests\Form\EventListener;

use Mongator\MongatorBundle\Form\EventListener\MergeGroupListener;
use Symfony\Component\Form\FormEvents;

class MergeGroupListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array(FormEvents::SUBMIT => 'onBindNormData'),
            MergeGroupListener::getSubscribedEvents()
        );
    }

    public function testOnBindNormData()
    {
        $event = \Mockery::mock('Symfony\Component\Form\FormEvent');

        $form = \Mockery::mock('Symfony\Component\Form\FormInterface');
        $event->shouldReceive('getForm')->andReturn($form);

        $data = array(1, 2, 3);
        $event->shouldReceive('getData')->andReturn($data);

        $group = \Mockery::mock('data');
        $group->shouldReceive('replace')->with($data);

        $form->shouldReceive('getData')->andReturn($group);
        $event->shouldReceive('setData')->with($group);

        $listener = new MergeGroupListener();
        $this->assertNull($listener->onBindNormData($event));
    }
}
