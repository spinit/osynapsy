<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;
use Osynapsy\Core\Lib\Tag;

class ImageBox extends Component
{
    private $image = array(
        'object' => null,
        'webPath' => null,
        'diskPath' => null,
        'dimension' => null,
        'width' => null,
        'height' => null,
        'maxwidth' => 0,
        'maxheight' => 0
    );
    
    private $resizeMethod = 'resize';
    private $toolbar;
    private $dummy;
    private $cropActive = false;
    
    public function __construct($id)
    {
        $this->requireCss('/vendor/osynapsy/Bcl/ImageBox2/style.css');
        $this->requireCss('/vendor/osynapsy/Bcl/ImageBox2/cropper.css');
        $this->requireJs('/vendor/osynapsy/Bcl/ImageBox2/cropper.js');
        $this->requireJs('/vendor/osynapsy/Bcl/ImageBox2/script.js');
        parent::__construct('div',$id);
        $this->att('class','osy-imagebox-bcl')->att('data-action','save');
        $this->add(new HiddenBox($id));
        $this->dummy = $this->add(new Tag('label'))
                            ->att('class','osy-imagebox-dummy')
                            ->att('for',$this->id.'_file');
        $file = $this->add(new Tag('input'));
        $file->att('type','file')->att('class','hidden')->att('id',$id.'_file')->name = $id;
        
        $this->toolbar = new Tag('div');
        $this->toolbar->att('class','osy-imagebox-bcl-cmd');
    }

    protected function __build_extra__()
    {
        $this->getImage();
        $this->checkCrop();
        $this->buildImage();
       
        if (empty($this->image['object'])) {
            $this->dummy->add(new Tag('span'))->att('class', 'fa fa-camera glyphicon glyphicon-camera');
            if ($this->image['maxwidth']) {
                $this->dummy->att('style','width : '.$this->image['maxwidth'].'px; height : '.$this->image['maxheight'].'px;');
            }
            return;
        }        
        $this->toolbar->add(new Tag('a'))
             ->att('href','javascript:void(0);')             
             ->att('data-cmd','delete')
             ->add('Elimina <span class="fa fa-trash cmd-execute" data-action="deleteImage" data-action-parameters="'.$this->image['webPath'].'"></span>');
        $this->add($this->toolbar);        
    }
    
    private function getImage()
    {
        if (empty($_REQUEST[$this->id])) {
            return;
        }
        $this->image['webPath'] = $_REQUEST[$this->id];
        $this->image['diskPath'] = $_SERVER['DOCUMENT_ROOT'].$this->image['webPath'];
        if (file_exists($this->image['diskPath'])) {
            $this->image['dimension'] = getimagesize($this->image['diskPath']);
        }
        if (empty($this->image['dimension'])) {
            return;
        }
        $this->image['width'] = $this->image['dimension'][0];
        $this->image['height'] = $this->image['dimension'][1];
        $this->image['formFactor'] = $this->image['width'] / $this->image['height'];
    }
    
    private function buildImage()
    {
        if (!file_exists($this->image['diskPath'])) { 
            return;
        }
        if ($this->cropActive) {
            $this->image['object'] = $this->add(new Tag('img'))->att('src', $this->image['webPath']);
        } else {
            $this->image['object'] = $this->dummy->add(new Tag('img'))->att('src', $this->image['webPath']);
        }
        $width = $this->image['width'];
        $height = $this->image['height'];
        if ($this->image['height'] > $this->image['maxheight']) {
            $height = $this->image['maxheight'];
            $width  = ceil($this->image['width'] * ($this->image['maxheight'] / $this->image['height']));
        } elseif ($this->image['width'] > $this->image['maxwidth']) {
            $width  = $this->image['width'];
            $height = ceil($this->image['height'] * ($this->image['maxwidth'] / $this->image['width']));
        }
        $this->image['object'];
             //->att('style','width:'.$width.'px; height: '.$height.'px;');
    }
    
    private function checkCrop()
    {    
        if ($this->image['width'] <= $this->image['maxwidth'] && $this->image['height'] <= $this->image['maxheight']) {                        
            return;
        }
        $this->cropActive = true;
        $this->att('data-max-width', $this->image['maxwidth']);
        $this->att('data-max-height', $this->image['maxheight']);
        $this->att('class','crop',true);
        $this->toolbar->add('Crop <span class="fa fa-crop crop-command"></span>');    
    }        
    
    public function setAction($action)
    {
        $this->att('data-action', $action);
    }
    
    public function setMaxDimension($width, $height)
    {
        $this->image['maxwidth'] = $width;
        $this->image['maxheight'] = $height;
        $this->image['formFactorIdeal'] = $width / $height;
        return $this;
    }
    
    public function setResizeByCrop()
    {
        $this->resizeMethod = 'crop';
        return $this;
    }
    
    public static function crop($path, $x, $y, $w, $h)
    {
        $img = self::imageCreateFromAny($path);
        $crp = imagecrop(
            $img, 
            array('x' => $x, 'y' => $y, 'width' => $w, 'height' =>$h)
        );
        imagepng($crp, $path);
        return true;
    }
    
    public static function imageCreateFromAny($filepath)
    { 
        $size = getImageSize($filepath); // [] if you don't have exif you could use getImageSize() 
        $type = $size[2];
        $allowedTypes = array( 
            1,  // [] gif 
            2,// [] jpg 
            3,  // [] png 
            6   // [] bmp 
        ); 
        if (!in_array($type, $allowedTypes)) { 
            return false; 
        } 
        switch ($type) { 
            case 1 : 
                $img = imageCreateFromGif($filepath); 
            break; 
            case 2 : 
                $img = imageCreateFromJpeg($filepath); 
            break; 
            case 3 : 
                $img = imageCreateFromPng($filepath); 
            break; 
            case 6 : 
                $img = imageCreateFromBmp($filepath); 
            break; 
        }    
        return $img;  
    }
}


