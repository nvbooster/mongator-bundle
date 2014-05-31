<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * UniqueConstraint.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class UniqueDocument extends Constraint
{
    const FIELDS_OPTION = 'fields';
    const SERVICE_VALIDATOR = 'mongator.validator.unique_document';

    public $message = 'This value is already used.';
    public $fields = array();
    public $caseInsensitive = array();

    /**
     * {@inheritDoc}
     */
    public function getDefaultOption()
    {
        return self::FIELDS_OPTION;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions()
    {
        return array(self::FIELDS_OPTION);
    }

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return self::SERVICE_VALIDATOR;
    }

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
