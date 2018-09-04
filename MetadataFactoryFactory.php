<?php

namespace Mongator\MongatorBundle;

use Model\Mapping\MetadataFactory;
use Mongator\MetadataFactory as BaseMetadataFactory;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
class MetadataFactoryFactory
{
    /**
     * @return \Mongator\MetadataFactory
     */
    public function getMetadataFactory()
    {
        if (class_exists(MetadataFactory::class)) {
            return new MetadataFactory();
        } else {
            return new class() extends BaseMetadataFactory {
                protected $classes = [];
            };
        }
    }
}
