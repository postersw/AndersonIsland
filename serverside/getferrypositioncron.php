<?php
//////////////////////////////////////////////////////////////////////////////
// getferrypositioncron.php - retrieves current ferry position and calculates an eta.
//  Run every 3 min. by cron.
// leaves the results in <ferryposition.txt> file.  This will be picked up by the getalert script
// which will tuck it into the FERRY/FERRYEND alert messages.
// Ferry position is retrieved from marinetraffic.com.  Max allowed call rate is once/3 minutes.
//
//  Files: ferryposition.txt = message to display.
//         ferrypositionsave.json = saved and restored data in json format. in $SAVED[] as an associative array.
//                          [MMSI id] = last port for that boat
//                          [ferrystate] = last ferry state (toAI, atAI, toST, atST). Not valid if 2 boats running.
//                          [arrivaltimeAI|ST] = arrival time at that port, in min since midnight
//          ferryrunlog.txt = 1 line/ferry run with departure time and late status. ADDED 1/8/24.
//                           unixtimestamp,date,A/S,ONTIME/LATE,delaytime in min, next run time
//          (ferrylatelog.txt = 1 line per late ferry run. OBSOLETE 1/8/24)
//          ferrypositionlog.csv = 1 line/script run. For debugging.
//
//  MarineTraffic.com data by subscription for Robert Bedoll:
//  MMSI, IMO, SHIP_ID, LAT, LON, SPEED, HEADING, COURSE, STATUS, TIMESTAMP, DSRC, UTC_SECONDS
//  [["304010417","9015462","359396","47.758499","-5.154223","74","329","327","0","2017-05-19T09:39:57","TER","54"],...]
//
//  Robert Bedoll. 12/26/20.  
//  1.12 12/30/20  Include latitude in calculation
//  1.26 1/23/21   Skip boat if docked at backup-boat dock. Always display CA before S2 if both active.
//  1.27 2/1/21 Case of boat returning to AI. Check course for a return heading.
//  1.28 2/2/21 Make position log a csv file.
//  1.30 2/11/21 Use heading to determine next port if it is unambiguous. Otherwise use previous port.
//  1.32 5/25/21 Make font dark blue.
//  1.33 7/19/21 Make text italic
//  1.34 5/26/22 Make text red if ferry is delayed.
//  1.35 6/07/22 Detect a late ferry and add a LATE message
//  1.36 6/9/22. Use 30 minutes to find a late run.
//  1.37 6/9/22. Moved logging to subroutine.
//  1.38 10/7/22. Remove 3 min backoff wwhen at dock.
//  1.39 11/30/22. Add ETD for all runs. create persistant data in $SAVED/ferryjsonsave file.
//  1.40 12/5/22. Add "OnTime" to message.
//  1.41 12/9/22. Change ST to St for getferryoverflowcron script.   Change $loadtime to 5 min after 8 pm.
//  1.42 12/11/22. Change to use dailycache.txt for ferry schedule so we have the same run times as the app does.
//  1.43 12/12/22. Revise ketron latitude.
//  1.44 12/16/22. Add debug printouts under control of $debug to make it easy to turn on debug.
//  1.45 12/19/22  Improve Ketron times
//  1.46 1/20/23.  Change ketron times to not assume boat will always go to ST.  Use previous port to determine next port.
//  1.47 1/31/23.  Look back 50 minutes to find run time if At ST or At AI.
//  1.48 7/3/23.   Add 2 boat message if 2 boats and Fri, Sun, Mon
//  1.49 10/23/23. Change location of ferry times to 'ferryscheduleinclude.txt'
//  1.50 12/30/23. Change OnTime and Late msg to be white text on colored background.
//  1.51 1/7/24.   Add ferryrunlog.txt. 

$ver = "1.51"; // 12/30/23.
$longAI = -122.677; $latAI = 47.17869;   // AI Dock
$longSt = -122.603; $latSt = 47.17347;  // Steilacoom Dock
$longKe = -122.6289; $latKe = 47.1622; // ketron dock
$longE = .0007;  $latE = .001; // epselon big enough to capture the 2 steilacoom docks
$longMIN = -122.7; $longMAX = -122.5; // longitude bounding
$latMIN = 47.15; $latMAX = 47.2;  // latitude bounding
$DAItoSt = ($longSt-$longAI) + ($latAI-$latSt)*.67; // total distance in longitude equivalent degrees

$ferrypositionfile = "ferryposition.html";
$log = 'ferrypositionlog.csv';
$ferryalertfile = "alert.txt";
$ferryjsonsavefile = "ferrypositionsave.json";
$crossingtime = 20; // nominal crossing time in minutes
$MMSICA = 366659730;  // Christine Anderson
$MMSIS2 = 367153930; // Steilacoom II
$MMSI = $MMSIS2; // use steilacoom
$APIkey = "e5c425e79c24d1c960955f251b0146e361eca917";  // subscription key from MarineTraffic.com
$ferryname = ""; // based on MMSI
$fa = []; // ferry array, 0, 1, or 2 arrays of data
$mi = "<i class='material-icons mptext'>";
$ri = "";
$debug = false;
//$debug = true; /////////////////////////////////////DEBUG
if($debug) echo "$ver <br>";

// instantanious position retrieved from maringtraffic.com
$speed = 0;  // speed in  knots tenths
$lat = 0;
$long = 0;
$course = 0;
$timestamp = 0;
$deltamin = 0;  // minutes after timestamp
$status = 0;  // 0 = normal, other=wierd

// global status for use by checkforferrylate
$ferrystate = ""; // 1 =at ST, 2 =moving to AI, 3=at AI, 4=moving to ST
$timetoarrival = 0;  // minutes to arrival

