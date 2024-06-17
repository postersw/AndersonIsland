<?php
/////////////////////////////////////////////////////////////
//  getheremooncron - gets the moon json structure from https://weather.api.here.com/weather/1.0/report.xml
//  web site and writes it to moon.txt.
//  this file is picked up by the the app directly.
//  Called by cron every morning at midnight
//  Bob Bedoll. 10/31/19  Replaced the navy site because it is down till april 2020. Free account at here.com.
//  1/15/23. Changed file name to moondatainclude.txt.
//  6/15/24. Request comes up unauthorized.  Got new credentials but it still comes up Unauthorized.  
//           Can't get it to work. Abandoned.
//  New keys 6/15/24:
//  here.com app id = P0heauAYz72Rp4VuKJH7
//  here.com api key=cPxMQ5P7fCJdZhZ3xrTKVzS-4L8yjNzzsglTR5MpSrA
//
//  API RETURN:
//  {"astronomy":{"astronomy":[{"sunrise":"7:51AM","sunset":"5:55PM","moonrise":"11:58AM","moonset":"8:45PM","moonPhase":0.105,"moonPhaseDesc":"Waxing crescent","iconName":"cw_waxing_crescent"
//}
//
$file = "moondatainclude.txt";
chdir("/home/postersw/public_html");  // move to web root

// code to get moon rise/set & phase

date_default_timezone_set("America/Los_Angeles"); // set PDT
//$link = "https://weather.api.here.com/weather/1.0/report.json?app_id=P0heauAYz72Rp4VuKJH7&appKey=cPxMQ5P7fCJdZhZ3xrTKVzS-4L8yjNzzsglTR5MpSrA&product=forecast_astronomy&name=seattle";
$link = "https://weather.api.here.com/weather/1.0/report.json?appKey=cPxMQ5P7fCJdZhZ3xrTKVzS-4L8yjNzzsglTR5MpSrA&product=forecast_astronomy&name=seattle";

echo $link; //debug
//return;

// Get the moon data - retry 10 times
$str = GetData($link);
if($str==false) exit("no data");
if($str=="") exit("no data");

// reformat the reply
$strout = FormatHtml($str);  // reformat
echo "<br/>$strout<br/>"; //debug
$j = file_put_contents($file, $strout);  // save the data
if($j <= 0) {  // if not success
    echo("moon cron run: file_put_contents ERROR !!!\n $j $strout");
    return 0;
}
//echo("here.com moon cron run successful:\n $strout");
return 0;

/////////////////////////////////////////////////////////////////////////////////////////
// formathtml - reformat data from usno.navy.mil format to HTML line to be output directly by app.
//  input: //  {"astronomy":{"astronomy":[{"sunrise":"7:51AM","sunset":"5:55PM","moonrise":"11:58AM","moonset":"8:45PM","moonPhase":0.105,"moonPhaseDesc":"Waxing crescent","iconName":"cw_waxing_crescent"
//  output: Moon: Rise hh:mm, Set hh:mm, ICON for PHASE

function FormatHtml($reply) {
    global $linktomorrow;
    $jreply = json_decode($reply);  // decode the json reply
    if(is_null($jreply)) echo "<br/>Error decoding Today json data<br/>";
    //echo $jreply; ////////////////// DEBUG //////////////////////////
    $risetime = "";
    $settime = "";
    $risetime = $jreply->astronomy->astronomy[0]->moonrise;
    $settime = $jreply->astronomy->astronomy[0]->moonset;
    $times = "Rise:$risetime Set:$settime";
    $times = str_replace("AM", "a", $times);  // replace AM and PM for shorter string
    $times = str_replace("PM", "p", $times);
    //echo " TIMES: $times ***";// debug //
    // get phase and icon
    $pct =  number_format(abs($jreply->astronomy->astronomy[0]->moonPhase * 100));
    $icon = "";
    $mp = "";
    $arrow = " ";
    switch (strtolower($jreply->astronomy->astronomy[0]->moonPhaseDesc)) {
        case "first quarter": $mp = "First"; $icon = "moon_firstqtr";$arrow = "<i class='material-icons'>&#xe5d8;</i>"; break;// up arrow
        case "new moon": $mp = "New"; $icon = "moon_new";$arrow = "<i class='material-icons'>&#xe5d8;</i>"; break;
        case "waxing crescent": $mp = "Waxing"; $icon = "moon_waxcres";$arrow = "<i class='material-icons'>&#xe5d8;</i>"; break;
        case "waxing gibbous": $mp = "Waxing"; $icon = "moon_waxgib";$arrow = "<i class='material-icons'>&#xe5d8;</i>"; break;
        case "full moon": $mp = "Full"; $icon = "moon_full";$arrow="<i class='material-icons'>&#xe5db;</i>"; break;
        case "waning gibbous": $mp = "Waning"; $icon = "moon_wangib";$arrow="<i class='material-icons'>&#xe5db;</i>"; break;//down arrow
        case "last quarter": $mp = "Last"; $icon = "moon_lastqtr";$arrow="<i class='material-icons'>&#xe5db;</i>"; break;
        case "waning crescent": $mp = "Waning"; $icon = "moon_wancres";$arrow="<i class='material-icons'>&#xe5db;</i>"; break;
    }
    //  final return;
    if($pct == "") $pct = $mp; // if no pct, use the phase
    $s = "<br/>Moon:<span style='font-weight:normal'> <img style='vertical-align:middle' src='img/$icon.png' width=30 height=30> $pct% $arrow $times</span> ";
    // fix times
    return $s;
}

///////////////////////////////////////////////////////////////////////////////
// Get data after 10 tries
//  Entry   link = address
//  returns data
function GetData($link) {
    //echo $link;
    for ($x = 0; $x <= 10; $x++) {
        $str = "";
        //echo " GetData $x. ";
        $str = file_get_contents($link);
        //echo "str=$str";
        if($str != false && $str != "") {return $str;}
        sleep(10);
        echo " GetData Try $x. ";
    }
    echo("<br/>moon cron run: NO here.com moon DATA after 10 tries for $link<br/>");
    return "";
}

?>