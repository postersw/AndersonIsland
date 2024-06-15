<?php
/////////////////////////////////////////////////////////////
//  getmooncron - gets the moon json structure from https://api.pulsewise.com/astronomy
//  web site and writes it to moondatainclude.txt.
//  https://api.pulsewise.com/astronomy?latitude=47.17869&22.677&timestamp=1700410825
//  this file is picked up by the include referenbe in dailycache.txt which is read by getdailycache.
//  Called by cron every morning at midnight
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
//$latlong = "";
//$link = $link . $latlong;
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
    echo "risetime $risetime, settime $settime, status $status <br>";
    $angle = (int)$jreply->moon_parallactic_angle;
    $visible = (int)(cos(deg2rad($angle))/2*100);  // compute visible portion
    $times = "Rise $risetime, Set $settime, Visible $visible%";

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

?>