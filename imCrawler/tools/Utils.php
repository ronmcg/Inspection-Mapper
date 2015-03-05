<?php

include_once(dirname(__FILE__) . '/../data/Data.php');

class Utils {

    /**
     * Get the lat/lng for the businesses with an idea between start and stop (inclusive)
     * Google only gives you 2,500 requests a day so this way we can spread it over a few days
     * @param int $start
     * @param int $stop
     */
    public function runLatLngRange($start, $stop){
        while($start <= $stop){
            $result = Data::selectBusinessByID($start);
            $address = $result[0]['address'];
            $id = $result[0]['bID'];
            $this->findLatLong($address, $id);
            $start++;
        }
    }

    /**
     * @param string $address
     * @param int $id
     */
    private function findLatLong($address, $id){
        $cleanAddress = strstr($address, '<br>Calgary AB', true);
        $cleanAddress .= 'Calgary'; //add calgary back on to the string
        $latlng = $this->getLatLng($cleanAddress);
        $lat = $latlng['lat'];
        $lng = $latlng['lng'];
        echo $id . ": " . $lat . ", " . $lng . "\n";
        Data::insertLatLng((string)$lat, (string)$lng, $id);
    }

    /**
     * Takes an address and returns the coordinates of that location
     * @param $address The address to Geolocate, (DO NOT INCLUDE PROVINCE AND POSTAL CODE)
     * @return array The latitude and longitude of the address
     */
    private function getLatLng($address){
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=@REPLACE@&key=AIzaSyCIDKrnJ-R3_rUqmg5pFvhVvYSlRRbE_0M';
        $address = str_replace(' ', '+', $address);
        $url = str_replace('@REPLACE@', $address, $url);
        $latlng = ['lat'=>0, 'lng'=>0];
        $json = file_get_contents($url);
        //get an array from the JSON
        $results = json_decode($json, true);
        $latlng['lat'] = $results['results'][0]['geometry']['location']['lat'];
        $latlng['lng'] = $results['results'][0]['geometry']['location']['lng'];
        return $latlng;
    }
} 