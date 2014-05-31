<?php

namespace Mongator\MongatorBundle\Tests;

use Mongator\MongatorBundle\Util;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayDeepMerge()
    {
        $a = array(
            0,
            array(0),
            'integer' => 123,
            'integer456_merge_with_integer444' => 456,
            'integer789_merge_with_array777' => 789,
            'array' => array('string1', 'string2'),
            'array45_merge_with_array6789' => array('string4', 'string5'),
            'arraykeyabc_merge_with_arraykeycd' => array('a' => 'a', 'b' => 'b', 'c' => 'c'),
            'array0_merge_with_integer3' => array(0),
            'multiple_merge' => array(1),
        );

        $b = array(
            'integer456_merge_with_integer444' => 444,
            'integer789_merge_with_array777' => array(7, 7, 7),
            'array45_merge_with_array6789' => array('string6', 'string7', 'string8', 'string9'),
            'arraykeyabc_merge_with_arraykeycd' => array('c' => 'ccc', 'd' => 'ddd'),
            'array0_merge_with_integer3' => 3,
            'multiple_merge' => array(2),
        );

        $c = array(
            'multiple_merge' => array(3),
        );

        $expected = array(
            0 => 0,
            1 =>
                array(
                    0 => 0,
                ),
            'integer' => 123,
            'integer456_merge_with_integer444' => 444,
            'integer789_merge_with_array777' =>
                array(
                    0 => 7,
                    1 => 7,
                    2 => 7,
                ),
            'array' =>
                array(
                    0 => 'string1',
                    1 => 'string2',
                ),
            'array45_merge_with_array6789' =>
                array(
                    0 => 'string4',
                    1 => 'string6',
                    2 => 'string5',
                    3 => 'string7',
                    4 => 'string8',
                    5 => 'string9',
                ),
            'arraykeyabc_merge_with_arraykeycd' =>
                array(
                    'a' => 'a',
                    'b' => 'b',
                    'c' => 'ccc',
                    'd' => 'ddd',
                ),
            'array0_merge_with_integer3' => 3,
            'multiple_merge' =>
                array(
                    0 => 1,
                    1 => 3,
                    2 => 2,
                ),
        );

        $this->assertEquals($expected, Util::arrayDeepMerge($a, $b, $c));
    }
}
