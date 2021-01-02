<?php
//////////////////////////////////////////////////////////////////////////////
// getferrypositioncron.php - retrieves current ferry position and calculates an eta.
// leaves the results in <ferryposition.txt> file.  This will be picked up by the getalert script
// which will tuck it into the FERRY/FERRYEND alert messages.
// Ferry position is retrieved from marinetraffic.com.  
//
//  Files: ferryposition.txt = message to display.
//          <mmsi> = S if last port was Steilacoom, A if last port was AI.
//
//  MarineTraffic.com data by subscription for Robert Bedoll:
//  MMSI, IMO, SHIP_ID, LAT, LON, SPEED, HEADING, COURSE, STATUS, TIMESTAMP, DSRC, UTC_SECONDS
//  [["304010417","9015462","359396","47.758499","-5.154223","74","329","327","0","2017-05-19T09:39:57","TER","54"],...]
//
//  Robert Bedoll. 12/26/20.  
//  1.12 12/30  Include latitude in calculation

$ver = "1.17";  // 1/1/21
$longAI = -122.677; $latAI = 47.17869;   // AI Dock
$longSt = -122.603; $latSt = 47.17347;  // Steilacoom Dock
$longKe = -122.6289; $latKe = 47.1622; // ketron dock
$longE = .0007;  $latE = .001; // epselon big enough to capture the 2 steilacoom docks
$longMIN = -122.7; $longMAX = -122.5; // longitude bounding
$latMIN = 47.15; $latMAX = 47.2;  // latitude bounding
$ferrypositionfile = "ferryposition.html";
$log = 'ferrypositionlog.txt';
$crossingtime = 20; // nominal crossing time in minutes
$MMSICA = 366659730;  // Christine Anderson
$MMSIS2 = 367153930; // Steilacoom II
$MMSI = $MMSIST; // use steilacoom
$APIkey = "e5c425e79c24d1c960955f251b0146e361eca917";  // subscription key from MarineTraffic.com
$ferryname = ""; // based on MMSI
$fa = []; // ferry array, 0, 1, or 2 arrays of data
$debug = false;

// instantanious position retrieved from maringtraffic.com
$speed = 0;  // speed in  knots tenths
$lat = 0;
$long = 0;
$course = 0;
$timestamp = 0;
$deltamin = 0;  // minutes after timestamp
$status = 0;  // 0 = normal, other=wierd

// start
//echo "started <br/>";
chdir("/home/postersw/public_html");  // move to web root
date_default_timezone_set("UTC"); // set UTC
$lt = localtime();
//echo " hour=" . $lt[2] . " ";
if($lt[2]>7 && $lt[2]<12) exit(0); //DEBUG"time");  // don't run midnight - 4 (7-12 UTC)

// get position
$fa = getposition();
$p = "";
//print_r($fa); // debug

// loop through reply. There will be 0, 1, or 2 rows (1 row/ferry)
foreach($fa as $a) {
    $MMSI = $a[0]; $lat = $a[3]; $long = $a[4]; $speed = $a[5]; $course = $a[7]; $status = $a[8]; $timestamp = $a[9];
    if($MMSI=="")  abortme("MMSI is empty");
    if($MMSI == $MMSICA) $ferryname = "'CA'";
    elseif($MMSI == $MMSIS2) $ferryname = "'S2'";
    checktimestamp($timestamp); 
    //echo " mmsi=$MMSI, lat=$lat, long=$long, speed=$speed, course=$course, status=$status, timestamp=$timestamp, ";
    //if($status != 0) continue; // skip if not normal. Doesn't work because transponder status is not set correctly
    if($long < $longMIN || $long > $longMAX || $lat < $latMIN || $lat > $latMAX) continue; // if outside boundaries

    // calculate location and arrival;
    if($p <> "") $p = $p . "<br/>";
    $p = $p . "$ferryname ";
    if($speed < 10) $p =  $p . reportatdock();  // if LT 1 knots report at dock
    else $p = $p . timetocross();
}

//echo "$p"; // debug

// write to ferry position file
file_put_contents($ferrypositionfile, "<div style='font-family:sans-serif;font-size:smaller'>Ferry $p</div>");  // html file for iframe
file_put_contents("ferryposition.txt", $p); // txt file for getalerts.php
$tlh = fopen($log, 'a');
$s =  implode(",", $fa[0]) . "/" ;
if(count($fa)>1) $s = $s . implode(",", $fa[1])  ;
fwrite($tlh, date('c') . " $ver $s / deltamin=" . round($deltamin,1) . ":$p \n");
fclose($tlh);
return;

///////////////////////////////////////////////////////////////////////////
// checktimnestamp - checks time stamp (UTC) to see if it is more than 5 min old
// This is unnecessary now beccause the call excludes all data older than 10
//  entry   timestamp
//  returns $deltamin = how old the data is, in min.
//
function checktimestamp($ts) {
    global $deltamin;
    $t = strtotime($ts);
    if($t == false) abortme("time stamp cannot be parsed");
    $tnow = time(); // current UTC time
    $deltamin = ($tnow - $t)/60;
    //echo (" deltamin=$deltamin, ");
    // if no data in 12 minutes, delete it. unnecessary because call filters it out.
    //if($deltamin > 12) abortme("data is $deltamin old. file deleted.");
}

