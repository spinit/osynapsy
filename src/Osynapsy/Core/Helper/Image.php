<?php
namespace Osynapsy\Core\Helper;

class Image
{
    private $image = array();
    private $info = array();
    private $path = null;
    private $allowedTypes = array( 
        1,  // [] gif 
        2,  // [] jpg 
        3,  // [] png 
        6   // [] bmp 
    );
    
    public function __construct($path=null)
    {
        if (is_null($path)) {
            return;
        }
        $this->load($path, true);
    }
    
    public function create($width, $height, $color =  array(), $type = 3)
    {
        $this->image = imagecreatetruecolor($width, $height);
        if (!empty($color)) {
            $col = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
            imagefill($this->image, 0, 0, $col);
        }
        $this->info = [$width, $height, $type];
    }
    
    public function crop($x, $y, $w, $h)
    {
        $croppedImage = imagecrop(
            $this->image, 
            array(
                'x' => intval($x), 
                'y' => intval($y), 
                'width' => intval($w),
                'height' => intval($h)
            )
        );        
        if ($croppedImage) {
            $this->image = $croppedImage;
            return true;
        }
        return false;
    }
    
    public function getDimension()
    {
        return $this->info;
    }

    public function getPath()
    {
        return $this->path;
    }
    
    private function load($path, $init = false)
    {
        $info = getimagesize($path);
        if (!in_array($info[2], $this->allowedTypes)) { 
            return false; 
        } 
        switch ($info[2]) { 
            case 1 : 
                $image = imageCreateFromGif($path); 
                break; 
            case 2 : 
                $image = imageCreateFromJpeg($path); 
                break; 
            case 3 : 
                $image = imageCreateFromPng($path); 
                break; 
            case 6 : 
                $image = imageCreateFromBmp($path); 
                break; 
        }
        if (!$init) {
            return [$image, $info];
        }
        $this->path = $path;
        $this->info = $info;
        $this->image = $image;
    }
    
    public function merge($path, $x, $y)
    {
        list($source, $sourceDim) = $this->load($path);
        //imagesavealpha($this->image, true);
        imagecopy($this->image, $source, $x, $y, 0, 0, $sourceDim[0], $sourceDim[1]);                
    }
    
    public function resize($newWidth, $newHeight)
    {        
        $resizedImage = imagecreatetruecolor(intval($newWidth), intval($newHeight));
        imagecopyresampled(
            $resizedImage, 
            $this->image, 
            0, 
            0, 
            0, 
            0, 
            $newWidth ,
            $newHeight, 
            $this->info[0],
            $this->info[1]
         );
         $this->image = $resizedImage;
         $this->info[0] = $newWidth;
         $this->info[1] = $newHeight;
    }

    public function resizeAdaptive($newWidth, $newHeight)
    {
        $oldFormFactor = $this->info[0] / $this->info[1];
        $newFormFactor = $newWidth / $newHeight;
        if ($oldFormFactor == $newFormFactor) {
            $this->resize($newWidth, $newHeight);
            return;
        }
        if ($this->info[0] > $this->info[1]) {
            $newHeight = $newHeight / $oldFormFactor;
        } else {
            $newWidth = $newWidth * $oldFormFactor;
        }
        $this->resize($newWidth, $newHeight);
    }
    
    public function save($path)
    {        
        imagepng($this->image, $path);
        $this->path = $path;
    }
}
