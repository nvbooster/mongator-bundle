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

use Mongator\Document\Document;
use Mongator\MongatorBundle\Form\ChoiceList\MongatorDocumentChoiceList;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * MongatorDocumentToIdTransformer.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorDocumentToIdTransformer implements DataTransformerInterface
{
    private $choiceList;

    /**
     * @param MongatorDocumentChoiceList $choiceList
     */
    public function __construct(MongatorDocumentChoiceList $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    /**
     * @param Document $document
     *
     * @return string
     */
    public function transform($document)
    {
        if (null === $document) {
            return null;
        }

        return (string) $document->getId();
    }

    /**
     * @param string $key
     *
     * @return Document
     */
    public function reverseTransform($key)
    {
        if (null === $key) {
            return null;
        }

        $documents = $this->choiceList->getDocuments();

        return array_key_exists($key, $documents) ? $documents[$key] : null;
    }
}
