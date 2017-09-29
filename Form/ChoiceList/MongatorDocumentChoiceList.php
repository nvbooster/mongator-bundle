<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\Form\ChoiceList;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Mongator\Query;
use Mongator\Mongator;

/**
 * MongatorDocumentChoiceList.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorDocumentChoiceList extends ChoiceList
{
    private $mongator;
    private $class;
    private $field;
    private $query;

    private $documents;

    /**
     * @param Mongator $mongator
     * @param string   $class
     * @param string   $field
     * @param Query    $query
     * @param array    $choices
     */
    public function __construct(Mongator $mongator, $class, $field = null, Query $query = null, array $choices = array())
    {
        $this->mongator = $mongator;
        $this->class = $class;
        $this->field = $field;
        $this->query = $query;

        parent::__construct($choices);
    }

    /**
     * @return array
     */
    public function getDocuments()
    {
        if (null === $this->documents) {
            $this->load();
        }

        return $this->documents;
    }

    protected function load()
    {
        parent::load();

        if ($this->choices) {
            $documents = $this->choices;
        } elseif ($this->query) {
            $documents = $this->query->all();
        } else {
            $documents = $this->mongator->getRepository($this->class)->createQuery()->all();
        }
        $this->documents = $documents;

        $this->choices = array();
        foreach ($documents as $document) {
            if (null !== $this->field) {
                $value = $this->field;
            } elseif (method_exists($document, '__toString')) {
                $value = $document->__toString();
            } else {
                $value = $document->getId();
            }

            $this->choices[(string) $document->getId()] = $value;
        }
    }
}
