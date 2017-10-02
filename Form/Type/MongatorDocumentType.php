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

use Mongator\Mongator;
use Mongator\MongatorBundle\Form\DataTransformer\MongatorDocumentsToArrayTransformer;
use Mongator\MongatorBundle\Form\EventListener\MergeGroupListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * MongatorDocumentType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorDocumentType extends AbstractType
{
    /**
     * @var Mongator
     */
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
                ->addViewTransformer(new MongatorDocumentsToArrayTransformer(), true);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractType::configureOptions()
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = function (Options $options) {

            if (!$this->mongator->getMetadataFactory()->hasClass($options['class'])) {
                throw new InvalidConfigurationException(sprintf('Class %s is not registered in mongator', $options['class']));
            }

            $collection = (is_array($options['criteria']) && count($options['criteria'])) ?
                $this->mongator->getRepository($options['class'])->createQuery()->criteria($options['criteria']) :
                $this->mongator->getRepository($options['class'])->createQuery()->all()
            ;

            $choices = array();
            foreach ($collection as $document) {
                $choices[(string) $document->getId()] = $document;
            }

            return $choices;
        };

        $resolver->setDefaults(array(
            'choice_label' => function($choice) { return (string) $choice; },
            'criteria' => array(),
            'choices' => $choices,
        ));

        $resolver->setRequired('class');
        $resolver->addAllowedTypes('class', 'string');
        $resolver->addAllowedTypes('criteria', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mongator_document';
    }
}
