<?php
/////////////////////////////////////////////////////////////
//  getmooncron - gets the moon json structure from http://api.usno.navy.mil
//  web site and writes it to moon.txt.
//  this file is picked up by the the app directly.
//  Called by cron every morning at 1am.
//  Bob Bedoll. 8/18/18.
//
//  API REST CALL: http://api.usno.navy.mil/rstt/oneday?date=8/18/2018&loc=Seattle,%20WA
//  API RETURN:
//  {
//"error":false,
//"apiversion":"2.1.0",
//"year":2018,
//"month":8,
//"day":18,
//"dayofweek":"Saturday",
//"datechanged":false,
//"state":"WA",
//"city":"Seattle",
//"lon":-122.33,
//"lat":47.63,
//"county":"King",
//"tz":-8,
//"isdst":"yes",

//"sundata":[
//            {"phen":"BC", "time":"5:36 a.m. DT"},
//            {"phen":"R", "time":"6:09 a.m. DT"},
//            {"phen":"U", "time":"1:13 p.m. DT"},
//            {"phen":"S", "time":"8:16 p.m. DT"},
//            {"phen":"EC", "time":"8:49 p.m. DT"}],

//"moondata":[
//            {"phen":"R", "time":"2:41 p.m. DT"},
//            {"phen":"U", "time":"7:36 p.m. DT"}],

//"nextmoondata":[
//            {"phen":"S","time":"12:25 a.m. DT"}],

//"closestphase":{"phase":"First Quarter","date":"August 18, 2018","time":"12:48 a.m. DT"}
//}
//
$file = "moondata.txt";
chdir("/home/postersw/public_html");  // move to web root

// code to get moon rise/set & phase

date_default_timezone_set("America/Los_Angeles"); // set PDT
$link = "http://api.usno.navy.mil/rstt/oneday?loc=Seattle,%20WA&date=";
$ts = time();
$y = date("Y", $ts); // year, e.g. 2017
$m = date ("m", $ts); // month with leading zero
$d = date("d", $ts); // day with leading zero
$link = $link . $m . "/" . $d . "/" . $y;
echo $link; //debug


// retry 10 times
for ($x = 0; $x <= 10; $x++) {
    $str = "";
    $str = file_get_contents($link);
    if($str != "") break;
}
if($str == "") {  // if no data
    echo("moon cron run: NO usno.navy.mil moon DATA after 10 tries!!!");
    return 0;
}

$strout = FormatHtml($str);  // reformat
echo "<br/>$strout<br/>";
$j = file_put_contents($file, $strout);  // save the data
if($j <= 0) {  // if not success
    echo("moon cron run: file_put_contents ERROR !!!\n $j $strout");
    return 0;
}
echo("NAVY moon cron run successful:\n $strout");
return 0;

/////////////////////////////////////////////////////////////////////////////////////////
// formathtml - reformat data from usno.navy.mil format to HTML line to be output directly by app.
//  input: see format above
//  output: Moon: Rise hh:mm, Set hh:mm, ICON for PHASE

function FormatHtml($reply) {
    $jreply = json_decode($reply);  // decode the json reply
    //var_dump($jreply);
    //echo count($jreply->predictions) . " items. <br/>";
    $st = $jreply->moondata[0]->time;  // start time
    $et = $jreply->nextmoondata[0]->time;  // end time n:nn p.m. DT
    echo $st, $et;
    // if an error
    if ($st == "") {
        echo "ERROR - no tides returned. \n";
        die( "ERROR - no tides returned.");
    }

    $phase = $jreply->closestphase->phase;
    echo $phase;
    $icon = "";
    $mp = "";
    switch ($phase) {
        case "First Quarter": $mp = "First"; $icon = "moon_firstqtr";break;
        case "New Moon": $mp = "New"; $icon = "moon_new"; break;
        case "Waxing Crescent": $mp = "Waxing"; $icon = "moon_waxcres"; break;
        case "Waxing Gibbous": $mp = "Waxing"; $icon = "moon_waxgib"; break;
        case "Full Moon": $mp = "Full"; $icon = "moon_full"; break;
        case "Waning Gibbous": $mp = "Waning"; $icon = "moon_wangib"; break;
        case "Last Quarter": $mp = "Last"; $icon = "moon_lastqtr"; break;
        case "Waning Crescent": $mp = "Waning"; $icon = "moon_wancres"; break;
    }
    $s = "<br/>Moon:<span style='font-weight:normal'> $mp <img style='vertical-align:middle' src='img/$icon.png' width=30 height=30> Rise: $st | Set: $et</span> ";
    // fix times
    $s = str_replace("a.m.", "AM", $s);
    $s = str_replace("p.m.", "PM", $s);
    $s = str_replace("DT", "", $s);
    $s = str_replace("ST", "", $s);
    return $s . "";
}

?>