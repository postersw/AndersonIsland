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

$ver = "1.22";  // 1/7/21
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
$mi = "<i class='material-icons mptext'>";
$ri = "";
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
//if($_GET["test"]=="test") testme();
$lt = localtime();   // time in UTC:  [2] = hour, [3]=min
//echo " hour=" . $lt[2] . " ";
if($lt[2]>7 && $lt[2]<12) exit(0); //DEBUG"time");  // don't run midnight - 4 (7-12 UTC)

// get position
$fa = getposition();
$p = ""; $pi = 0; $pstr = "";
//print_r($fa); // debug

//// loop through reply. There will be 0, 1, or 2 rows (1 row/ferry)
//foreach($fa as $a) {
//    $MMSI = $a[0]; $lat = $a[3]; $long = $a[4]; $speed = $a[5]; $course = $a[7]; $status = $a[8]; $timestamp = $a[9];
//    if($MMSI=="")  abortme("MMSI is empty");
//    if($MMSI == $MMSICA) $ferryname = "'CA'";
//    elseif($MMSI == $MMSIS2) $ferryname = "'S2'";
//    checktimestamp($timestamp); 
//    //echo " mmsi=$MMSI, lat=$lat, long=$long, speed=$speed, course=$course, status=$status, timestamp=$timestamp, ";
//    //if($status != 0) continue; // skip if not normal. Doesn't work because transponder status is not set correctly
//    if($long < $longMIN || $long > $longMAX || $lat < $latMIN || $lat > $latMAX) continue; // if outside boundaries

//    // calculate location and arrival;
//    if($p <> "") {
//        $p = $p . "<br/>";
//        $pi = $pi . "<br/>";
//    }
//    if($speed < 10) $s = reportatdock();  // at dock if speed< 1 knot
//    else $s = timetocross();
//    $p =  $p . "$ferryname $s"; 
//    $pi = $pi . "$mi$ri</i> $ferryname $s";
//}

// loop through reply. There will be 0, 1, or 2 rows (1 row/ferry)
$i = 0; $pj = 0;
$px = array();  // text string
for($i=0; $i < count($fa); $i++) {
    $MMSI = $fa[i][0]; $lat = $fa[i][3]; $long = $fa[i][4]; $speed = $fa[i][5]; $course = $fa[i][7]; 
    $status = $fa[i][8]; $timestamp = $fa[i][9];
    if($MMSI=="")  abortme("MMSI is empty");
    if($MMSI == $MMSICA) $ferryname = "'CA'";
    elseif($MMSI == $MMSIS2) $ferryname = "'S2'";
    checktimestamp($timestamp); 
    //echo " mmsi=$MMSI, lat=$lat, long=$long, speed=$speed, course=$course, status=$status, timestamp=$timestamp, ";
    //if($status != 0) continue; // skip if not normal. Doesn't work because transponder status is not set correctly
    if($long < $longMIN || $long > $longMAX || $lat < $latMIN || $lat > $latMAX) continue; // if outside boundaries

    // calculate location and arrival;
    if($speed < 10) $s = reportatdock();  // at dock if speed< 1 knot
    else $s = timetocross();
    $p =  $p . "$ferryname $s"; 
    $px[$pi] = "$mi$ri</i> $ferryname $s";
    $pi++;
}

// always display 'docked at Steilacoom' last and in gray if there are 2 ferries
if($pi==0) $pstr = "";
elseif($pi==1) $pstr = $px[0];
elseif(index($px[0], "docked at Steilacoom") > 0) $pstr = $px[1] . "<br/><span style='color:gray'>" . $px[0] . "</span>";
elseif(index($px[1], "docked at Steilacoom") > 0) $pstr = $px[0] . "<br/><span style='color:gray'>" . $px[1] . "</span>";
else $pstr = $px[0] . "<br/>" . $px[1];

