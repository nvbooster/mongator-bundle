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
use Mongator\MongatorBundle\Form\ChoiceList\MongatorDocumentChoiceList;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\DataTransformer\TransformationFailedException;

/**
 * MongatorDocumentToArrayTransformer.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorDocumentsToArrayTransformer implements DataTransformerInterface
{
    private $choiceList;

    public function __construct(MongatorDocumentChoiceList $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    public function transform($group)
    {
        if (null === $group) {
            return array();
        }

        if (!$group instanceof ReferenceGroup) {
            throw new UnexpectedTypeException($group, 'Mongator\Group\ReferenceGroup');
        }

        $array = array();
        foreach ($group as $document) {
            $array[] = (string) $document->getId();
        }

        return $array;
    }

    public function reverseTransform($keys)
    {
        $documents = $this->choiceList->getDocuments();

        $array = array();
        foreach ($keys as $key) {
            if (!isset($documents[(string) $key])) {
                throw new TransformationFailedException('Some Mongator document does not exist.');
            }
            $array[] = $documents[(string) $key];
        }

        return $array;
    }
}
