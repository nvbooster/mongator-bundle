<?php

namespace Mongator\MongatorBundle\Tests\Form\DataTransformer;


use Mongator\MongatorBundle\Form\DataTransformer\MongatorDocumentToIdTransformer;

class MongatorDocumentToIdTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $choiceList;
    /** @var MongatorDocumentToIdTransformer */
    private $transformer;

    protected function setUp()
    {
        $this->choiceList = \Mockery::mock('Mongator\MongatorBundle\Form\ChoiceList\MongatorDocumentChoiceList');
        $this->transformer = new MongatorDocumentToIdTransformer($this->choiceList);
    }

    public function testTransform()
    {
        $id = '001';
        $document = \Mockery::mock('document');
        $document
            ->shouldReceive('getId')
            ->andReturn($id);

        $this->assertEquals($id, $this->transformer->transform($document));
    }

    public function testTransformNullDocumentReturnNull()
    {
        $this->assertNull($this->transformer->transform(null));
    }

    public function testReverseTransformNullKeyReturnNull()
    {
        $this->assertNull($this->transformer->reverseTransform(null));
    }

    public function testReverseTransformNotFoundDocumentReturnNull()
    {
        $this->choiceList
            ->shouldReceive('getDocuments')
            ->andReturn(array());

        $this->assertNull($this->transformer->reverseTransform('001'));
    }

    public function testReverseTransformReturnDocument()
    {
        $document = new \stdClass();
        $this->choiceList
            ->shouldReceive('getDocuments')
            ->andReturn(array('001' => $document));

        $this->assertSame($document, $this->transformer->reverseTransform('001'));
    }
}
