<?php

namespace Mongator\MongatorBundle\Tests\Validator\Constraint;

use Mongator\MongatorBundle\Validator\Constraint\UniqueDocument;

class UniqueDocumentTest extends \PHPUnit_Framework_TestCase
{
    /** @var UniqueDocument */
    private $constraint;

    public function testGetDefaultOption()
    {
        $this->assertEquals(UniqueDocument::FIELDS_OPTION, $this->constraint->getDefaultOption());
    }

    public function testGetRequiredOptions()
    {
        $this->assertEquals(array(UniqueDocument::FIELDS_OPTION), $this->constraint->getRequiredOptions());
    }

    public function testGetTargets()
    {
        $this->assertEquals(
            \Symfony\Component\Validator\Constraint::CLASS_CONSTRAINT,
            $this->constraint->getTargets()
        );
    }

    public function testValidatedBy()
    {
        $this->assertEquals(
            UniqueDocument::SERVICE_VALIDATOR,
            $this->constraint->validatedBy()
        );
    }

    protected function setUp()
    {
        $this->constraint = new UniqueDocument(array('fields' => array('title')));
    }
}