// start
//echo "started <br/>";
chdir("/home/postersw/public_html");  // move to web root
date_default_timezone_set("UTC"); // set UTC
//if($_GET["test"]=="test") testme();
$lt = localtime();   // time in UTC:  [0]=sec,[1]=min,[2] = hour, [3]=minday,[4]=mon,[5]=year,[6]=weekay,[7]=yearday
//echo " hour=" . $lt[2] . " ";
if($lt[2]>7 && $lt[2]<12) exit(0); //DEBUG"time");  // don't run midnight - 4 (7-12 UTC)

// reload persistant data
$SAVED = json_decode(file_get_contents($ferryjsonsavefile), TRUE); // load persistant data
//var_dump($SAVED);  // print out what was loaded

// get position
$fa = getposition();
if($debug) echo ("ferry count = " . count($fa) . "<br>");
$p = ""; $pi = 0; $pstr = "";

// loop through reply. There will be 0, 1, or 2 rows (1 row/ferry)
$i = 0; $pj = 0;
$px = array();  // text string
for($i=0; $i < count($fa); $i++) {
    $MMSI = $fa[$i][0]; $lat = $fa[$i][3]; $long = $fa[$i][4]; $speed = $fa[$i][5]; $course = $fa[$i][7]; 
    $status = $fa[$i][8]; $timestamp = $fa[$i][9];
    if($MMSI=="")  abortme("MMSI is empty");
    if($MMSI == $MMSICA) $ferryname = "'CA'";
    elseif($MMSI == $MMSIS2) $ferryname = "'S2'";
    //if($ferryname == "'CA'") continue; // skip CA///////////////////////////////////////////////
    checktimestamp($timestamp); 
    //echo " mmsi=$MMSI, lat=$lat, long=$long, speed=$speed, course=$course, status=$status, timestamp=$timestamp, ";
    //if($status != 0) continue; // skip if not normal. Doesn't work because transponder status is not set correctly
    if($long < $longMIN || $long > $longMAX || $lat < $latMIN || $lat > $latMAX) continue; // if outside boundaries

    // calculate location and arrival;
    if($speed < 3 && $long >= -122.6036 && $long <= -122.6034) continue;  // skip boat if it is stopped & docked at the backup-boat dock
    if($speed < 10) $s = reportatdock();  // at dock if speed< 1 knot
    else $s = timetocross();
    $p =  $p . "$ferryname $s"; 
    $px[$pi] = "$mi$ri</i><i> $ferryname $s</i>";
    $pi++;
}
if($debug) echo "fa=" . count($fa) . ", pi=$pi<br>";

// always display 'CA' before 'S2' when both are active.

if($pi==0) $pstr = "";
elseif($pi==1) $pstr = $px[0];
elseif(strpos($px[0], "'S2'") > 0) $pstr = $px[1] . "<br/>" . $px[0];  // always display CA before S2
else $pstr = $px[0] . "<br/>" . $px[1];
//echo $pstr;

// if running 1 ferry and it is actually late, make the time red.
$ferrylate = "";
if($pi==1) {
    $ferrylate = checkforLateFerry();  //  if running 1 boat, calculate if ferry is late and add message
} elseif($pi==2) {
    $ferrylate = checkforTwoBoats();  // if running 2 boats, issue 2 boat msg on fri, sun, mon
}
$pstr = "$ferrylate<br><span style='color:darkblue'>&nbsp;&nbsp;$pstr</span>";  // build message as <pstr><ferrylate>

// save output and info
file_put_contents("ferryposition.txt", $pstr); // txt file for getalerts.php
$SAVED['message'] = $pstr;        // message for debugging
file_put_contents($ferryjsonsavefile, json_encode($SAVED));  // save persistant data,
logPosition($log);// log it to csv file 
return;

///////////////////////////////////////////////////////////////////////////
// checktimnestamp - checks time stamp (UTC) to get age of position data
// 
//  entry   timestamp
//  returns $deltamin = how old the data is, in min.
//
function checktimestamp($ts) {
    global $deltamin;
    $t = strtotime($ts);
    if($t == false) abortme("time stamp cannot be parsed");
    $tnow = time(); // current UTC time
    $deltamin = ($tnow-$t)/60; // age of data, in minutes, rounded
    //echo (" deltamin=$deltamin<br> ");
    // if no data in 12 minutes, delete it. unnecessary because call filters it out.
    //if($deltamin > 12) abortme("data is $deltamin old. file deleted.");
}

