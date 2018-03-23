<?php

namespace Mongator\MongatorBundle;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
class ConfigurationManager
{
    /**
     * @var array
     */
    private $providers = [];

    /**
     * @param ConfigurationProviderInterface $provider
     * @param integer                        $priority
     */
    public function addProvider(ConfigurationProviderInterface $provider, $priority = 10)
    {
        $this->providers[] = [$provider, 10];
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        $providers = [];
        $priorities = [];

        foreach ($this->providers as $p) {
            $providers[] = $p[0];
            $priorities[] = $p[1];
        }

        array_multisort($priorities, SORT_ASC, $providers);

        $configClasses = [];
        /**
         * @var ConfigurationProviderInterface $provider
         */
        foreach ($providers as $provider) {
            foreach ($provider->getConfiguration() as $class => $config) {
                if (empty($configClasses[$class])) {
                    $configClasses[$class] = $config;
                } else {
                    $configClasses[$class] = Util::arrayDeepMerge($configClasses[$class], $config);
                }
            }
        }

        return $configClasses;
    }
}