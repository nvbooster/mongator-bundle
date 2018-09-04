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
 * @author nvb <nvb@aproxima.ru>
 */
class UniqueDocument extends Constraint
{
    const FIELDS_OPTION = 'fields';

    public $message = 'This value is already used.';
    public $fields = [];
    public $caseInsensitive = [];

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
        return [self::FIELDS_OPTION];
    }

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