// write to ferry position file
file_put_contents($ferrypositionfile, "<div style='font-family:sans-serif;font-size:smaller'>Ferry $p</div>");  // html file for iframe
file_put_contents("ferryposition.txt", $pstr); // txt file for getalerts.php
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
//  returns string indicating remaining time by computing percentage of crossing completed and multiplying by 20 min.
//          adjusts time by deltamin, which is how old the data is.
//          if time < 1, returns 'docking...'
//          $ri = return icon;
// note when long is outside of the docking zone and speed is not > 10, make crossing time slower
//
function timetocross() {
    global $MMSI, $lat, $long, $longAI, $longSt,$latAI,$latSt,$latKe,$longKe, $crossingtime, $course, $deltamin, $ferryport, $speed, $mi, $ri;
    $AItoSt = .074; // steilacoom to AI longitude
    $latKeIs = 47.1725; // Ketron course latitude flag. Just south of the Steilacoom dock
    $ketron = "";
    // if above the tip of ketron but headed SE, OR
    // if below the tip of Ketron, do a general stopping  with estimated arrival based on latitude, or leaving based on course
    if(($lat <= $latKeIs) || ($lat<47.177 && $long>-122.640 && $long<-122.624 && $course>100 && $course <180 )) { // if southerly westerly course, assume arriving.
    //if($lat <= $latKeIs) { 
        if(($long<$longKe) || ($course>110 & $course < 340))  { // if southerly westerly course, assume arriving.
            $ri = "file_download";
            $t = floor(abs(($lat-$latKe)/(47.177-$latKe)) * 10);  // min left based on latitude left
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
    // override ferry port when close to steilacoom and course is headed to steilacoom
    if($long > -122.615 && $long < -122.600 && $course > 90 && $course < 200) $ferryport = "A"; 

    // Headed to AI
    if($ferryport=="S" ) { // headed to AI 
        //$Dt = floor((($long-$longAI)/ $AItoSt ) * $crossingtime - $deltamin);  // longitude only
        $t = floor(((abs($long-$longAI) + abs($lat-$latAI)*.67)/ $DAItoSt ) * $crossingtime - $deltamin);  // latitude & longitude
        //echo " AItoST=$AItoSt, DATtoSt=$DAItoSt, Dt=$Dt ";
        $ri = "reply"; //"fast_rewind";
        if($t <=0) {
            if($speed >=80) $t = 1;  // if boat is still running at full speed, always give 1 more minute
            else {
                $ri = "skip_previous"; //"arrow_drop_down_circle";
                return "docking @Anderson";
            }
        }
        return $ketron . "arriving @AI in $t m";

    // Headed to Steilacoom
    } else { 
        //$Dt = floor(($longSt-$long)/ $AItoSt * $crossingtime - $deltamin); // longitude only
        $t = floor(((abs($longSt-$long) + abs($latSt-$lat)*.67)/ $DAItoSt ) * $crossingtime - $deltamin);  // latitude & longitude
        //echo " AItoST=$AItoSt, DATtoSt=$DAItoSt, Dt=$Dt ";
        $ri = "forward"; //"fast_forward";
        if($t <= 0) {
            if($speed >=80) $t = 1;  // if boat is still running at full speed, always give 1 more minute
            else {
                $ri = "skip_next"; //"arrow_drop_down_circle";
                return "docking @Steilacoom";
            }
        }    
        return $ketron . "arriving @Steilacoom in $t m";
    }
}


/////////////////////////////////////////////////////////////////////////
// reportatdock - reports the dock position. called when speed < 1 knot.
//  exit    returns at AI, at Steilacoom, or At Ketron based only on longditude
//          Also writes the port location (A/S) into the file named for the boat MMSI number
//          $ri = return icon;

function reportatdock() {
    global $MMSI, $lat, $long, $longAI, $longSt, $longKe, $longE, $mi, $ri, $lt;
    $latKeIs = 47.167; // north tip of ketron //$longKe = -122.6289;
    if($long > ($longAI-$longE) && $long < ($longAI+$longE))  {  // At AI
        file_put_contents($MMSI, "A");
        $ri = "font_download";
        return "docked at Anderson";
    } elseif($long > ($longSt-$longE) && $long < ($longSt+$longE))  {
        file_put_contents($MMSI, "S");
        $ri = "home";
        return "docked at Steilacoom";
    } elseif($long > ($longKe-.001) && $long < ($longKe+.002) && ($lat < $latKeIs) ) {
        // special case for monday morning ketron run that is steilacoom-ketron-steilacoom onlyh
        if($lt[2]>=16 && $lt[2]<=17) file_put_contents($MMSI, "A");  //  if 9am, always pretend it came from anderson so it will report returning to Steilacoom
        $ri = "do_not_disturb_on";
        return "at Ketron";  // allow for extended docking
    } else {
        $ri = "report";
        return "stopped at $lat, $long";
    }
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

/////////////////////////////////////////////////////////////////////////
// testme - call routines for each set of coordinates
//
function testme() {
global $lat, $long, $speed, $course, $deltamin;
// x = array(lat, long, speed, course, ...
//1-05T19:21:02+00:00 1.19 367153930,0,5125754,47.176970,-122.644900,113,511,102,0,2021-01-05T19:19:43,TER,42/ / deltamin=1.3:'S2' arriving @Steilacoom in 10 m 
//2021-01-05T19:24:02+00:00 1.19 367153930,0,5125754,47.173740,-122.628700,107,511,125,0,2021-01-05T19:23:23,TER,22/ / deltamin=0.7:'S2' arriving @Steilacoom in 6 m 
//2021-01-05T19:27:02+00:00 1.19 367153930,0,5125754,47.168220,-122.626100,112,511,181,0,2021-01-05T19:25:22,TER,22/ / deltamin=1.7:'S2' stopping @Ketron in 4 min 
$x = array(47.176970,-122.644900,113,102,47.173740,-122.628700,107,125,47.168220,-122.626100,112,181);
$deltamin = 0;
$i = 0;
for($i=0; $i<count($x); $i=$i+4) {
    $lat = $x[$i]; $long=$x[$i+1];$speed=$x[$i+2];$course=$x[$i+3];
    $s = timetocross();
    echo "$lat, $long, $speed, $course: $s \n";
}
exit("test complete");
}

//////////////////////////////////////////////////////////////////////////
// abortme(msg) - deletes ferry position file and exists with message
//  entry msg = message
//  does not return. exits the script.
//
function abortme($msg) {
    global $ferrypositionfile, $fa, $log,$MMSI,$lat,$long,$speed,$course,$timestamp,$deltamin,$ver;
    if (file_exists($ferrypositionfile)) unlink($ferrypositionfile);
    if (file_exists("ferryposition.txt")) unlink("ferryposition.txt");
    $tlh = fopen($log, 'a');
    fwrite($tlh, date('c') ." $ver " . print_r($fa, true) . ", deltamin=" . round($deltamin,1) . ": ABORT $msg \n");
    fclose($tlh);
    if($msg == "0 length array") exit(); // no message if 0 length
    print_r($fa);
    exit($msg);
}

?>