<?php

namespace Mongator\MongatorBundle\Tests\Form\DataTransformer;

use Mongator\MongatorBundle\Form\DataTransformer\MongatorDocumentsToArrayTransformer;

class MongatorDocumentsToArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $choiceList;

    /** @var MongatorDocumentsToArrayTransformer */
    private $transformer;

    protected function setUp()
    {
        $this->choiceList = \Mockery::mock('Mongator\MongatorBundle\Form\ChoiceList\MongatorDocumentChoiceList');
        $this->transformer = new MongatorDocumentsToArrayTransformer($this->choiceList);
    }

    public function testTransformIfGroupNullReturnEmptyArray()
    {
        $this->assertEquals(array(), $this->transformer->transform(null));
    }

    public function testTransformShouldThrowExceptionIfNotReferenceGroup()
    {
        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');
        $this->transformer->transform('notObject');
    }

    public function testTransform()
    {
        $documents = array(
            \Mockery::mock('document')->shouldReceive('getId')->andReturn('001')->getMock(),
            \Mockery::mock('document')->shouldReceive('getId')->andReturn('002')->getMock()
        );

        $group = \Mockery::mock('Mongator\Group\ReferenceGroup');
        $group
            ->shouldReceive('getIterator')
            ->andReturn(new \ArrayIterator($documents));

        $this->assertEquals(array('001', '002'), $this->transformer->transform($group));
    }

    public function testReverseTransformShouldThrowExceptionIfDocumentNotFound()
    {
        $this->choiceList
            ->shouldReceive('getDocuments')
            ->andReturn(array());

        $this->setExpectedException('Symfony\Component\Form\Exception\TransformationFailedException');
        $this->transformer->reverseTransform(array('001'));
    }

    public function testReverseTransform()
    {
        $document = new \stdClass();
        $this->choiceList
            ->shouldReceive('getDocuments')
            ->andReturn(array('001' => $document));

        $this->assertSame(array($document), $this->transformer->reverseTransform(array('001')));
    }
}
 