<?php
namespace Osynapsy\Ocl\Component;
/*
 +-----------------------------------------------------------------------+
 | lib/components/omapgrid.php                                           |
 |                                                                       |
 | This file is part of the Opensymap                                    |
 | Copyright (C) 2005-2013, Pietro Celeste - Italy                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Create page form for generate datagrid and treegrid                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Pietro Celeste <pietro.celeste@gmail.com>                     |
 +-----------------------------------------------------------------------+

 $Id:  $

/**
 * @email           pietro.celeste@opensymap.org
 * @date-creation   31/10/2014
 * @date-update     31/10/2014
 */
 

class MapLeafletBox extends Component
{
	private $map;
	private $cnt;
	
	public function __construct($name)
	{
		parent::__construct('dummy',$name);
		$this->requireCss(OSY_WEB_ROOT.'/css/leaflet.css');
		$this->requireCss(OSY_WEB_ROOT.'/css/leaflet.draw.css');
		$this->requireJs('/__assets/Lib/Leaflet/leaflet.js');
		$this->requireJs('/__assets/Lib/Leaflet/leaflet.awesome-markers.min.js');
		$this->requireJs('/__assets/Lib/Leaflet/leaflet.draw.js');
		$this->requireJs('/__assets/Ocl/MapLeafletBox/script.js');

		$this->map = $this->add(tag::create('div'))->att('class','osy-mapgrid-leaflet');
		$this->add(new HiddenBox($this->id.'_ne_lat'));
        $this->add(new HiddenBox($this->id.'_ne_lng'));
        $this->add(new HiddenBox($this->id.'_sw_lat'));
        $this->add(new HiddenBox($this->id.'_sw_lng'));
        $this->add(new HiddenBox($this->id.'_center'));
  	    $this->add(new HiddenBox($this->id.'_cnt_lat'));
        $this->add(new HiddenBox($this->id.'_cnt_lng'));
		$this->add(new HiddenBox($this->id.'_zoom'));
	}
	
	public function __build_extra__()
	{
		foreach($this->get_att() as $k => $v) {
			if (is_numeric($k)) {
                continue;
            }
			$this->map->att($k, $v, true);
		}
		if (empty($res)){ 
		  	$res = array(
                array(
                    'lat'=>41.9100711,
                    'lng'=>12.5359979
                )
            );	
		}
		$this->map->att('coostart', $res[0]['lat'].','.$res[0]['lng'].','.$res[0]['ico']);
		if (empty($_REQUEST[$this->id.'_center'])) {
			$_REQUEST[$this->id.'_center'] = $res[0]['lat'].','.$res[0]['lng'];
		}
        if ($grid = $this->get_par('datagrid-parent')){
            $this->map->att('data-datagrid-parent',$grid);
        }
	}
}
