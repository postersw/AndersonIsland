<?php
/////////////////////////////////////////////////////////////
//  getmooncron - gets the moon json structure from api.ipgeolocation.io.
//  web site and writes it to moondatainclude.txt.
//  this file is picked up by the include referenbe in dailycache.txt which is read by getdailycache.
//  Called by cron every 6 hours.
//
//  Bob Bedoll. 10/31/19  Replaced the navy site because it is down till april 2020. Free account at here.com.
//  1/15/23. Changed file name to moondatainclude.txt.
//  6/15/24. Free account at here.com no longer works.  Changed to ipgeolocation.io.  Free. Login with Google.
//              Note: no moon phase. :-( 
//
//
$file = "moondatainclude.txt";
chdir("/home/postersw/public_html");  // move to web root

// code to get moon rise/set & phase

date_default_timezone_set("America/Los_Angeles"); // set PDT
$ts="timestamp=" . time();
$link = "https://api.ipgeolocation.io/astronomy?apiKey=3875e20b21824feab11a2cbee8212ed5&lat=47.17869&long=-122.677";
//$latlong = "&longitude=-122.67700&latitude=47.17869";  // anderson island

// Get the moon data 
echo "$link\n";

$str = file_get_contents($link);
echo $str;

// reformat the reply
$strout = FormatHtml($str);  // reformat
//echo "<br/>$strout<br/>"; //debug
$j = file_put_contents($file, $strout);  // save the data
if($j <= 0) {  // if not success
    echo("moon cron run: file_put_contents ERROR !!!\n $j $strout");
    return 0;
}

return 0;

/////////////////////////////////////////////////////////////////////////////////////////
// formathtml - reformat data from usno.navy.mil format to HTML line to be output directly by app.
// https://api.ipgeolocation.io/astronomy?apiKey=3875e20b21824feab11a2cbee8212ed5&lat=47.17869&long=-122.677 
// {"location":{"latitude":47.17869,"longitude":-122.677},
// "date":"2024-06-15",
// "current_time":"10:38:59.488",
// "sunrise":"05:14","sunset":"21:08","sun_status":"-",
//  "solar_noon":"13:11","day_length":"15:54","sun_altitude":51.41838635723647,
//  "sun_distance":151943166.15954477,"sun_azimuth":114.69505636196936,
//  "moonrise":"14:46","moonset":"01:50","moon_status":"-",
//  "moon_altitude":-37.30602534378108,"moon_distance":403678.96151010634,"moon_azimuth":47.29483719824111,
//  "moon_parallactic_angle":-30.06691555434202}
//  output: Moon: Rise hh:mm, Set hh:mm, ICON for PHASE

function FormatHtml($reply) {
    global $linktomorrow;
    $jreply = json_decode($reply);  // decode the json reply
    if(is_null($jreply)) echo "<br/>Error decoding Today json data<br/>";
    //echo $jreply; ////////////////// DEBUG //////////////////////////
    $risetime = toAMPM($jreply->moonrise);
    $settime = toAMPM($jreply->moonset);
    $status = $jreply->moon_status;
    //echo "risetime $risetime, settime $settime, status $status <br>";
    $angle = (int)$jreply->moon_parallactic_angle;
    //$visible = 100 - (int)(cos(deg2rad($angle))/2*100);  // compute visible portion
    $visible = (int)(getIlluminatedFractionOfMoon(JulianDateFromUnixTime(time()*1000))*100);
    $img = "";
    if($visible > 0) {
        if($visible < 30) $icon='moon_waxcres';
        elseif($visible<60) $icon = 'moon_firstqtr';
        elseif($visible<90) $icon = "moon_waxgib";
        else $icon="moon_full";
        $img = "<img style='vertical-align:middle' src='img/$icon.png' width=30 height=30>";
    }
    $times = "Rise $risetime, Set $settime, $img $visible%";

    // get phase and icon
    // $pct =  number_format(abs($jreply->astronomy->astronomy[0]->moonPhase * 100));
    // $icon = "";
    // $mp = "";
    // $arrow = " ";
    // switch (strtolower($jreply->astronomy->astronomy[0]->moonPhaseDesc)) {
    //     case "first quarter": $mp = "First"; $icon = "moon_firstqtr";$arrow = "<i class='material-icons'>&#xe5d8;</i>"; break;// up arrow
    //     case "new moon": $mp = "New"; $icon = "moon_new";$arrow = "<i class='material-icons'>&#xe5d8;</i>"; break;
    //     case "waxing crescent": $mp = "Waxing"; $icon = "moon_waxcres";$arrow = "<i class='material-icons'>&#xe5d8;</i>"; break;
    //     case "waxing gibbous": $mp = "Waxing"; $icon = "moon_waxgib";$arrow = "<i class='material-icons'>&#xe5d8;</i>"; break;
    //     case "full moon": $mp = "Full"; $icon = "moon_full";$arrow="<i class='material-icons'>&#xe5db;</i>"; break;
    //     case "waning gibbous": $mp = "Waning"; $icon = "moon_wangib";$arrow="<i class='material-icons'>&#xe5db;</i>"; break;//down arrow
    //     case "last quarter": $mp = "Last"; $icon = "moon_lastqtr";$arrow="<i class='material-icons'>&#xe5db;</i>"; break;
    //     case "waning crescent": $mp = "Waning"; $icon = "moon_wancres";$arrow="<i class='material-icons'>&#xe5db;</i>"; break;
    // }
    // //  final return;
    // if($pct == "") $pct = $mp; // if no pct, use the phase
    //$s = "<br/>Moon:<span style='font-weight:normal'> <img style='vertical-align:middle' src='img/$icon.png' width=30 height=30> $pct% $arrow $times</span> ";
    $s = "<br/>Moon:<span style='font-weight:normal'> $times";
    echo $s;
    return $s;
}

///////////////////////////////////////////////////////////////////////////////
// toAMPM - convert 24 hr to 12hr
// entry hh:mm string
//  exit hh:mm am|pm string
function toAMPM($t) {
    if($t=="") return $t;
    $h = intval(substr($t,0,2));
    if($h>11) $ampm="pm";
    else $ampm = "am";
    if($h>12) $h = $h-12;
    return $h . substr($t,2,3) . $ampm;
}



/*
Greg Miller gmiller@gregmiller.net 2021
http://www.celestialprogramming.com/
Released as public domain
This seems to work for us. 6/17/24.
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