////////////////////////////////////////////////////////////////////////
// timetocross compute and return remaining time to port
//  entry   boat in route
//          $MMSI = id of boat
//  returns string indicating remaining time by computing percentage of crossing completed and multiplying by 20 min.
//          adjusts time by deltamin, which is how old the data is.
//          if time < 1, returns 'docking...'
//          $ri = return icon;
// note when long is outside of the docking zone and speed is not > 10, make crossing time slower
//
function timetocross() {
    global $MMSI, $lat, $long, $longAI, $longSt,$latAI,$latSt,$latKe,$longKe, $DAItoSt, $crossingtime, $course, $deltamin, $ferryport, $speed, $mi, $ri;
    global $ferrystate, $timetoarrival;
    global $SAVED; // persistant data

    $AItoSt = .074; // steilacoom to AI longitude
    $latKeIs = 47.1725; // Ketron course latitude flag. Just south of the Steilacoom dock
    $latKeNTip = 47.1673; // ketron northern tip
    $ketron = "";
    $ketronDockTime = 6; // avg ketron dock time
    $ketronToStTime = 8; // avg Ke to St time
    $ketronToAITime = 18; // avg Ke to AI time
    $ferryport = $SAVED[$MMSI];  // get last port based on ship id

    // Ketron Special Case:   (assumes any trip to Ketron then goes on to St)
    //   if below the tip of Ketron, OR if above the tip of ketron but headed SE
    //      do a general stopping  with estimated arrival based on latitude, or leaving based on course
    if(($lat <= $latKeNTip) || ($lat<47.177 && $long>-122.640 && $long<-122.624 && $course>100 && $course <180 )) { // if southerly westerly course, assume arriving.
        // set ferry state based on previous port. Assume ferry will continue to next port after Ketron.
        $ferrystate = ($ferryport=="A" ? "toST": "toAI"); // set ferry state based on previous port. Assume ferry will continue to next port after Ketron.
        if(($long<$longKe) || ($course>99 & $course < 340))  { // if southerly westerly course, assume arriving.
            $ri = "file_download";
            $t = floor(abs(($lat-$latKe)/(47.177-$latKe)) * 10);  // min left based on latitude left
            //    $timetoarrival =  floor($ketronDockTime + $ketronToStTime  - $deltamin); // time to arrival at steilacoom
            //    if($speed > 50) $t = 1;// if boat > 5 knts, give 1 more minute
            //    else return "docking @Ke, arriving @St in $timetoarrival m";
            if($ferrystate = "toST") {  // if headint to St
                $timetoarrival = floor($t + $ketronDockTime + $ketronToStTime - $deltamin); // time to arrival at steilacoom
                return "stopping @Ke in $t m, arriving @St in $timetoarrival m";
            } else {  // heading to AI
                $timetoarrival = floor($t + $ketronDockTime + $ketronToAITime - $deltamin); // time to arrival at steilacoom
                return "stopping @Ke in $t m, arriving @AI in $timetoarrival m";    
            }
        } else { 
            // if not arriving, it must be leaving
            if($ferrystate = "toST") {  // heading to St
                $timetoarrival = floor($ketronToStTime - $deltamin); // time to arrive in steilacoom
                return "leaving Ke, arriving @St in $timetoarrival m";
            } else {  // heading to AI
                $timetoarrival = floor($ketronToAITime - $deltamin); // time to arrive in steilacoom
                return "leaving Ke, arriving @AI in $timetoarrival m";    
            }
        }
    }

    // Default: if coming from S, it is headed to A, . Added 1.29 2/22/21
    // $courseto = destination port.  $ferryport = previous port
    if($ferryport=="S") $courseto = "A";
    else $courseto = "S";
    // override courseto when compass course is unambiguous
    if($course > 225 && $course < 351) $courseto = "A";  // heading to AI
    if($course > 35  && $course < 186) $courseto = "S";  // heading to steilacoom

    // Headed to AI
    if($courseto == "A") {
        $ferrystate = "toAI"; // travelling to AI
        //$Dt = floor((($long-$longAI)/ $AItoSt ) * $crossingtime - $deltamin);  // longitude only
        $t = floor(((abs($long-$longAI) + abs($lat-$latAI)*.67)/ $DAItoSt ) * $crossingtime - $deltamin);  // latitude & longitude
        $timetoarrival = max($t, 0);
        //echo " AItoST=$AItoSt, DATtoSt=$DAItoSt, Dt=$Dt ";
        $ri = "reply"; //"fast_rewind";
        if($t <=0) {
            if($speed >=80) $t = 1;  // if boat is still running at >8 knots, always give 1 more minute
            else {
                $ri = "skip_previous"; //"arrow_drop_down_circle";
                return "docking @AI";
            }
        }
        if($ferryport == "A") return "returning to AI in $t m";
        return $ketron . "arriving @AI in $t m";

    // Headed to Steilacoom
    } else {  // $courseto = "S"
        $ferrystate = "toST"; // headint to ST
        //$Dt = floor(($longSt-$long)/ $AItoSt * $crossingtime - $deltamin); // longitude only
        $t = floor(((abs($longSt-$long) + abs($latSt-$lat)*.67)/ $DAItoSt ) * $crossingtime - $deltamin);  // latitude & 
        $timetoarrival = max($t, 0);
        //echo " AItoST=$AItoSt, DATtoSt=$DAItoSt, Dt=$Dt ";
        $ri = "forward"; //"fast_forward";
        if($t <= 0) {
            if($speed >=80) $t = 1;  // if boat is still running at full speed, always give 1 more minute
            else {
                $ri = "skip_next"; //"arrow_drop_down_circle";
                return "docking @St";
            }
        }
        if($ferryport == "S") return $ketron . "returning to St in $t m";    
        return "arriving @St in $t m";
    }
}


/////////////////////////////////////////////////////////////////////////
// reportatdock - reports the dock position. called when speed < 1 knot.
//  entry   globals for ferry position are set
//  exit    returns at AI, at Steilacoom, or At Ketron based only on longditude
//          $ri = return icon;
//      SIDE AFFECTS
//          Also writes the port location (A/S) into the file named for the boat MMSI number
//          $ferrystate = atAI or atST
//          

