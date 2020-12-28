<?php
//////////////////////////////////////////////////////////////////////////////
// getferrypositioncron.php - retrieves current ferry position and calculates an eta.
// leaves the results in <ferryposition.txt> file.  This will be picked up by the getalert script
// which will tuck it into the FERRY/FERRYEND alert messages.
// Ferry position is retrieved from marinetraffic.com.  
//
//  Files: ferryposition.txt = message to display.
//          <mmsi>port.txt = S if last port was Steilacoom, A if last port was AI.
//
//  Robert Bedoll. 12/26/20.  
//
$longAI = -122.677; $latAI = 47.17869;   // AI Dock
$longSt = -122.603; $latSt = 47.17347;  // Steilacoom Dock
$longKe = -122.6289; $latKe = 47.1622; // ketron dock
$longE = -.0004;  $latE = .001; // epselon
$ferrypositionfile = "ferryposition.html";
$log = 'ferrypositionlog.txt';
$ferryportfile = "ferryport.txt";
$crossingtime = 21; // nominal crossing time in minutes
$MMSICA = 366659730;  // Christine anderson
$MMSIST = 367153930; // Steilacoom II
$MMSI = $MMSIST; // use steilacoom
$APIkey = "e5c425e79c24d1c960955f251b0146e361eca917";

// instantanious position retrieved from maringtraffic.com
$speed = 0;  // speed in  knots tenths
$lat = 0;
$long = 0;
$course = 0;
$timestamp = 0;
$deltamin = 0;  // minutes after timestamp

// start
//echo "started <br/>";
chdir("/home/postersw/public_html");  // move to web root
date_default_timezone_set("UTC"); // set UTC
$lt = localtime();
//echo " hour=" . $lt[2] . " ";
if($lt[2]>7 && $lt[2]<12) exit(0); //DEBUG"time");  // don't run midnight - 4 (7-12 UTC)

// get position
$ferryport = file_get_contents($ferryportfile);
getposition();
checktimestamp($timestamp); // if ($timestamp diff > 5 min) clear file;

// calculate location and arrival;
if($speed < 10) $p = reportatdock();  // if LT 1 knots report at dock
else $p = timetocross();
//echo "$p"; // debug

// write to ferry position file
file_put_contents($ferrypositionfile, "<div style='font-family:sans-serif;font-size:smaller'>Ferry $p</div>");
$tlh = fopen($log, 'a');
fwrite($tlh, date('c') . " 1.4 mmsi=$MMSI, lat=$lat, long=$long, speed=$speed, course=$course, timestamp=$timestamp, deltamin=" . round($deltamin,1) . ": $p \n");
close($tlh);
return;

///////////////////////////////////////////////////////////////////////////
// checktimnestamp - checks time stamp (UTC) to see if it is more than 5 min old
// This is unnecessary now beccause the call excludes all data older than 10
//  entry   timestamp
//  returns exits if > 12 min old
function checktimestamp($ts) {
    global $deltamin;
    date_default_timezone_set("UTC"); // set UTC
    $t = strtotime($ts);
    if($t == false) abortme("time stamp cannot be parsed");
    $tnow = time(); // current UTC time
    // debug
    $deltamin = ($tnow - $t)/60;
    //echo (" deltamin=$deltamin, ");
    // if no data in 12 minutes, delete it
    if($deltamin > 12) abortme("data is $deltamin old. file deleted.");
}

////////////////////////////////////////////////////////////////////////
// timetocross compute and return remaining time to port
//  entry   boat in route
//  returns remaining time by computing percentage of crossing completed and multiplying by 20 min.
//          adjusts time by deltamin, which is how old the data is.
// note when long is outside of the docking zone and speed is not > 10, make crossing time slower

