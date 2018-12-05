<?php
/////////////////////////////////////////////////////////////
//  getcurrentweather - gets current weather.
//  Gets weather from openweathermap.org and hobolink.com (Oro bay weather station for local temp)
//  Inserts hobolink orobay temperature into openweathermap data.
//  OpenWeatherMap json return format for backward compatability:
//
//  rfb. 12/5/18.

// code for Open Weather Map current data
//{"coord":{"lon":-122.6,"lat":47.17},"weather":[{"id":800,"main":"Clear","description":"clear sky","icon":"01n"}],"base":"stations","main":{"temp":27.03,"pressure":1021,"humidity":100,"temp_min":19.4,"temp_max":35.96},"visibility":16093,"wind":{"speed":4.74,"deg":72.5002},"clouds":{"all":1},"dt":1544019300,"sys":{"type":1,"id":6003,"message":0.0043,"country":"US","sunrise":1544024490,"sunset":1544055663},"id":5812092,"name":"Steilacoom","cod":200}
$link = "http://api.openweathermap.org/data/2.5/weather?id=5812092&units=imperial&APPID=f0047017839b75ed3d166440bef52bb0";
$hobolink = "http://www.hobolink.com/p/de3ae8d02aff87b5523cf8b2e7491441";
$weatherfile = "currentweather.txt";
$owmstr = file_get_contents($link);  // get open weather map

// code for hobolink
$hobostr = file_get_contents($hobolink);

    // read the data from NOAA. Try 10 times. Then give up.
    ////for ($i = 0; $i <= 10; $x++) {
    ////    $str = "";
    ////    //$log .= "Time:  " . date("h:i:sa") . " #" . $i . "<br/>";////////////////////////
    ////    $str = file_get_contents($link);
    ////    if($str == FALSE) {
    ////    } else {
    ////        break;
    ////    }
    ////    //if($str != "") break;
    ////}

if($owmstr == "") {  // if no data
        echo("openweathermap: NO  DATA ");
        return 0;
}
if($hobostr == "") {  // if no data
    echo("hobolink: NO  DATA ");
}

// write to data file

$strout = reformatdata($owmstr, $hobostr);  // reformat
$fh = fopen($weatherfile, 'w');  // write to currentweather.txt.
fwrite($fh, $strout);
fclose($fh);

 echo $strout;  // return the data


/////////////////////////////////////////////////////////////////////////////////////////
// reformatdata - reformats openweathermap data by replacing fields from hobonet
//  replaces Air Temperature
//  Entry   $owmstr = json reply from open weather map
//          $hobostr = web page reply from hobolink
//  Exit    returns json reply with hobolink data inserted.

    function reformatdata($owmstr, $hobostr) {
        $jreply = json_decode($owmstr);  // decode the json reply
        $temp = "";
        $i = strpos($hobostr, "Air Temperature:");
        if($i > 0) $temp = getnumber($hobostr, $i);
        if($temp != "") $jreply->main->temp=$temp;  // save the temp
        echo $temp;
        $str = json_encode($jreply);
        return $str;
    }

    /////////////////////////////////////////////////////////////////////////////
    //  getnumber - returns the integer number after position $i in string $str
    function getnumber($str, $i) {
        $n = "";
        $e = $i+100;
        for(; $i<$e; $i++) {
            $s = substr($str, $i, 1);
            if($s>="0" && $s<="9") break;
        }
        $n = $s;
        for($i = $i + 1; $i<$e; $i++) {
            $s = substr($str, $i, 1);
            if($s>="0" && $s<="9") $n = $n . $s;
            else break;
        }
        return $n;
    }


?>