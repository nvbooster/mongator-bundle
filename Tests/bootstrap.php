<?php

$vendorDir = __DIR__.'/../vendor';

$loader = require $vendorDir . '/autoload.php';
$loader->add('Model', __DIR__);

/*
 * Generate Mongator model.
 */
$configClasses = array(
    'Model\Article' => array(
        'fields' => array(
            'title' => array('type' => 'string'),
        ),
    ),
);

use Mandango\Mondator\Mondator;

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Mongator\Extension\Core(array(
        'metadata_factory_class'  => 'Model\Mapping\Metadata',
        'metadata_factory_output' => __DIR__,
        'default_output'          => __DIR__
    )),
));
$mondator->process();
