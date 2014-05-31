<?php

namespace Mongator\MongatorBundle\Tests\Extension;

use Mongator\MongatorBundle\Extension\Bundles;
use Mandango\Mondator\Container;
use Mandango\Mondator\Definition;
use Mandango\Mondator\Output;

class BundlesTest extends \PHPUnit_Framework_TestCase
{
    private $bundles;

    protected function setUp()
    {
        $this->bundles = new Bundles();
    }

    public function testClassProcess_IfNoBundleDataDoNothing()
    {
        $class = 'Model\Article';
        $configClasses = new \ArrayObject(
            array(
                $class => array()
            )
        );
        $container = new Container();

        $this->bundles->classProcess(
            $class,
            $configClasses,
            $container
        );

        $this->assertEmpty($container);
    }

    public function testClassProcess_IfNoDocumentShouldThrowException()
    {
        $class = 'Model\Article';
        $configClasses = new \ArrayObject(
            array(
                $class => array(
                    'fields' => array(
                        'title' => array('type' => 'string'),
                    ),
                    'bundle_name' => 'BlogBundle',
                    'bundle_namespace' => 'Blog\\BlogBundle',
                    'bundle_output' => 'src/'
                ),
            )
        );
        $container = new Container();

        $this->setExpectedException('InvalidArgumentException');
        $this->bundles->classProcess(
            $class,
            $configClasses,
            $container
        );
    }

    public function testClassProcess_Default()
    {
        $class = 'Model\\BlogBundle\\Article';

        $configClasses = new \ArrayObject(
            array(
                $class => array(
                    'fields' => array(
                        'title' => array('type' => 'string'),
                    ),
                    'isEmbedded' => false,
                    'bundle_name' => 'BlogBundle',
                    'bundle_namespace' => 'Blog\\BlogBundle',
                    'bundle_output' => 'src/'
                ),
            )
        );
        $container = new Container();
        foreach (array(
                    'document' => $class,
                    'document_base'   => 'Model\\BlogBundle\\Base\\Article',
                    'repository'      => 'Model\\BlogBundle\\ArticleRepository',
                    'repository_base' => 'Model\\BlogBundle\\Base\\ArticleRepository',
                    'query'           => 'Model\\BlogBundle\\ArticleQuery',
                    'query_base'      => 'Model\\BlogBundle\\Base\\ArticleQuery'
                ) as $key => $className) {

            $container[$key] = new Definition($className, new Output(sys_get_temp_dir()));
        }

        $this->bundles->classProcess(
            $class,
            $configClasses,
            $container
        );

        $this->assertEquals('Blog\\BlogBundle\\Model\\Article', $container['document_bundle']->getClass());
        $this->assertEquals('\\Model\\BlogBundle\\Base\\Article', $container['document_bundle']->getParentClass());
        //$this->assertTrue($container['document_bundle']->isAbstract());
        $this->assertEquals('Blog\\BlogBundle\\Model\\ArticleRepository', $container['repository_bundle']->getClass());
        $this->assertEquals('\\Model\\BlogBundle\\Base\\ArticleRepository', $container['repository_bundle']->getParentClass());
        //$this->assertTrue($container['repository_bundle']->isAbstract());
        $this->assertEquals('Blog\\BlogBundle\\Model\\ArticleQuery', $container['query_bundle']->getClass());
        $this->assertEquals('\\Model\\BlogBundle\\Base\\ArticleQuery', $container['query_bundle']->getParentClass());
        //$this->assertTrue($container['query_bundle']->isAbstract());

        $this->assertEquals('\\Blog\\BlogBundle\\Model\\Article', $container['document']->getParentClass());
        $this->assertEquals('\\Blog\\BlogBundle\\Model\\ArticleQuery', $container['query']->getParentClass());
        $this->assertEquals('\\Blog\\BlogBundle\\Model\\ArticleRepository', $container['repository']->getParentClass());
    }
}
