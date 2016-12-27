<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;

class FileBox extends Component
{
    protected $fileBox;
    public $showImage = false;
    public $span;
    
    public function __construct($name, $postfix=true, $prefix=true)
    {
         /* 
            http://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3/
            <span class="input-group-btn">
                <span class="btn btn-primary btn-file">
                    Browse&hellip; <input type="file" multiple>
                </span>
            </span>
        */
        $this->requireJs('/__assets/osynapsy/Bcl/FileBox/script.js');
        
        parent::__construct('dummy',$name);
        $this->span = $this->add(new Tag('span'));
        $div = $this->add(new Tag('div'));
        $div->att('class','input-group')
                    ->add(new Tag('span'))
                    ->att('class', 'input-group-btn')
                    ->add(new Tag('span'))
                    ->att('class','btn btn-primary btn-file')
                    ->add('<input type="file" name="'.$name.'"><span class="fa fa-folder-open"></span>');
        $div->add('<input type="text" class="form-control" readonly>');
        if (!$postfix) {
            return;
        }
        $div->add(new Tag('span'))
             ->att('class', 'input-group-btn')
             ->add(new Tag('button'))
             ->att('class','btn btn-primary')
             ->att('type','submit')
             ->add('Send');        
    }
    
    protected function __build_extra__()
    {
        //var_dump( $_REQUEST );
        if ($this->showImage && !empty($_REQUEST[$this->id])) {
            $this->span->add(new Tag('img'))->att('src',$_REQUEST[$this->id]);
            $this->span->add('');
        }
    }
}

