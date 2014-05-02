<?php

namespace Mongator\MongatorBundle\Tests;

use Mongator\Mongator;
use Mongator\Cache\ArrayCache;
use Mongator\Connection;
use Model\Mapping\Metadata;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $mongator;

    protected function setUp()
    {
        if (!class_exists('Mongo')) {
            $this->markTestSkipped('Mongo is not available.');
        }

        $this->mongator = new Mongator(new Metadata());
        $this->mongator->setConnection('global', new Connection('mongodb://localhost:27017', 'mongator_bundle'));
        $this->mongator->setDefaultConnectionName('global');
        $this->mongator->setFieldsCache(new ArrayCache());
        $this->mongator->setDataCache(new ArrayCache());

        foreach ($this->mongator->getAllRepositories() as $repository) {
            $repository->getCollection()->drop();
        }
    }
}