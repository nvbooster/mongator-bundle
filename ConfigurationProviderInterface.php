<?php

namespace Mongator\MongatorBundle;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
interface ConfigurationProviderInterface
{

    /**
     * @return array
     */
    public function getConfiguration();
}