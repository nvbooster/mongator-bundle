<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle;

use Mongator\MongatorBundle\DependencyInjection\Compiler\MongatorMondatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Mongator\MongatorBundle\DependencyInjection\Compiler\MongatorEventDispatcherPass;
use Mandango\Mondator\Extension as MondatorExtension;

/**
 * MongatorBundle.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @author nvb <nvb@aproxima.ru>
 */
class MongatorBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MongatorMondatorPass());
        $container->addCompilerPass(new MongatorEventDispatcherPass());
        $container->registerForAutoconfiguration(MondatorExtension::class)
            ->addTag('mongator.mondator.extension');
    }
}
