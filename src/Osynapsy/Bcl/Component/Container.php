<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;
use Osynapsy\Core\Lib\Tag;

class Container extends Tag
{
    private $currentRow;
    
    public function __construct($id, $tag='div')
    {
        parent::__construct($tag, $id);
        if ($tag == 'form'){
            $this->att('method','post');
        }
    }

    public function AddRow()
    {
        return $this->currentRow = $this->add(new Tag('div'))->att('class','row');
    }
    
    public function AddColumn($lg = 4, $sm = null, $xs = null)
    {
        $col = new Column($lg);
        $col->setSm($sm);
        $col->setXs($xs);
        if ($this->currentRow) {
            return $this->currentRow->add($col);
        }
        return $this->add($col);
    }
}
