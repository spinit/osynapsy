<?php
namespace Osynapsy;
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
class ChanneSingletonTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGet()
    {
        $expected = null;
        $actual = Singleton::get('hi');
        $this->assertEquals($expected, $actual);
        $expected = 'hello word';
        $actual = Singleton::set('hi', $expected);
        $this->assertEquals($expected, $actual);
        $actual = Singleton::get('hi');
        $this->assertEquals($expected, $actual);
    }
    
    public function testKernel()
    {
        $kernel = Singleton::kernel();
        $this->assertTrue($kernel instanceof \Osynapsy\Core\Kernel);
        
        $expected = 'hello word';
        $actual = Singleton::kernel($expected);
        $this->assertEquals($expected, $actual);
    }
}