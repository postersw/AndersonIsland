<?php
/////////////////////////////////////////////////////////////
//  getmooncron - gets the moon json structure from http://api.usno.navy.mil
//  web site and writes it to moon.txt.
//  this file is picked up by the the app directly.
//  Called by cron every morning at 1am.
//  Bob Bedoll. 8/18/18.
//  9/1/18. reissue request for tomorrow if necessary to get set time.
//
//  API REST CALL: http://api.usno.navy.mil/rstt/oneday?date=today&loc=Seattle,%20WA
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
// SOMETIMES I GET:
//"nextmoondata":[
//            {"phen":"S","time":"12:25 a.m. DT"}],
// BUT SOMETIMES I HAVE TO REISSUE THE REQUEST FOR TOMORROW AND USE THE SET TIME IN MOONDATA.
//"closestphase":{"phase":"First Quarter","date":"August 18, 2018","time":"12:48 a.m. DT"},
// SOMETIMES I GET (if it is not a full moon):
//  "fracillum":"64%",
// SOMETIMES I GET:
//  "curphase":"Waning Gibbous"
//}
//
$file = "moondata.txt";
chdir("/home/postersw/public_html");  // move to web root

// code to get moon rise/set & phase

date_default_timezone_set("America/Los_Angeles"); // set PDT
$link = "http://api.usno.navy.mil/rstt/oneday?loc=Seattle,%20WA&date=today";
$linktomorrow = "http://api.usno.navy.mil/rstt/oneday?loc=Seattle,%20WA&date=tomorrow";

//$ts = time();
//$y = date("Y", $ts); // year, e.g. 2017
//$m = date ("m", $ts); // month with leading zero
//$d = date("d", $ts); // day with leading zero
//$link = $link . $m . "/" . $d . "/" . $y;
echo $link; //debug


// Get the moon data - retry 10 times
$str = GetData($link);
//$strtomorrow = GetData($linktomorrow);
//for ($x = 0; $x <= 10; $x++) {
//    $str = "";
//    $str = file_get_contents($link);
//    if($str != "") break;
//}
//if($str == "") {  // if no data
//    echo("moon cron run: NO usno.navy.mil moon DATA after 10 tries!!!");
//    return 0;
//}


// reformat the reply
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
//  NOTE: will issue a request to navy.mil for tomorrow data if necessary to get set time
//  NOTE: Rise time is always in the 'today' data (moondata "R").
//      Set time could be in today data (moondata "S") after rise, OR
//          today data (nextmoondata "S") OR
//          tomorrow data HTML request  (moondata "S")

function FormatHtml($reply) {
    global $linktomorrow;
    $jreply = json_decode($reply);  // decode the json reply
    if(is_null($jreply)) echo "<br/>Error decoding Today json data<br/>";

    // find rise time today:
    $times = "";
    $risetime = "";
    $settime = "";
    for($i=0; $i<count($jreply->moondata); $i++) {
        switch ($jreply->moondata[$i]->phen) {
            case "R":
                if($times <> "") $times = $times . "|";
                $times = "$times Rise: {$jreply->moondata[$i]->time} ";
                break;
            case "S":
                if($times <> "") $times = $times . "|";
                $times = "$times Set: {$jreply->moondata[$i]->time} ";
                $settime = $jreply->moondata[$i]->time;
        }
    }
    // if an error
    if ($times == "") {
        echo "ERROR - no moon rise returned. \n";
        die( "ERROR - no moon rise returned.");
    } else echo "<br/>Moon Times: $times <br/>";


    // not setting today. find set time tomorrow.
    if($settime == "") {
        for($i=0; $i<count($jreply->nextmoondata); $i++) {
            if($jreply->nextmoondata[$i]->phen == "S") {
                $times = "$times | Set: {$jreply->nextmoondata[$i]->time} ";
                echo "<br/>Moon sets tomorrow $times<br/>";
                break;
            }
        }
    }

    //// no set time for tomorrow present in the reply, so get and use tomorrow data. Turned off 9/2,.
    //if($settime == "") {
    //    echo "<br/>Getting moon data for tomorrow 1<br/>";
    //    $str = GetData($linktomorrow);  // ask the navy for tomorrows data which will have the set time for tonight
    //    if($str == "" || $str==false) echo "<br/>No tomorrow data returned for $link <br/>";
    //    $jreplytomorrow = json_decode($str);  // decode the json reply
    //    if(is_null($jreplytomorrow)) echo "<br/>Error decoding Tomorrow json data<br/>";
    //    for($i=0; $i<count($jreplytomorrow->moondata); $i++) {
    //        if($jreplytomorrow->moondata[$i]->phen == "S") {
    //            $settime = $jreplytomorrow->moondata[$i]->time;
    //            echo "<br/>Moon sets tomorrow $settime<br/>";
    //            break;
    //        }
    //    }
    //}

    // get phase and icon
    $pct =  $jreply->fracillum;
    $phase = "";
    $phase = $jreply->curphase;
    if($phase == "") $phase = $jreply->closestphase->phase;
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
    //  final return;
    if($pct == "") $pct = $mp; // if no pct, use the phase
    $s = "<br/>Moon:<span style='font-weight:normal'> <img style='vertical-align:middle' src='img/$icon.png' width=30 height=30> $pct, $times</span> ";
    // fix times
    $s = str_replace(" a.m.", "a", $s);
    $s = str_replace(" p.m.", "p", $s);
    $s = str_replace("DT", "", $s);
    $s = str_replace("ST", "", $s);
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
    }
    echo("<br/>moon cron run: NO usno.navy.mil moon DATA after 10 tries for $link<br/>");
    return "";
}

?>