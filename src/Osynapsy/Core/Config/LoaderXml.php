<?php
namespace Osynapsy\Core\Config;

use Osynapsy\Core\Lib\Dictionary;

/**
 * Description of LoaderXml
 *
 * @author Pietro Celeste <p.celeste@osynapsy.org>
 */
class LoaderXml
{    
    private $repo;
    
    public function __construct()
    {            
        $this->repo = new Dictionary();        
    }
        
    public function load($path, $prefix='')
    {    
        if (!is_file($path)) {
            return;
        }
        $tree = simplexml_load_file($path);
        $this->grabElement($tree, $prefix);        
    }
    
    private function grabElement($element, $parent = '')
    {        
        $name = $element->getName();
        $path = $parent . (empty($parent) ? '' : '.') . $name;
        $attr = $this->grabAttribute($element);
        if ($element->count() === 0) {           
            $attr['value'] = $this->grabValue($element, $path);                        
        }
        if (!empty($attr)) {
            $this->repo->append($path, $attr);
        }
        foreach ($element as $child) {
            $this->grabElement($child, $path);
        }        
    }
    
    private function grabAttribute($element)
    {
        $attributes = array();
        foreach($element->attributes() as $key => $value) {
            $attributes[$key] = (string) $value;
        }
        return $attributes;
    }
    
    private function grabValue($element)
    {     
        $value = (string) $element;        
        return trim($value);
    }
    
    public function get($key)
    {
        return $this->repo->get($key);
    }
}
