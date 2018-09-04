<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * MergeGroupListener.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @author nvb <nvb@aproxima.ru>
 */
class MergeGroupListener implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [FormEvents::SUBMIT => ['onBind', 10]];
    }

    /**
     * @param FormEvent $event
     */
    public function onBind(FormEvent $event)
    {
        $group = $event->getForm()->getData();
        $data = $event->getData();

        $group->replace($data);

        $event->setData($group);
        $event->stopPropagation();
    }
}
