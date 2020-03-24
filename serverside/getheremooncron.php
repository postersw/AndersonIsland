<?php
/////////////////////////////////////////////////////////////
//  getheremooncron - gets the moon json structure from https://weather.api.here.com/weather/1.0/report.xml
//  web site and writes it to moon.txt.
//  this file is picked up by the the app directly.
//  Called by cron every morning at midnight
//  Bob Bedoll. 10/31/19  Replaced the navy site because it is down till april 2020. Free account at here.com.
//
//  API RETURN:
//  {"astronomy":{"astronomy":[{"sunrise":"7:51AM","sunset":"5:55PM","moonrise":"11:58AM","moonset":"8:45PM","moonPhase":0.105,"moonPhaseDesc":"Waxing crescent","iconName":"cw_waxing_crescent"
//}
//
$file = "moondata.txt";
chdir("/home/postersw/public_html");  // move to web root

// code to get moon rise/set & phase

date_default_timezone_set("America/Los_Angeles"); // set PDT
$link = "https://weather.api.here.com/weather/1.0/report.json?app_id=HkBK0Kns18xl1CnGanFm&app_code=iyHvhhPZD4nQlBA2XVu2hw&product=forecast_astronomy&zipcode=98303";

echo $link; //debug

// Get the moon data - retry 10 times
$str = GetData($link);

// reformat the reply
$strout = FormatHtml($str);  // reformat
echo "<br/>$strout<br/>";
$j = file_put_contents($file, $strout);  // save the data
if($j <= 0) {  // if not success
    echo("moon cron run: file_put_contents ERROR !!!\n $j $strout");
    return 0;
}
echo("here.com moon cron run successful:\n $strout");
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
    echo " TIMES: $times ***";// debug //
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
    echo $link;
    for ($x = 0; $x <= 10; $x++) {
        $str = "";
        echo " GetData $x. ";
        $str = file_get_contents($link);
        echo "str=$str";
        if($str != false && $str != "") {echo "returned ok<br/>"; return $str;}
        sleep(10);
    }
    echo("<br/>moon cron run: NO here.com moon DATA after 10 tries for $link<br/>");
    return "";
}

?>