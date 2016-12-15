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
class DictionaryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->object = new Core\Lib\Dictionary();
    }
    public function testSetGet()
    {
        $this->object->set('uno.due', '1');
        $this->object->set('uno.due', 'tre');
        $this->object->set('uno.tre', 3);
        foreach(array('due'=>'tre', 'tre'=>3) as $k=>$v) {
            $this->assertEquals($this->object['uno'][$k], $v);
        }
    }
}