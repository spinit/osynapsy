<?php
namespace Osynapsy\Core\Helper\Geo;

/**
 * Description of GoogleMaps
 *
 * @author pietr
 */
class GoogleMaps 
{
    //put your code here
    public static function getLatLng($add)
    {
           $add = trim($add);
           $geourl = "http://maps.googleapis.com/maps/api/geocode/xml?address={$add}&sensor=false&region=it";
           
           // Create cUrl object to grab XML content using $geourl
           $c = curl_init();
           curl_setopt($c, CURLOPT_URL, utf8_encode($geourl));
           curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
           curl_setopt($c, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		   //curl_setopt($c, CURLOPT_CONNECTTIMEOUT ,2);
		   //curl_setopt($c, CURLOPT_TIMEOUT, 5);
           $xmlContent = trim(curl_exec($c));
           //$r = curl_getinfo($c);
           curl_close($c);
           // Create SimpleXML object from XML Content
           $xmlObject = &simplexml_load_string($xmlContent);
           // Print out all of the XML Object
           if ($xmlObject->status) {
               if ($xmlObject->status == 'OK') {
                    return array((float)$xmlObject->result->geometry->location->lat,(float)$xmlObject->result->geometry->location->lng);
               }
           }
           return false;
    }
}

