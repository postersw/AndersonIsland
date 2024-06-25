<?php
echo "HI I am a php file.3 ";
date_default_timezone_set("America/Los_Angeles"); // set PDT
echo "visible = " . getIlluminatedFractionOfMoon(JulianDateFromUnixTime(time()*1000));
exit();

/*
Greg Miller gmiller@gregmiller.net 2021
http://www.celestialprogramming.com/
Released as public domain
*/

function JulianDateFromUnixTime($t){
	//Not valid for dates before Oct 15, 1582
	return ($t / 86400000) + 2440587.5;
}

function UnixTimeFromJulianDate($jd){
	//Not valid for dates before Oct 15, 1582
	return ($jd-2440587.5)*86400000;
}	

function constrain($d){
    $t=$d%360;
    if($t<0){$t+=360;}
    return $t;
}

  function getIlluminatedFractionOfMoon($jd){
    //const toRad=Math.PI/180.0;
    $T=($jd-2451545)/36525.0;

    $D = deg2rad(constrain(297.8501921 + 445267.1114034*$T - 0.0018819*$T*$T + 1.0/545868.0*$T*$T*$T - 1.0/113065000.0*$T*$T*$T*$T)); //47.2
    $M = deg2rad(constrain(357.5291092 + 35999.0502909*$T - 0.0001536*$T*$T + 1.0/24490000.0*$T*$T*$T)); //47.3
    $Mp = deg2rad(constrain(134.9633964 + 477198.8675055*$T + 0.0087414*$T*$T + 1.0/69699.0*$T*$T*$T - 1.0/14712000.0*$T*$T*$T*$T)); //47.4

    //48.4
    $i=deg2rad(constrain(180 - $D*180/3.14159 - 6.289 * sin($Mp) + 2.1 * sin($M) -1.274 * sin(2*$D - $Mp) -0.658 * sin(2*$D) -0.214 * sin(2*$Mp)
     -0.11 * sin($D)));

    $k=(1+cos($i))/2;
    return $k;
}
?>