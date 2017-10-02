<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\Form\DataTransformer;

use Mongator\Group\ReferenceGroup;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * MongatorDocumentToArrayTransformer.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorDocumentsToArrayTransformer implements DataTransformerInterface
{

    /**
     * @param ReferenceGroup $group
     *
     * @throws UnexpectedTypeException
     *
     * @return array
     */
    public function transform($group)
    {
        if (null === $group) {
            return array();
        }

        if (!$group instanceof ReferenceGroup) {
            throw new UnexpectedTypeException($group, 'Mongator\Group\ReferenceGroup');
        }

        return iterator_to_array($group, false);
    }

    /**
     * @param array $array
     *
     * @throws TransformationFailedException
     *
     * @return array
     */
    public function reverseTransform($array)
    {
        return $array;
    }
}