function timetocross() {
    global $lat, $long, $longAI, $longSt,$latKeIs, $crossingtime, $course, $deltamin, $ferryport;
    $AItoSt = .074; // steilacoom to AI longitude
    $latKeIs = 47.170; // north tip of ketron
    $ketron = "";
    // if below the tip of Ketron, do a general stopping at or leaving
    if($lat <= $latKeIs) {
        if($course>110 & $course < 270)  return "stopping at Ketron";
        else $ketron = "leaving Ketron, ";
    }
    // if $ferryport=S, then ferry is headed to AI, else the reverse;

    if($ferryport=="S" ) { //|| $course > 190  || $course < 25) { 
        $t = floor((($long-$longAI)/ $AItoSt ) * $crossingtime - $deltamin);
        if($t <= 0) return "at Anderson Is";
        return $ketron . "arriving @AI in $t min";
    } else {
        $t = floor(($longSt-$long)/ $AItoSt * $crossingtime - $deltamin);
        if($t <= 0) return "at Steilacoom";
        return $ketron . "arriving @Steilacoom in $t min";
    }
}


/////////////////////////////////////////////////////////////////////////
// reportatdock - reports the dock position
//  exit    returns at AI, at Steilacoom, or At Ketron based only on longditude
function reportatdock() {
    global $lat, $long, $longAI, $longSt, $longKe, $longE, $ferryportfile;
    $latKeIs = 47.167; // north tip of ketron
    if($long < ($longAI-$longE) && $long > ($longAI+$longE))  {  // At AI
        file_put_contents($ferryportfile, "A");
        return "docked at Anderson Is";
    } elseif($long < ($longSt-$longE) && $long > ($longSt+$longE))  {
        file_put_contents($ferryportfile, "S");
        return "docked at Steilacoom";
    } elseif($long < ($longKe-$longE) && $long > ($longKe+$longE) && ($lat < $latKeIs) ) return "at Ketron";
    else return "stopped at $lat, $long";
}


///////////////////////////////////////////////////////////////
//  getposition - get the ferry position from marinetraffic.com
//  exit - sets globals speed, lat, long, course, timestamp
//          if no data, deletes the ferry position file.
function getposition() {
    global $lat,$long,$speed,$course,$timestamp,$MMSI,$APIkey;
    $link = "https://services.marinetraffic.com/api/exportvessels/v:8/$APIkey/timespan:10/protocol:json";
    $d = GetData($link);
    if( is_null($d) || $d == "") abortme("no data");

    // echo $d . "//"; // debug
    $a = json_decode($d); // unpack CSV data into array
    if(is_null($a)) abortme("null json decode");
    // var_dump($a);  // debug
    $numa = count($a);
    if($numa==0)  abortme("0 length array");
    if($numa > 1) echo "$numa rows of Data. now what?:: $d";

    // if there are > 1, I need to find the true working row.  Except for summer when there are 2 runs.
    $a = $a[0];
    $MMSI = $a[0];
    $lat = $a[3];
    $long = $a[4];
    $speed = $a[5];
    $course = $a[7];
    $timestamp = $a[9];
    // debug
    if($MMSI=="")  abortme("MMSI is empty");
    //echo " mmsi=$MMSI, lat=$lat, long=$long, speed=$speed, course=$course, timestamp=$timestamp, ";
}

///////////////////////////////////////////////////////////////////////////////
// Get data after 3 tries
//  Entry   link = address
//  returns data or it aborts
//      if no data, it deletes ferrypositionfile.
function GetData($link) {
    for ($x = 0; $x <= 3; $x++) {
        $str = "";
        //echo " GetData $x. ";
        $str = file_get_contents($link);
        //echo "str=$str";
        if($str != false && $str != "") {return $str;}
        sleep(10);
        echo " GetData Try $x. ";
    }
    abortme ("<br/>getferryposition cron run: NO marinetraffic.com DATA after 2 tries for $link<br/>");
}

//////////////////////////////////////////////////////////////////////////
// abortme(msg) - deletes ferry position file and exists with message
//  entry msg = message
//  does not return. exits the script.
function abortme($msg) {
    global $ferrypositionfile, $log,$MMSI,$lat,$long,$speed,$course,$timestamp,$deltamin;
    unlink($ferrypositionfile);
    $tlh = fopen($log, 'a');
    fwrite($tlh, date('c') . " mmsi=$MMSI, lat=$lat, long=$long, speed=$speed, course=$course, timestamp=$timestamp, deltamin=" . round($deltamin,1) . ": $msg \n");
    close($tlh);
    exit($msg);
}

?>