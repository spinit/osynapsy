<?php
namespace Osynapsy\Core;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Singleton
 *
 * @author ermanno
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Base
     */
    protected $object;
    
    public function setUp()
    {
        $this->object = new Base();
    }
    public function testSingleton()
    {
        $expected = null;
        $actual = $this->object->singleton('test');
        $this->assertEquals($expected, $actual);
        $expected = 'hello word';
        $actual = $this->object->singleton('test', $expected);
        $this->assertEquals($expected, $actual);
        $actual = $this->object->singleton('test');
        $this->assertEquals($expected, $actual);
    }
    
    public function testKernel()
    {
        $kernel = $this->object->kernel();
        $this->assertTrue($kernel instanceof \Osynapsy\Core\Kernel);
        
        $expected = 'hello word';
        $actual = $this->object->kernel($expected);
        $this->assertEquals($expected, $actual);
    }
}