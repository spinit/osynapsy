<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Kernel;

class Summernote extends TextArea
{
    public function __construct($name)
    {
        parent::__construct($name);        
        $this->att('class','summernote');
        Kernel::$controller->response->addContent('<link rel="stylesheet" href="/vendor/bootstrap_template/vendor/summernote/dist/summernote.css">','head');
        Kernel::$controller->response->addContent('<script src="/vendor/bootstrap_template/vendor/summernote/dist/summernote.js"></script>','head');
        Kernel::$controller->response->addContent('<script src="/vendor/bootstrap_template/vendor/summernote/dist/summernote.start.js"></script>','head');
    }
}