function reportatdock() {
    global $MMSI, $lat, $long, $longAI, $latAI, $longSt, $latSt, $longKe, $longE, $mi, $ri, $lt, $DAItoSt;
    global $deltamin;
    global $ferrystate, $timetoarrival;
    global $SAVED;
    $ketronDockTime = 6; // avg ketron dock time
    $ketronToStTime = 8; // avg Ke to St time
    $ketronToAITime = 18; // avg Ke to AI time

    $latKeIs = 47.167; // north tip of ketron //$longKe = -122.6289;
    if($long > ($longAI-$longE) && $long < ($longAI+$longE))  {  
        // At AI
        $SAVED[$MMSI] = "A"; // remember last position of ferry
        $ri = "font_download";
        $ferrystate = "atAI";  // at AI
        return "docked @AI";

    } elseif($long > ($longSt-$longE) && $long < ($longSt+$longE))  {
        // At Steilacoom
        $SAVED[$MMSI] = "S"; 
        $ri = "home";
        $ferrystate = "atST";  // at steilacoom
        return "docked @St";

    } elseif($long > ($longKe-.001) && $long < ($longKe+.002) && ($lat < $latKeIs) ) {
        // At Ketron
        $ri = "do_not_disturb_on";
        //ferrystate = "toST"; // moving to ST;  disabled 1/20/23 to allow for stops in run from ST to AI
        if($SAVED[$MMSI] == "A"){  // if boat came from AI, assume it will continue to ST
            $ferrystate = "toST";
            $timetoarrival =  floor($ketronDockTime/2 + $ketronToStTime - $deltamin); // time to arrival at ST in minutes
            return "docked @Ke, arriving @St in $timetoarrival m";  // allow for extended docking
        } else {  // boat was at ST, assume it will go to AI
            $ferrystate = "toAI";
            $timetoarrival =  floor($ketronDockTime/2 + $ketronToAITime - $deltamin); // time to arrival at ST in minutes
            return "docked @Ke, arriving @AI in $timetoarrival m";  // allow for extended docking
        }

    } else {
        // stopped somewhere. Report distance to AI or Steilacoom
        $ri = "report";
        $ta = (((abs($long-$longAI) + abs($lat-$latAI)*.67)/ $DAItoSt ) * 4);  // approx miles to AI
        $ts = (((abs($longSt-$long) + abs($latSt-$lat)*.67)/ $DAItoSt ) * 4);  // approx miles to Steilacoom
        $ferrystate = "toAI"; // travelling to AI;
        $timetoarrival = 10;
        if($ta<$ts) return "stopped " . round($ta,1) . " miles from AI";

        // if closer to Steilacoom
        $ferrystate = "toST"; 
        $timetoarrival = 10;
        return "stopped " . round($ts,1) . " miles from Steilacoom";
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

/////////////////////////////////////////////////////////////////////////////
// logPosition - writes position to CSV file in 2 columns so it is easy to view with excel
//  I think this is strill wrong on 12/16/22 because px can have just 1 entry even if fa has 2 if boat is out of bounds
//  entry   $log = log file name
//          fa = array of ferry position  0, 1, or 2 entries
//          px = array of ferry messages
//  exit    writes to $log
//
function logPosition($log) {
    global $fa, $px, $MMSICA;
    global $ver, $deltamin;
    
    if(count($fa)==0) return; // if no poisiton data
    $tlh = fopen($log, 'a');  // append to log
    $s1 = ""; $s2 = "";
    //  this mess is to ensure that the boats are always in the same column if there are 2 boats
    if($fa[0][0] == $MMSICA) {  // if 1st boat is CA
        $s1 =  implode(",", $fa[0]) . "," . $px[0];
        if(count($fa)>1 && count($px)>1) $s2 = implode(",", $fa[1]) . "," . $px[1];
        else $s2 = ",,,,,,,,,,,,";
    } else  { // if 1st boat is S2
        if(strpos($px[0], "'S2'") < 0) {
            $t = $px[0]; $px[0] = $px[1]; $px[1] = $t;
        }
        $s2 =  implode(",", $fa[0]) . "," . $px[0];
        if(count($fa)>1 && count($px)>1) $s1 = implode(",", $fa[1]) . "," . $px[1];
        else $s1 = ",,,,,,,,,,,,";
    }
    date_default_timezone_set("America/Los_Angeles"); // set UTC
    fwrite($tlh, date('c') . ",$ver,$s1,$s2," . round($deltamin,1) ."\n");
    fclose($tlh);
}

//////////////////////////////////////////////////////////////////////////////
// check for late - check for ferry being late and adds a message. Writes 'ferryrunlog.txt' for statistics.
//      Don't call if running 2 ferrys. Its too confusing and $ferrystate is unsafe for multiple boats.
//  entry   globals $ferrystate = atST, toAI, atAI, toST.  NOT SAFE FOR MULTIPLE BOATS
//          $timetoarrival = min to arrival if state = toAI/toST
//  exit    returns prefix to ferry message: "LATE nn m for XX hh:mm run", or "ONTIME for XX hh:mm run", or ""
//          Writes ontime/late msg to ferryrunlog.txt if the ferry has left: datetime,S/A,Ontime/LATE,delay,scheduledruntime
//          When ferry leaves, writes to 'ferryrunlog.txt'
//  side effects:  if late, writes to stdout
//          $SAVED["ferrystate"] = ferry state: atST, atAI, toST, toAI
//          $SAVED["ferrivarrivaltimeAI"], "ferryarrivaltimeST" = arrival time in min since midnight
//          $SAVED["delaytime"] = $delaytime in min
//
//  Detecting CANCELLED runs: When the time of next run changes from the time of the run you are waiting on,
//      the run you are waiting on is assumed to be cancelled and is logged as a cancelled run.  
//      This should happen 51 minutes after the scheduled run time.
//
function checkforLateFerry() {
    global $ferrystate, $timetoarrival;
    global $SAVED;
    global $deltamin; // age of ferry status
    global $debug;
    
    if($debug) echo "ferrystate=$ferrystate<br>";
    if($ferrystate=="") return "";  // unable to determine state;
    $priorferrystate = $SAVED['ferrystate'];  // use the SAVE array to remember position
    $SAVED['ferrystate'] = $ferrystate;  // update current ferry state
    $traveltime = $timetoarrival; // time to travel AI-St or St-AI in minutes. Already adjusted by $deltamin.
    $loadtime = 8; // time to unload & load the ferry
    $dockingtime = 3; // time to dock the ferry and begin unloading - 1 3 min cycle
    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $loctime = localtime();  // returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday
    $now = $loctime[2] * 60 + $loctime[1]; // - 3;  // local time in minutes since midnight. 
    if($loctime[2] >= 20) $loadtime = 5; // if >8pm, load time is just 5 minutes. 
    $ferryarrivaltime = 0;

    // All arithmetic is done in minutes since midnight.
    switch($ferrystate) {
        case "atST": // docked at ST
            if($priorferrystate != "atST") {  // if ferry was coming to ST, it has just arrived, so save its arrival time
                $ferryarrivaltime = $now - floor($deltamin); // adjust for age of ferry position data
                $SAVED['ferryarrivaltimeST'] = $ferryarrivaltime;  //  file_put_contents("ferryarrivaltimeST", $now);
            } else $ferryarrivaltime = $SAVED['ferryarrivaltimeST'];  // file_get_contents("ferryarrivaltimeST");
            if($ferryarrivaltime > $now) $ferryarrivaltime = 0; // arrivaltime has to be before now. allow for end of day and wierd stuff
            $ETD = max($now, $ferryarrivaltime+$loadtime); // ETD = arrival time + load time.
            $nextrun = getTimeofNextRun("ST", 50);  // next run time in minutes-since-midnight. up to 50 minutes late for next run.
            $waitingforrun = $SAVED['waitingforrun'];
            // If the run we are waiting on changed in port, the previous run must have been cancelled. 
            if($waitingforrun!="") {
                if($nextrun>$waitingforrun) LogFerryRun("St", "CANCELLED", $waitingforrun); // prior run is cancelled
            }
            $SAVED['waitingforrun'] = $nextrun; // remember the run we are waiting on
            $delaytime = $ETD - $nextrun;
            $ferryport = "St";
            break;

        case "toAI": // travelling to AI
            if($priorferrystate == "atST") {  // if ferry just jeft ST
                LogFerryRun("ST");
            }
            $SAVED['waitingforrun'] = "";
            $nextrun = getTimeofNextRun("AI"); // next run time minutes since midnight second
            $ETD = $now + $traveltime + $loadtime + $dockingtime; // ESTIMATED TIME OF DEPARTURE. 
            $delaytime = $ETD - $nextrun;  // calculate delay    
            $ferryport = "AI";
            break;

        case "atAI": // docked at AI
            if($priorferrystate != "atAI") {  // if ferry was coming to AI, it has just arrived, so save its arrival time
                $ferryarrivaltime = $now -floor($deltamin);  // adjust for age of ferry position message
                $SAVED["ferryarrivaltimeAI"] = $ferryarrivaltime; //  file_put_contents("ferryarrivaltimeAI", $now);
            } else $ferryarrivaltime = $SAVED["ferryarrivaltimeAI"]; //$ferryarrivaltime = file_get_contents("ferryarrivaltimeAI");
            if($ferryarrivaltime > $now) $ferryarrivaltime = 0; // arrival time has to be before now. allow for end of day and wierd stuff
            $ETD = max($now, $ferryarrivaltime+$loadtime); // ETD = arrival time + load time.
            $nextrun = getTimeofNextRun("AI", 50);  // next run time minutes since midnight second. up to 50 min late.
            $waitingforrun = $SAVED['waitingforrun'];
            // If the run we are waiting on changed in port, the previous run must have been cancelled.  Except for ???
            if($waitingforrun!="") {  // if we are waiting for a run
                if($nextrun>$waitingforrun) LogFerryRun("AI", "CANCELLED", $waitingforrun); // prior run is cancelled
            }
            $SAVED['waitingforrun'] = $nextrun; // remember the run we are waiting on
            $delaytime = $ETD - $nextrun;  // calculate delay       
            $ferryport = "AI";
            //if($delaytime > 46) LogFerryRun($ferryport, "CANCELLED"); // run is cancelled
            break;

        case "toST": // travelling to ST
            if($priorferrystate == "atAI") {  // if ferry just left AI
                LogFerryRun("AI");
            }
            $SAVED['WaitingforRun'] = "";
            $nextrun = getTimeofNextRun("ST");  // next run time in minutes-since-midnight 
            $ETD = $now + $traveltime + $loadtime + $dockingtime;  // ESTIMATED TIME OF DEPARTURE
            $delaytime = $ETD - $nextrun;  // calculate delay       
            $ferryport = "St";
            break;
        
        default:
            echo "Error - invalid ferrystate = $ferrystate<br>";
    }

    // $delaytime = delay in minutes, i.e. time past the next scheduled run. if <0 it is not late.

    $SAVED['nextrun'] = $nextrun; // time of next run
    $SAVED["delaytime"] = $delaytime;  
    if($debug) echo "delaytime=$delaytime<br>";
    if($nextrun==0) return "";  // if no nextrun
    //if($delaytime <5) return "<span style='color:darkgreen'>OnTime for $ferryport " . ftime($nextrun)  . " run.</span>";  // give 5 minutes of grace for a late boat
    if($delaytime <5) return "<span style='color:white;background-color:green'>&nbsp;OnTime&nbsp;</span><span style='color:darkgreen'>&nbsp;for $ferryport " . ftime($nextrun)  . " run.</span>";  // give 5 minutes of grace for a late boat
    //if($delaytime <=6) return "";  // give 5 minutes of grace for a late boat
    if($now > $ETD) $dETD = "";  // if the ETD is > now, display it. Otherwise don't.
    else $dETD = "ETD " . ftime($ETD);  
    //$delaymsg = "<span style='color:red'>Late $delaytime m for $ferryport " . ftime($nextrun)  . " run. $dETD</span>";  
    $delaymsg = "<span style='color:white;background-color:red'>&nbsp;Late&nbsp;</span><span style='color:red'>&nbsp;$delaytime m for $ferryport " . ftime($nextrun)  . " run. $dETD</span>";  
    $latedebug =  date('m/d H:i ') . " $ferrystate: time=$now,  nextrun=" . ftime($nextrun) . ", traveltime=$traveltime, delaytime=$delaytime, arrivaltime=" .
        ftime($ferryarrivaltime) . ", $dETD |";  
    //file_put_contents("ferrylatelog.txt",  $latedebug . $delaymsg . "\n", FILE_APPEND);

    if($delaytime > 12) echo $latedebug . "\n $delaymsg";
    return $delaymsg;
}
/////////////////////////////////////////////////////////////////////////////////////////////
//  ftime - format time in minutes since midnight to hh:mm. e.g. 65 becomes 01:05
function ftime($t) {
    $am = "a";
    if($t >= 12*60) $am = "p";
    if($t >= 13*60) $t = $t - 12*60;
    return  floor($t/60) . ":" . sprintf("%02d", ($t%60)) . $am;
}

/////////////////////////////////////////////////////////////////////////////////////////
//  checkforTwoBoats - returns message if two boats are running. Only call if pi=2.
//  entry   none
//  exit    returns 2 boat message if FRI,SAT,MON afternoon between late may and early september
//
function checkforTwoBoats() {
    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $loctime = localtime();  // returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday, 7-dayofyear (0-365)
    // Sun, Mon, Fri 12 - 1200 - 1800 
    //echo "loctime 6=" . $loctime[6] . ", loctime[2]=" . $loctime[2];
    if(($loctime[7]>140 && $loctime[7]<250) && ($loctime[6]==0 || $loctime[6]==1 || $loctime[6]==5) && ($loctime[2]>=12 && $loctime[2]<=18)) {
        //echo " two boats";
        return "<span style='color:darkgreen'>Two boat service now.</span>"; 
    }
    return "";
}

// //////////////////////////////////////////////////////////////////////////////////
// // getTimeofNextSTRun();  next run time in unix seconds. up to 30 minutes late for next run.
// // Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
// //
// //  entry   $STAI = "AI" or "ST"
// //  exit    returns time of scheduled run, as minutes since midnight: hh*60+mm.  0 if no run.
// //  CAUTION: resets global timezone to PT
// //  NOTE: this looks for the next run based on current time -30.  This makes up for a run being up to
// //    30 minutes late.  After 30 minutes late it will find the next run.
// function getTimeofNextRun($STAI)  {
//     // this array also used in getferryoverflow.php and should be in a shared file.
//     $ST = array(445,545,705,820,930,1035,1210,1445,1550,1700,1810,1920,2035,2220); // ST departures
//     $AI = array(515,620,735,855,1005,1110,1245,1515,1625,1735,1845,1955,2110,2250); // AI departures

//     date_default_timezone_set("America/Los_Angeles"); // set PDT
//     $utc = time(); // UTC time
//     $loctime = localtime($utc - 30*60);  // Backup 30 min. returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday
//     $s = 0;
//     if($loctime[6]==6 || $loctime[6]==0) $s = 1; // skip early runs on sat, sun (d=0 or 6)
//     $lt = $loctime[2] * 100 + $loctime[1];  // local time in hhmm.

//     if($STAI == "ST") {
//         // loop through steilacoom
//         for($i=$s; $i<count($ST); $i++){
//             if($lt < $ST[$i]) break;
//         }
//         if($i == count($ST)) return 0;
//         //echo "Local time-30m $lt. Next Run " . $ST[$i]; // DEBUG
//         return (floor($ST[$i]/100)*60) + ($ST[$i]%100);

//     } else {
//          // loop through AI
//         for($i=$s; $i<count($AI); $i++){
//             if($lt < $AI[$i]) break;
//         }
//         if($i == count($AI)) return 0;
//         //echo "$STAI Local time-30m $lt. Next Run " . $AI[$i]; // DEBUG
//         return (floor($AI[$i]/100)*60) + ($AI[$i]%100);      
//     }
//     return 0;
// }

////////////  Use Dailycache.txt 12/6/22. ////////////////////////////////////////////////////////////////////////////////////////////////////////
//445;(gDayofWeek>0)&&(gDayofWeek<6)&&!InList(gMonthDay,1231,101,221,memorialday,619,704,laborday,1111,thanksgiving,thanksgiving+1,1224,1225);545;*;705;*;820;*;
//930;(!(gDayofWeek==1&&(gWeekofMonth==1||gWeekofMonth==3)));1035;*;1210;*;
//1230;(InList(gMonthDay,memorialday-3,memorialday-1,704))||((InList(gDayofWeek,5,0,1)&&(gMonthDay>=817)&&(gMonthDay<(906))));1445;*;
//1515;(InList(gMonthDay,memorialday-3,memorialday-1,704))||((InList(gDayofWeek,5,0,1)&&(gMonthDay>=817)&&(gMonthDay<(906))));1550;*;
//1625;(InList(gMonthDay,memorialday-3,memorialday-1,704))||((InList(gDayofWeek,5,0,1)&&(gMonthDay>=817)&&(gMonthDay<(906))));1700;*;
//1735;(InList(gMonthDay,memorialday-3,memorialday-1,704))||((InList(gDayofWeek,5,0,1)&&(gMonthDay>=817)&&(gMonthDay<(906))));1810;*;
//1920;*;2035;*;2220;(!InList(gMonthDay,1015,1113))
$gtimestamp = 0;
$gDayofWeek = 0;
$gDayofMonth = 0;
$gMonthDay = 0;
$gWeekofMonth = 0;
//////////////////////////////////////////////////////////////////////////////////
// getTimeofNextRun();  
// Returns time of next run, using the ferry times in ferryscheduleinclude.txt (formerly dailycache.txt.
//  That way dailycache.txt rules are used by the AndersonIslandAssistant AND this code.
//  Once each half hour, this will read the schedule from dailycache.txt, extract the ferry schedule, and evaluate it
//  to determine the next scheduled run.  Based on the code in index.js in the app.
// 
// next run time in unix seconds. up to 30 minutes late for next run.
// Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
//
//  entry   $STAI = "AI" or "ST"
//          $backup = how many minutes to turn back the clock to find the next run.
//  exit    returns time of scheduled run, as minutes since midnight: hh*60+mm.  0 if no run.
//  CAUTION: resets global timezone to PT
//  NOTE: this looks for the next run based on current time -30.  This makes up for a run being up to
//    30 minutes late.  After 30 minutes late it will find the next run.
function getTimeofNextRun($STAI, $backup=30)  {
    global $gtimestamp, $gDayofWeek, $gDayofMonth, $gMonthDay, $gWeekofMonth;
    global $SAVED;  // persistent data
    global $debug;

    //$backup = 30;
    //$backup = 50; // allowed late time.  This doesn't work because once a ferry sails it trys to find the next run, and this will find a previous run.
    $dailycache = "ferryscheduleinclude.txt";
    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $gtimestamp = time(); // time in seconds
    $loctime = localtime($gtimestamp - $backup*60);  // Backup 30 min. returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday
    $s = 0;
    $lt = $loctime[2] * 100 + $loctime[1];  // local time in hhmm.
    if($debug) echo "lt=$lt <br>";//DEBUG

    //  Steilacoom
    if($STAI == "ST") { // if Steilacoom
        $nextSTRun = intval($SAVED['NextSTRun']); // last saved time
        //echo " Saved nextSTRun = $nextSTRun<br>";
        if(($lt<$nextSTRun) && ($nextSTRun-$lt<800)) return (floor($nextSTRun/100)*60) + ($nextSTRun%100);  // return min since midnight
        if($nextSTRun==0 && $lt>2100) return 0; // after 9pm a return of 0 for last run is ok.
	    $STschedule = getschedule($dailycache, "FERRYTS");  // get the schedule
        if($debug) echo "---ST Schedule read.<br>";  // DEBUG
	    $ST = explode(";", $STschedule); //create array
        // loop through steilacoom and find the next scheduled run
        for($i=0; $i<count($ST); $i=$i+2){
            if($lt < intval($ST[$i])) {
                if($debug) echo " ST found = {$ST[$i]}<br>";  // debug
			    if(ValidFerryRun($ST[$i+1]))break;
		    }
        }
        if($i == count($ST)) $nextSTRun = 0; // if past last run, return 0
        else $nextSTRun = intval($ST[$i]);
        $SAVED['NextSTRun'] = $nextSTRun;  // save it
        if($debug) echo "ST Local time-30m=$lt. Next Run=$nextSTRun ----------------------<br>"; // DEBUG
        return (floor($nextSTRun/100)*60) + ($nextSTRun%100);  // convert hhmm to min since midnight

    } else {

        //  Anderson Island
        $nextAIRun = intval($SAVED['NextAIRun']); // last saved time
        if($debug) echo " Saved nextAIRun = $nextAIRun<br>";
        if(($lt<$nextAIRun)&& ($nextAIRun-$lt<800)) return (floor($nextAIRun/100)*60) + ($nextAIRun%100);  // return min since midnight
        if($nextAIRun==0 && $lt>2100) return 0; // after 9pm a return of 0 for last run is ok.
        $AIschedule = getschedule($dailycache, "FERRYTA");
        if($debug) echo "---AI schedule read<br>";  // DEBUG
	    $AI = explode(";", $AIschedule); //create array
        // loop through AI
        for($i=0; $i<count($AI); $i=$i+2){
           if($lt < intval($AI[$i])) {
                if(ValidFerryRun($AI[$i+1]))break;
           }
        }
        if($i == count($AI)) $nextAIRun = 0;
        else $nextAIRun = intval($AI[$i]);  // save it
        $SAVED['NextAIRun'] = $nextAIRun;
        if($debug) echo "$STAI Local time-30m $lt. Next Run =$nextAIRun ------------------------<br>"; // DEBUG
        return (floor($nextAIRun/100)*60) + ($nextAIRun%100);      // convert hhmm to min since midnight
   }
   return 0;
}

//////////////////////////////////////////////////////////////////////////////////////////////////
//  ValidFerryRun - matches the ValidFerryRun routine in index.js.  Use Eval to determine valid run.
// ValidFerryRun return true if a valid ferry time, else false.
//  alternate to having the rules special cased
//  Entry: flag is the rules for the ferry time:  *=always, (xxxx) = eval rules in javascript
//  Exit   returns true if $flag evaluates true, else false
//       eval rules are javascript, returning true for a valid run, else false
//      can use global variables gMonthDay, gDayofWeek, gWeekofMonth,...
//      e.g. ((gDayofWeek>0)&&(gDayofWeek<6)&&!InList(gMonthDay,1225,101,704,laborday,1123))
function ValidFerryRun($flag) {
    global $gtimestamp, $gDayofWeek, $gDayofMonth, $gMonthDay, $gWeekofMonth;
    global $debug;
	if($flag == "") return false;
	if($flag == "*") return true; // if good every day
	if(substr($flag, 0,1) != "(") return false;  // if not an expression
	// eval rules
    CalcDays();
	$flag = str_replace("gD", '$gD', $flag);
	$flag = str_replace("gM", '$gM', $flag);
	$flag = str_replace("gW", '$gW', $flag);
	$flag = str_replace("memorialday", "529", $flag);

	$flag = str_replace("laborday", "904", $flag);
	$flag = str_replace("thanksgiving", "1123", $flag);
    $r = eval('return' . $flag . ";");
    if($debug) echo " eval=$r for $flag<br>";  // debug
    return $r;
}

/////////////////////////////////////////////////////////////////////////////
// CakcDays - calculate the special days used in the rules
function CalcDays() {
    global $gtimestamp, $gDayofWeek, $gDayofMonth, $gMonthDay, $gWeekofMonth;
    $gDayofWeek = date( "w", $gtimestamp);
    $gDayofMonth = date("d", $gtimestamp);
    $gMonthDay = date("m", $gtimestamp) * 100 + $gDayofMonth;
    $gWeekofMonth = floor(($gDayofMonth - 1) / 7) + 1;  // nth occurance of day within month: 1,2,3,4,5
}

/////////////////////////////////////////////////////////////////////////////////////////
//  InList check for an argument in the list. Matches function in index.js.
//  entry   a = value
//          a1, a2, ... = values to test for
//  returns true if a = a1 or a2 or a3, ...; e.g. InList(3,0,1,2,3,4) returns true because 3 is in the list
function InList() {
    global $gtimestamp, $gDayofWeek, $gDayofMonth, $gMonthDay, $gWeekofMonth;
    $arguments = func_get_args();
    $n = func_num_args(); // number of arguments
    for ($i = 1; $i < $n; $i++) { 
        //echo " Inlist {$arguments[0]},  {$arguments[$i]}<br>";
        if ($arguments[0] == $arguments[$i]) return true; 
    }
    //echo "Inlist returns false<br>";
    return false;
}

///////////////////////////////////////////////////////////////////////////
// GetSchedule - read ferryscheduleinclude.txt and extract the ferry schedule, which is one line
//  entry   file
//          keyword
//  exit    schedule string
function GetSchedule($file, $keyword) {
    $f = file_get_contents($file);
    if($f===false) die("could not read $file");
    $i = strpos($f, $keyword);
    if($i < 1) die("no $keyword in file string");
    $i += strlen($keyword) + 1;  // skip keyword
    $j = strpos($f, "\n", $i);
    if($j < $i) die ("no end of line in file string");
    return substr($f, $i, $j-$i); // return the substring
}

///////////////////////////////////////////////////////////////////////////////
//  LogFerryRun - log the run to ferryrunlog.txt.  
//  This routine is called one 3 minute period AFTER the ferry leaves.
//  Entry: SA = ST or AI
//      $ont = ontime value. Defaults to Ontime.
//      $waitingforrun = time of next run. if "", use $SAVED[nextrun]
//  Exit Log the ferry sailing to ferryrunlog.txt
//    unixtimestamp,date,A/S,ONTIME/LATE,delaytime in min, next run time
function LogFerryRun($SA, $ont = "", $waitingforrun="") {
    global $SAVED;
    if($ont==""){
        if($SAVED["delaytime"]>10) $ont = "LATE";
        else $ont = "ONTIME";
    }
    if($waitingforrun=="")  $run = $SAVED['nextrun'];
    else $run = $waitingforrun;
    $t = time() - 3*60; // backup 3 minutes
    $msg = $t . "," . date('m/d/y H:i', $t) . ",$SA,$ont,$SAVED[delaytime]," . ftime($run) . "\n";
    file_put_contents("ferryrunlog.txt", $msg, FILE_APPEND );
    echo $msg; 
    ComputeFerryPerformance();  // compute ferry performance to ferryperformance.txt
}

//////////////////////////////////////////////////////////////////////////////
// ComputeFerryPerformance - reads the ferryrunlog.txt and computes the ontime performance
// called after a ferry run is logged.
//  Entry: reads ferryrunlog.txt: unixtimestamp,date,A/S,ONTIME/LATE,delaytime in min, next run time
//  Exit: writes the answer to ferryperformance.txt.
function ComputeFerryPerformance() {
    define("SecInWeek", 7*24*3600);
    define("SecInMonth", 30*24*3600);
    define("SecInYear",365*24*3600);
    define("SecInDay",24*3600); 
    $t = time();  // unix timestamp in seconds
    $D7Ontime = 0; $D7runs=0; $D7=0; $D7late=0; $D7cancelled=0;// 7 day ontime
    $D30Ontime=0; $D30runs=0; $D30=0; $D30late=0; $D30cancelled=0;// 30 day ontime
    $D365Ontime=0; $D365runs=0; $D365=0; //365 days


    $handle = fopen("ferryrunlog.txt", "r"); // open the file for reading
    if ($handle) {
        while (($line = fgets($handle)) !== false) { // read a line
            // process the line
            $A = explode(",", $line); // split into 0unixtimestamp,1date,2A/S,3ONTIME/LATE/CANCELLED,4delaytime in min, 5next run time
            if(count($A)==6) {
                $dt = $t - (int)($A[0]);  // elapsed time in sec
                if($dt < SecInYear) {  // year
                    if($D365==0) $D365= (int)($dt/SecInDay); // elapsed days
                    $D365runs++;
                    if($A[3]=="CANCELLED") $D365cancelled++;
                    elseif($A[3]=="LATE") $D365late++;

                    if($dt < SecInMonth) {  // month
                        if($D30==0) $D30= (int)(($dt/SecInDay)+1); // elapsed days
                        $D30runs++;
                        if($A[3]=="CANCELLED") $D30cancelled++;
                        elseif($A[3]=="LATE") $D30late++;

                        if($dt < SecInWeek) {  // week
                            if($D7==0) $D7= (int)(($dt/SecInDay)+1); // elapsed days
                            $D7runs++;
                            if($A[3]=="CANCELLED") $D7cancelled++;
                            elseif($A[3]=="LATE") $D7late++;
                        }
                    } 
                }
            }
        }
        fclose($handle); // close the file
    }
    // compute percent and write to ferryperformance.txt.
    if($D7runs>0) {
        $runsontime = $D7runs-($D7late+$D7cancelled);
        $D30runsontime = $D30runs-($D30late+$D30cancelled);
        $m = "<a href='https://www.anderson-island.org/ferryontime.php'><i class='material-icons'>&#xe8b5;</i><b>Ferry OnTime:</b></a> Last $D7 Days " . intval($runsontime*100/$D7runs) . "% ($D7late runs > 10min late, $D7cancelled cancelled), Last $D30 days " . intval($D30runsontime*100/$D30runs) . "%<br><br>\n";
        file_put_contents("ferryperformanceinclude.txt", $m);
        echo "D7Ontime-$D7Ontime, D7runs=$D7runs, D30Ontime=$D30Ontime,D30runs=$D30runs,M=$m"; // debug
    }
}

///////////////////////////////////////////////////////////////////////////////
// testgetnextrun
//
function testgetnextrun() {
    global $gtimestamp;
    $gtimestamp = time();  // time in seconds
    date_default_timezone_set("America/Los_Angeles"); // set PDT
    for($i=0; $i<(24*6); $i++) {
        $gtimestamp += 10*60;  // add 10 min
        $hhmm = date("H:m", $gtimestamp);
        $nrST = getTimeofNextRun2("ST");
        $nrAI = getTimeofNextRun2("AI");
        echo "$hhmm ST=" . ftime($nrST) . ", AI=" . ftime($nrAI) . "<br><br>";
    }
}


?>