////////////////////////////////////////////////////////////////////////
// timetocross compute and return remaining time to port
//  entry   boat in route
//  returns remaining time by computing percentage of crossing completed and multiplying by 20 min.
//          adjusts time by deltamin, which is how old the data is.
// note when long is outside of the docking zone and speed is not > 10, make crossing time slower
//
function timetocross() {
    global $MMSI, $lat, $long, $longAI, $longSt,$latAI,$latSt,$latKe, $crossingtime, $course, $deltamin, $ferryport, $speed;
    $AItoSt = .074; // steilacoom to AI longitude
    $latKeIs = 47.1725; // Ketron course latitude flag. Just south of the Steilacoom dock
    $ketron = "";
    // if below the tip of Ketron, do a general stopping  with estimated arrival based on latitude, or leaving based on course
    if($lat <= $latKeIs) { // if southerly westerly course, assume arriving.
        if($course>110 & $course < 340)  {
            $t = floor(abs(($lat-$latKe)/($latKeIs-$latKe)) * 8);  // min left based on latitude left
            if($t <= 0) {
                if($speed > 50) $t = 1;// if boat > 5 knts, give 1 more minute
                else return "docking at Ketron";
            }
            return "stopping @Ketron in $t min";
        }
        else $ketron = "leaving Ketron, ";
    }
    // $ct = $crossingtime;
    // if($speed < 100) $ct = $ct * (100/$speed); // adjust for speed. numbers could get too big.

    $ferryport = file_get_contents($MMSI); // get last ferry port
    $DAItoSt = ($longSt-$longAI) + ($latAI-$latSt)*.67; // total distance in longitude equivalent degrees
    // if $ferryport=S, then ferry is headed to AI, else the reverse;
    if($ferryport=="S" ) { //|| $course > 190  || $course < 25) { 
        //$Dt = floor((($long-$longAI)/ $AItoSt ) * $crossingtime - $deltamin);  // longitude only
        $t = floor(((abs($long-$longAI) + abs($lat-$latAI)*.67)/ $DAItoSt ) * $crossingtime - $deltamin);  // latitude & longitude
        //echo " AItoST=$AItoSt, DATtoSt=$DAItoSt, Dt=$Dt ";
        if($t <=0) {
            if($speed >=80) $t = 1;  // if boat is still running at full speed, always give 1 more minute
            else return "docking at Anderson Is";
        }
        return $ketron . "arriving @AI in $t min";
    } else {
        //$Dt = floor(($longSt-$long)/ $AItoSt * $crossingtime - $deltamin); // longitude only
        $t = floor(((abs($longSt-$long) + abs($latSt-$lat)*.67)/ $DAItoSt ) * $crossingtime - $deltamin);  // latitude & longitude
        //echo " AItoST=$AItoSt, DATtoSt=$DAItoSt, Dt=$Dt ";
        if($t <= 0) {
            if($speed >=80) $t = 1;  // if boat is still running at full speed, always give 1 more minute
            else return "docking at Steilacoom";
        }
        return $ketron . "arriving @Steilacoom in $t min";
    }
}


/////////////////////////////////////////////////////////////////////////
// reportatdock - reports the dock position. called when speed < 1 knot.
//  exit    returns at AI, at Steilacoom, or At Ketron based only on longditude
//          Also writes the port location (A/S) into the file named for the boat MMSI number
//
function reportatdock() {
    global $MMSI, $lat, $long, $longAI, $longSt, $longKe, $longE;
    $latKeIs = 47.167; // north tip of ketron //$longKe = -122.6289;
    if($long > ($longAI-$longE) && $long < ($longAI+$longE))  {  // At AI
        file_put_contents($MMSI, "A");
        return "docked at Anderson Is";
    } elseif($long > ($longSt-$longE) && $long < ($longSt+$longE))  {
        file_put_contents($MMSI, "S");
        return "docked at Steilacoom";
    } elseif($long > ($longKe-.001) && $long < ($longKe+.002) && ($lat < $latKeIs) ) return "at Ketron";  // allow for extended docking
    else return "stopped at $lat, $long";
}


///////////////////////////////////////////////////////////////
//  getposition - get the ferry position from marinetraffic.com
//  exit - returns json array of 0, 1, or 2 elements
//
function getposition() {
    global $APIkey;
    $link = "https://services.marinetraffic.com/api/exportvessels/v:8/$APIkey/timespan:10/protocol:json";
    $d = file_get_contents($link);
    if( is_null($d) || $d == "") abortme("no data, $link");

    // echo $d . "//"; // debug
    $fa = json_decode($d); // unpack CSV data into array
    if(is_null($fa)) abortme("null json decode");
    // var_dump($fa);  // debug
    $numa = count($fa);
    if($numa==0)  abortme("0 length array");
    return $fa;
}

//////////////////////////////////////////////////////////////////////////
// abortme(msg) - deletes ferry position file and exists with message
//  entry msg = message
//  does not return. exits the script.
//
function abortme($msg) {
    global $ferrypositionfile, $fa, $log,$MMSI,$lat,$long,$speed,$course,$timestamp,$deltamin,$ver;
    if (file_exists($ferrypositionfile)) unlink($ferrypositionfile);
    print_r($fa);
    $tlh = fopen($log, 'a');
    fwrite($tlh, date('c') ." $ver " . print_r($fa, true) . ", deltamin=" . round($deltamin,1) . ": ABORT $msg \n");
    fclose($tlh);
    if($msg == "0 length array") exit(); // no message if 0 length
    exit($msg);
}

?>