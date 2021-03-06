<?php

namespace Mongator\MongatorBundle\Extension;

use Mandango\Mondator\Extension;
use Mongator\Type\Container;

/**
 * CustomType
 *
 * @author nvb <nvb@aproxima.ru>
 */
class CustomType extends Extension
{
    /**
     * @param string $name
     * @param string $class
     */
    public function addCustomType($name, $class)
    {
        Container::add($name, $class);
    }
}
