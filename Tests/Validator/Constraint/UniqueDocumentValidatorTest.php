<?php

namespace Mongator\MongatorBundle\Tests\Validator\Constraint;

use Mongator\MongatorBundle\Tests\TestCase;
use Mongator\MongatorBundle\Validator\Constraint\UniqueDocument;
use Mongator\MongatorBundle\Validator\Constraint\UniqueDocumentValidator;

class UniqueDocumentValidatorTest extends TestCase
{
    private $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->validator = new UniqueDocumentValidator($this->mongator);
    }

    /**
      * @expectedException \InvalidArgumentException
      * @dataProvider IsValidNotMongatorDocumentProvider
      */
    public function testIsValidNotMongatorDocument($document)
    {
        $constraint = new UniqueDocument(array('fields' => array('title')));
        $this->validator->isValid($document, $constraint);
    }

    public function IsValidNotMongatorDocumentProvider()
    {
        return array(
            array('foo'),
            array(1),
            array(1.1),
            array(true)
        );
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @dataProvider isValidFieldsNotValidProvider
     */
    public function testIsValidFieldsNotValid($fields)
    {
        $this->validator->isValid($this->createArticle(), $this->createConstraint($fields));
    }

    public function isValidFieldsNotValidProvider()
    {
        return array(
            array(1),
            array(1.1),
            array(true),
            array(new \ArrayObject())
        );
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testIsValidAtLeastOneField()
    {
        $this->validator->isValid($this->createArticle(), $this->createConstraint(array()));
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @dataProvider isValidCaseInsensitiveNotValidProvider
     */
    public function testIsValidCaseInsensitiveNotValid($caseInsensitive)
    {
        $constraint = $this->createConstraint('title');
        $constraint->caseInsensitive = $caseInsensitive;
        $this->validator->isValid($this->createArticle(), $constraint);
    }

    public function isValidCaseInsensitiveNotValidProvider()
    {
        return array(
            array('foo'),
            array(1),
            array(1.1),
            array(true),
            array(new \ArrayObject())
        );
    }

    public function testIsValidWithoutResults()
    {
        $article = $this->createArticle()->setTitle('foo');
        $this->assertTrue($this->validator->isValid($article, $this->createConstraint('title')));
    }

    public function testIsValidSameResult()
    {
        $article = $this->createArticle()->setTitle('foo')->save();
        $this->assertTrue($this->validator->isValid($article, $this->createConstraint('title')));
    }

    public function testIsValidOneField()
    {
        $article1 = $this->createArticle()->setTitle('foo')->save();
        $article2 = $this->createArticle()->setTitle('foo');
        $this->assertFalse($this->validator->isValid($article2, $this->createConstraint('title')));
    }

    public function testIsValidCaseInsensitive()
    {
        $article1 = $this->createArticle()->setTitle('foo')->save();
        $article2 = $this->createArticle()->setTitle('foO');

        $constraint = $this->createConstraint('title');
        $constraint->caseInsensitive = array('title');

        $this->assertFalse($this->validator->isValid($article2, $constraint));
    }

    private function createConstraint($fields)
    {
        return new UniqueDocument(array('fields' => $fields));
    }

    private function createArticle()
    {
        return $this->mongator->create('Model\Article');
    }
}