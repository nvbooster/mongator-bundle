<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\Form\Type;

use Mongator\MongatorBundle\Form\ChoiceList\MongatorDocumentChoiceList;
use Mongator\MongatorBundle\Form\DataTransformer\MongatorDocumentToIdTransformer;
use Mongator\MongatorBundle\Form\DataTransformer\MongatorDocumentsToArrayTransformer;
use Mongator\MongatorBundle\Form\EventListener\MergeGroupListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Mongator\Mongator;

/**
 * MongatorDocumentType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorDocumentType extends AbstractType
{
    private $mongator;

    /**
     * Constructor.
     *
     * @param Mongator $mongator The mongator.
     */
    public function __construct(Mongator $mongator)
    {
        $this->mongator = $mongator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $builder
                ->addEventSubscriber(new MergeGroupListener())
                ->prependClientTransformer(new MongatorDocumentsToArrayTransformer($options['choice_list']));
        } else {
            $builder->prependClientTransformer(new MongatorDocumentToIdTransformer($options['choice_list']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'template' => 'choice',
            'multiple' => false,
            'expanded' => false,
            'mongator' => $this->mongator,
            'class' => null,
            'field' => null,
            'query' => null,
            'choices' => array(),
            'preferred_choices' => array(),
        );

        $options = array_replace($defaultOptions, $options);

        if (!isset($options['choice_list'])) {
            $defaultOptions['choice_list'] = new MongatorDocumentChoiceList(
                $options['mongator'],
                $options['class'],
                $options['field'],
                $options['query'],
                $options['choices']
            );
        }

        return $defaultOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mongator_document';
    }
}
