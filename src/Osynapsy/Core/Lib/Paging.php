<?php
namespace Osynapsy\Core\Lib;

class Paging
{
    private $lArt;
    private $lArtPag;
    private $ArtNum = 0;
    private $pageCur = 1;
    private $pageLen = 5;    
    private $pageMax = 1;
    private $pageMin = 1;
    private $pageNum = 1;
    private $paginationLen=4;
    private $Htm='';    
    private $sectionUrl;
      
    public function __construct($lArt, $url, $pag=1)
    {
        $this->pageCur = empty($pag) ? 1 : $pag;
        $this->sectionUrl = $url;
        $this->lArt = $lArt;
        $this->ArtNum = count($lArt);
        if (!empty($this->ArtNum)) {
            $this->pageNum = ceil($this->ArtNum / $this->pageLen);
        }
        $this->Build();
    }
      
    public function Build()
    {
        
        $this->init();
        if ($this->pageMin > 1){
            $list = "<li><a href=\"".$this->sectionUrl."1\">&laquo;</a></li>";
        }
        for($i = $this->pageMin; $i <= $this->pageMax; $i++) {
            $sel = ($i == $this->pageCur) ? " class=\"active\"" : '';
            $list .= "<li{$sel}><a href=\"".$this->sectionUrl.$i."\">$i</a></li>";
        }
        if ($this->pageMax < $this->pageNum) {
            $list .= "<li><a href=\"".$this->sectionUrl."{$this->pageNum}\">&raquo;</a></li>";
        }
        $this->Htm = "<div class=\"center\"><ul class=\"pagination\">$list</ul></div>";
    }
      
    public function Get()
    {
        return $this->Htm;
    }
      
    public function GetArtPag()
    {
        return $this->lArtPag;
    }
      
    public function CountArt()
    {
        return $this->ArtNum;
    }
    
    private function pageCurrent()
    {
        if (
            !empty($this->pageCur) && 
            is_numeric($this->pageCur) && 
            $this->pageCur > 1
        ) {
            $this->pageCur = ($this->pageCur > $this->pageNum) ? $this->pageNum : $this->pageCur;
        }
        $this->RecSta = ($this->pageCur - 1) * $this->pageLen;
        $this->RecEnd = $this->RecSta + $this->pageLen;
        if ($this->RecEnd > $this->ArtNum) {
            $this->RecEnd = $this->ArtNum;
        }
        for ($i = $this->RecSta; $i < $this->RecEnd; $i++) {
            $this->lArtPag[] = $this->lArt[$i];
        }
    }
    
    private function init()
    {
        $this->pageCurrent();
        $a = ceil($this->paginationLen/2);
        $this->pageMin = $this->pageCur - $a;
        $DeltaMin = 0;
        
        if (($this->pageCur - $a) < 1) {
            $this->pageMin = 1;
            $DeltaMin = ($a -$this->pageCur)+1;
        }
        
        $this->pageMax = $this->pageCur + $a + $DeltaMin;
        if ($this->pageMax > $this->pageNum){
            $DeltaMax = $this->pageMax - $this->pageNum;
            $this->pageMax = $this->pageNum;
        }
        if (($this->pageMin > 1)){
            $this->pageMin = max(1,$this->pageMin-$DeltaMax);
        }
        
    }
}