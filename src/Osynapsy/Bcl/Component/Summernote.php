<?php
namespace Osynapsy\Bcl\Component;

class Summernote extends TextArea
{
    public function __construct($name)
    {
        parent::__construct($name);        
        $this->att('class','summernote');
        $this->requireCss('/__asset/osynapsy/Bcl/Summernote/summernote.css');
        $this->requireJs('/__asset/osynapsy/Bcl/Summernote/summernote.js');
        $this->requireJs('/__asset/osynapsy/Bcl/Summernote/summernote.start.js');  
    }    
}
