<?php

namespace Mongator\MongatorBundle\Validator\Constraint;

use Mongator\Mongator;
use Mongator\Document\Document;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * UniqueConstraint.
 *
 * fixes compatibility errors
 *
 * @author nvb <nvb@aproxima.ru>
 */
class UniqueDocumentValidator extends ConstraintValidator
{
    /**
     * @var Mongator
     */
    protected $mongator;

    /**
     * @param Mongator $mongator A mongator.
     */
    public function __construct(Mongator $mongator)
    {
        $this->mongator = $mongator;
    }

    /**
     * Validates the document uniqueness.
     *
     * @param Document   $value      The document.
     * @param Constraint $constraint The constraint.
     *
     * @return bool Whether or not the document is unique.
     */
    public function validate($value, Constraint $constraint)
    {
        $document = $this->parseDocument($value);
        $fields = $this->parseFields($constraint->fields);
        $caseInsensitive = $this->parseCaseInsensitive($constraint->caseInsensitive);

        $query = $this->createQuery($document, $fields, $caseInsensitive);
        $numberResults = $query->count();

        if (0 === $numberResults) {
            return true;
        }

        if (1 === $numberResults) {
            $result = $query->one();
            if ($result === $document) {
                return true;
            }
        }

        if ($this->context) {
            $this->context->buildViolation($constraint->message)
                ->atPath($fields[0])
                ->addViolation();
        }

        return false;
    }

    private function parseDocument($document)
    {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException('The value must be a mongator document.');
        }

        return $document;
    }

    private function parseFields($fields)
    {
        if (is_string($fields)) {
            $fields = [$fields];
        } elseif (is_array($fields)) {
            if (0 === count($fields)) {
                throw new ConstraintDefinitionException('At least one field has to be specified.');
            }
        } else {
            throw new UnexpectedTypeException($fields, 'array');
        }

        return $fields;
    }

    private function parseCaseInsensitive($caseInsensitive)
    {
        if (!is_array($caseInsensitive)) {
            throw new UnexpectedTypeException($caseInsensitive, 'array');
        }

        return $caseInsensitive;
    }

    private function createQuery(Document $document, array $fields, array $caseInsensitive)
    {
        $repository = $this->mongator->getRepository(get_class($document));
        $criteria = $this->createCriteria($document, $fields, $caseInsensitive);

        return $repository->createQuery($criteria);
    }

    private function createCriteria(Document $document, array $fields, array $caseInsensitive)
    {
        $criteria = [];
        foreach ($fields as $field) {
            $value = $document->get($field);
            if (in_array($field, $caseInsensitive)) {
                $value = new Regex(sprintf('/^%s$/i', preg_quote($value)));
            }
            $criteria[$field] = $value;
        }

        return $criteria;
    }
}
