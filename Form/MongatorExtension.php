<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\Form;

use Symfony\Component\Form\AbstractExtension;

/**
 * MongatorExtension.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorExtension extends AbstractExtension
{
    protected function loadTypes()
    {
        return array(
            new Type\MongatorDocumentType(),
        );
    }

    protected function loadTypeGuesser()
    {
        return new MongatorTypeGuesser();
    }
}
