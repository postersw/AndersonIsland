<?php
////////////////////////////////////////////////////////////
// getferrypositioncronTEST.php - retrieves current ferry position and calculates an eta.
//  Run every 3 min. by cron.
//  Handles 2 ferriess simultaneously.
// leaves the results in <ferryposition.txt> file.  This will be picked up by the getalert script
// which will tuck it into the FERRY/FERRYEND alert messages.
// Ferry position is retrieved from marinetraffic.com.  Max allowed call rate is once/3 minutes.
//
//  Files: ferryposition.txt = message to display.
//         ferrypositionsave.json = saved and restored data in json format. in $SAVED[] as an associative array.debug
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
//  TESTING NOTE (6/11/23):
//    To test against the log:
//      1. create TESTferrytestinput.txt  as an extract of ferrypositionlog.csv for the timeperiod you want to simulate.
//          The file must be in the same directory as this script.
//      2. The time will be taken from the timestamp at the beginning of each row.
//      3. Set $gUseTestData = true.
//      4. Run this script locally from Visual Studio Code "Run".  
//      5. The resulting files will be in the script directory, as TESTxxxxx.
//          Check TESTferrypositionlog.html for a summary of each time step.
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
//  1.52 1/18/24.  Correct the ferry state when it stops enroute.
//  1.53 2/26/24.  Only echo msg if ferry is cancelled or late.
//  1.54 3/4/24.   Ver 2 of LateFerry. build an array.
//  1.55 4/1/24.   When 2 ferries are detected, continue to use the one we were using until it goes away.
//                  Handles ferry testing runs and ferry switching runs.
//  1.56 4/27/24   Ensure we skip ferry outside of boundary.
//       5/12/24   Turn off BuildFerryTimes dump
//  1.59 6/9/24.   Handle 2 ferries.  Add ability to run locally in test mode against an input file from ferrypositionlog.csv.

$ver = "1.59.1"; // 3/31/24.
$gUseTestData = false;
//$gUseTestData = true; 
$gtimestamp = 0;
$gDayofWeek = 0;
$gDayofMonth = 0;
$gMonthDay = 0;
$gWeekofMonth = 0;
$longAI = -122.677; $latAI = 47.17869;   // AI Dock
$longSt = -122.603; $latSt = 47.17347;  // Steilacoom Dock
$longKe = -122.6289; $latKe = 47.1622; // ketron dock
$longE = .0007;  $latE = .001; // epselon big enough to capture the 2 steilacoom docks
$longE = .001;  // try to help ferrys at dock
//  bounding box for a valid run. outside of this box we ignore the ferry.
$longMIN = -122.7; $longMAX = -122.5; // longitude bounding
$latMIN = 47.15; $latMAX = 47.2;  // latitude bounding

$DAItoSt = ($longSt-$longAI) + ($latAI-$latSt)*.67; // total distance in longitude equivalent degrees
$running2ferries = false; // true when running 2 ferries
$rowcount = 0;

// define file names with Test
$dir="";
if($gUseTestData) $dir="TEST";
$ferrypositionfile =$dir. "ferryposition.txt";
$log = $dir.'ferrypositionlog.csv';
$ferryalertfile = $dir."alert.txt";
$ferryjsonsavefile = $dir."ferrypositionsave.json";
$ferryperformanceinclude = $dir."ferryperformanceinclude.txt";
$ferryrunlog = $dir."ferryrunlog.txt";
$ferrytestinput = $dir . "ferrytestinput.txt";
$ferrypositionhtmlfile = $dir . "ferrypositionlog.html";
if($gUseTestData) { // get rid of all the old test files
    array_map('unlink', [$log, $ferryalertfile, $ferryjsonsavefile, $ferryperformanceinclude, $ferryrunlog, $ferrypositionfile, $ferrypositionhtmlfile]);
}

$crossingtime = 20; // nominal crossing time in minutes
$MMSICA = 366659730;  // Christine Anderson
$MMSIS2 = 367153930; // Steilacoom II
$MMSI = $MMSIS2; // use steilacoom
$APIkey = "e5c425e79c24d1c960955f251b0146e361eca917";  // subscription key from MarineTraffic.com
$ferryname = ""; // based on MMSI
$fa = []; // ferry array, 0, 1, or 2 arrays of data
$mi = "<i class='material-icons mptext'>";
$ri = "";
$gtimestamp = time(); // unix time stamp
$debug = false;
//$debug = true; /////////////////////////////////////DEBUG
if($gUseTestData) $debug = true;
if($debug) echo "$ver <br>\n";

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
$ferrystateCA=""; $ferrystateS2="";
$timetoarrivalCA=0; $timetoarrivalS2=0;

// ferry run time arrays.  Loaded/saved in $SAVED.  Initialized daily by BuildFerryTimes. Added ver 1.54.
$gFerryTimes = []; // run times in minutes since midnight
$gFerryLoc = []; // ferry location: ST or AI
$gFerryStatus = []; // status: blank = not run yet, T = ran on time, L = ran late, C = skipped or cancelled
$gFerryMonthDay = 0; // month and day of the ferry times in the array: mmdd
if(!$gUseTestData)chdir("/home/postersw/public_html");  // move to web root
if($gUseTestData) {
    echo ("Running ver $ver " . date("h:i:sa") . " " . getcwd() . "\n");
    echo $ferrytestinput;
    $testhandle=fopen($ferrytestinput,"r");
    if($testhandle===false) exit("no test input file");
}

// Main Data Loop

while(true) {
if($debug) echo "\n\n==$rowcount===========================================================================================\n";
$rowcount++;
date_default_timezone_set("UTC"); // set UTC
$lt = localtime();   // time in UTC:  [0]=sec,[1]=min,[2] = hour, [3]=minday,[4]=mon,[5]=year,[6]=weekay,[7]=yearday
//echo " hour=" . $lt[2] . " ";
if(!$gUseTestData) if($lt[2]>7 && $lt[2]<11) exit(0); //DEBUG"time");  // don't run midnight - 4 (7-12 UTC)

// reload persistant data

$SAVED = json_decode(file_get_contents($ferryjsonsavefile), TRUE); // load persistant data
//var_dump($SAVED);  // print out what was loaded
$gFerryMonthDay = $SAVED['gFerryMonthDay'];
$gFerryTimes = $SAVED['gFerryTimes'];
$gFerryLoc = $SAVED['gFerryLoc'];
$gFerryStatus = $SAVED['gFerryStatus'];
$gUTCtime = "";

// get position
if($gUseTestData) {
    $fa = getTESTposition($testhandle); // from test file
    if($fa===false) exit("eof");  // exit if end of file
    $lt = localtimeX();  // get time from test file
    echo ("localTestTime=$gUTCtime  {$fa[0][0]}, {$fa[1][0]} \n");
} else {
    $fa = getposition();  // from marine traffic
}
if($debug) echo ("ferry count = " . count($fa) . "<br>\n");
$p = ""; $pi = 0; $pstr = "";

// loop through reply. There will be 0, 1, or 2 rows (1 row/ferry)
$i = 0; $pj = 0;
$px = array();  // text string
for($i=0; $i < count($fa); $i++) {
    $MMSI = $fa[$i][0]; $lat = $fa[$i][3]; $long = $fa[$i][4]; $speed = $fa[$i][5]; $course = $fa[$i][7]; 
    $status = $fa[$i][8]; $timestamp = $fa[$i][9];
    if($MMSI == $MMSICA) $ferryname = "CA";
    elseif($MMSI == $MMSIS2) $ferryname = "S2";
    else continue; // abortme("MMSI is invalid: $MMSI");
    $s = ""; 
    if($long < $longMIN || $long > $longMAX || $lat < $latMIN || $lat > $latMAX) continue; // if outside boundaries
    if($speed < 3 && $long >= -122.6036 && $long <= -122.6034) continue;  // skip boat if it is stopped & docked at the backup-boat dock

    //if($ferryname == "CA") continue; // skip CA///////////////////////////////////////////////
    //if($ferryname == "S2") continue; // skip S2///////////////////////////////////////////////
    checktimestamp($timestamp); 
    if($debug) echo " mmsi=$MMSI, lat=$lat, long=$long, speed=$speed, course=$course, status=$status, timestamp=$timestamp, ";
    //if($status != 0) continue; // skip if not normal. Doesn't work because transponder status is not set correctl

    // calculate location and arrival;

    if($speed <= 10) $s = reportatdock();  // at dock if speed<= 1 knot
    else $s = timetocross();
    // handle 2 ferries
    if($MMSI == $MMSICA) {  // CA
         $ferrystateCA = $ferrystate;
         $timetoarrivalCA = $timetoarrival;
         $ferrymsgCA = "$mi$ri</i><i> CA $s</i>";
    } else {  // S2
        $ferrystateS2 = $ferrystate;
        $timetoarrivalS2 = $timetoarrival;
        $ferrymsgS2 = "$mi$ri</i><i> S2 $s</i>";   
    }
    $p =  $p . "$ferryname $s"; 
    $px[$pi] = "$mi$ri</i><i> $ferryname $s</i>";
    $pi++;
}
if($debug) echo "fa=" . count($fa) . ", pi=$pi<br>\n";

// always display 'CA' before 'S2' when both are active.

if($pi==0) $pstr = "";
elseif($pi==1) $pstr = $px[0];
elseif(strpos($px[0], "S2") > 0) $pstr = $px[1] . "<br/>" . $px[0];  // always display CA before S2
else $pstr = $px[0] . "<br/>" . $px[1];
//echo $pstr;

// SWITCH TIME ZONE TO LOCAL  (NOT UTC) //
date_default_timezone_set("America/Los_Angeles"); // set PDT
$gtimestamp = timeX();
CalcDays();  // set $gMonthDay, etc used to evaluate ferry fules

// New Day:  Build the FerryTimes array:
if($gFerryMonthDay!= $gMonthDay || $gFerryTimes==null || $gFerryMonthDay==null) {
    BuildFerryTimes();
    $SAVED['useferry'] = $ferryname;  // reset useferry 
}

// Display OnTime / LATE message
// if running 1 ferry and it is actually late, make the time red.
$ferrylate = "";
$fps = "<br><span style='color:darkblue'>&nbsp;&nbsp;";
if($pi==1) {  // if 1 boat
    $running2ferries = false;
    $ferrylate = checkforLateFerry2($ferryname, $ferrystate, $timetoarrival) . "$fps$pstr</span>";  //  if running 1 boat, calculate if ferry is late and add message
    $SAVED['useferry'] = $ferryname; // save ferry name

} elseif($pi==2) {  // if 2 boats
    $running2ferries = checkforTwoBoats();  // if running 2 boats, issue 2 boat msg on fri, sun, mon
    if($running2ferries) { // if  officially running 2 boats
        // kludge for state change.  Mark runs complete before we check for late ferry.
        if(substr($SAVED['ferrystateS2'],0,2)=='at' && substr($ferrystateS2,0,2)=='to') {   // if S2 ferry just left the dock
            $d = substr($SAVED['ferrystateS2'],2,2);  // old dest
            LogFerryRun2($d, $SAVED['delaytimeS2'], $SAVED["waitingforrunMM$d"."S2"], 'S2');  // mark the run as compllete
        }
        if(substr($SAVED['ferrystateCA'],0,2)=='at' && substr($ferrystateCA,0,2)=='to'){ // if  CA just left the dock
            $d = substr($SAVED['ferrystateCA'],2,2);
            LogFerryRun2($d, $SAVED['delaytimeCA'], $SAVED["waitingforrunMM$d"."CA"], 'CA');  // mark the run as complete
        }
        $ferrylate = "2 ferry service now.<br>" . checkforLateFerry2("CA", $ferrystateCA, $timetoarrivalCA) . "$fps$ferrymsgCA</span>";
        $ferrylate .= "<br>" . checkforLateFerry2("S2", $ferrystateS2, $timetoarrivalS2) . "$fps$ferrymsgS2</span>";
    } else {  // if only 1 valid boat
        //continue to use the ferry we were using and pretend it is the only boat
        switch($SAVED['useferry']) {
            case "CA": $ferrylate = checkforLateFerry2("CA", $ferrystateCA, $timetoarrivalCA) . "$fps$ferrymsgCA</span>"; break;
            case "S2": $ferrylate = checkforLateFerry2("S2", $ferrystateS2, $timetoarrivalS2) . "$fps$ferrymsgS2</span>"; break;
            default: echo "ERROR when running 2 boats but not 2 boat service: useferry = {$SAVED['useferry']}";
        }
    } 
}
if($gUseTestData) echo "\nTEST DATA: {$fa[0][12]}, {$fa[1][12]}\n";
//$pstr = "$ferrylate<br><span style='color:darkblue'>&nbsp;&nbsp;$pstr</span>";  // build message as <pstr><ferrylate>
$pstr = $ferrylate;
if($debug || $gUseTestData) echo "\n PSTR: $pstr \n";

// save output and info
file_put_contents($ferrypositionfile, $pstr); // Phone Display Ferry Message
if($debug || $gUseTestData) file_put_contents($ferrypositionhtmlfile, date("m/d/y H:i:s ", timeX()). "<br>$pstr<br><br>", FILE_APPEND);
$SAVED['message'] = $pstr;        // message for debugging
file_put_contents($ferryjsonsavefile, json_encode($SAVED));  // save persistant data,
logPosition($log);// log it to csv file 
if(!$gUseTestData) break;  // if not using test data, end after 1 loop
} // endwhile $gUseTestData

return;

//////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////
// checktimnestamp - checks time stamp (UTC) to get age of position data
// 
//  entry   timestamp
//  returns $deltamin = how old the data is, in min.
//
function checktimestamp($ts) {
    global $gUseTestData;
    global $deltamin;
    if($gUseTestData) {
        $deltamin = 0;
        return;
    }
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

        // if ferry was in port, assume its travelling to the other port
        if($SAVED[$MMSI] == "A") $ferrystate = "toST"; // travelling to AI;
        else $ferrystate = "toAI";
        $timetoarrival = 10;
        if($ta<$ts) return "stopped " . round($ta,1) . " miles from AI";
        // if closer to Steilacoom
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

///////////////////////////////////////////////////////////////
//  getTESTposition - get the ferry position from ferrytestinpug.txt
//  Used for testing. Simulates the Marine Traffic position return by reading data from our log.
//  each call reads 1 line from the file and returns it as an Marine Traffic return array.
//
//  entry  ferrytestinput.txt file.
//  exit - returns json array of 0, 1, or 2 elements;  false if eof
//          $testrow incremented by 1
//  INPUT   csv log file ferrytestinput.txt
// 2024-04-30T18:18:02-07:00,1.55.1,366659730,0,426195,47.175350,-122.603800,95,511,323,15,  2024-05-01T01:16:24,TER,23,   <i class='material-icons mptext'>reply</i><i> 'CA' arriving @AI in 17 m</i>,,,,,,,,,,,,,,1.6
//      0 time              ,1-ver,   2 MMSI  3IMO,4ID, 5 LAT,       6 LONG,7SP,8HD,9CR,10ST,11 TIMESTAMP       ,12T,12UTC, 13 OUTPUT,  14 MMSI, ...
//
//  OUTPUT RETURN array matching the output from getposition()
//   MMSI-0,      IMO,      SHIP_ID, LAT,        LON,      SPEED, HEADING,COURSE, STATUS, TIMESTAMP,            DSRC, UTC_SECONDS
//  [["304010417","9015462","359396","47.758499","-5.154223","74","329",  "327",  "0",    "2017-05-19T09:39:57","TER","54","OUTPUT"],...]
//       0             1       2           3          4        5     6      7      8              9               10    11    12
// IMPORTANT: [0]; [3]; [4]; [5]; [7];[8];[9];
function getTESTposition($handle) {
    global $gUTCtime;
    static $rowcount = 0;

    // skip empty rows
    while( true){
        $rowcount++;
        $s = fgets($handle);
        if($s===false) { // if eof
            fclose($handle);
            echo "\nEOF at row $rowcount\n";
            return false;
        }
        // create an array from the ferry log.  The log is a dump of the marine traffic data.
        if($s=="") continue;// skip empty row
        $a = explode(",", $s); // unpack the CSV into an Array
        if(count($a) < 5) continue; // if an anomaly
        $gUTCtime = $a[0]; // UTCtime
        if($gUTCtime!="") break;
    }
    $a1 = array_slice($a, 2, 13); // element 1
    $a2 = array_slice($a, 15, 13); // element 2
    if(($a1[0]=="") && ($a2[0]=="")) return false; // if no data
    if(($a1[0]!="") && ($a2[0]=="")) $fa = array($a1);  // 1 element
    elseif(($a1[0]=="") && ($a2[0]!="")) $fa = array($a2);  // 1 element
    else $fa = array($a1, $a2); // unpack CSV data into array
    //var_dump($fa);  // debug
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
    $tlh = fopen($log, 'a');
    fwrite($tlh, date('c') ." $ver " . print_r($fa, true) . ", deltamin=" . round($deltamin,1) . ": ABORT $msg \n");
    fclose($tlh);
    if($msg == "0 length array") exit(); // no message if 0 length
    print_r($fa);
    exit($msg);
}

/////////////////////////////////////////////////////////////////////////////
// logPosition - writes position to CSV file in 2 columns so it is easy to view with excel
//  This log can be read back in to supply position info for a debug run.
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
        $s1 =  implode(",", array_slice($fa[0], 0, 12)) . "," . $px[0];
        if(count($fa)>1 && count($px)>1) $s2 = implode(",", array_slice($fa[1], 0, 12)) . "," . $px[1];
        else $s2 = ",,,,,,,,,,,,";
    } else  { // if 1st boat is S2
        if(strpos($px[0], "'S2'") < 0) {
            $t = $px[0]; $px[0] = $px[1]; $px[1] = $t;
        }
        $s2 =  implode(",", array_slice($fa[0], 0, 12)) . "," . $px[0];
        if(count($fa)>1 && count($px)>1) $s1 = implode(",", array_slice($fa[1], 0, 12)) . "," . $px[1];
        else $s1 = ",,,,,,,,,,,,";
    }
    date_default_timezone_set("America/Los_Angeles"); // set UTC
    fwrite($tlh, date('c', timeX()) . ",$ver,$s1,$s2," . round($deltamin,1) ."\n");
    fclose($tlh);
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
//  exit    returns true if 2 boats, else false;   2 boat message if FRI,SAT,MON 1200-1920 between late may and early september
//
function checkforTwoBoats() {
    global $gUseTestData, $gUTCtime;
    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $loctime = localtimeX();  // returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday, 7-dayofyear (0-365)
    // Sun, Mon, Fri 12 - 1200 - 1800 
    //echo "loctime 6=" . $loctime[6] . ", loctime[2]=" . $loctime[2];
    if(($loctime[7]>140 && $loctime[7]<250) && ($loctime[6]==0 || $loctime[6]==1 || $loctime[6]==5) && 
      ($loctime[2]>=12) && (($loctime[2]*100 + $loctime[1]) <=1820)) {
        //echo " two boats";
        return true; //"<span style='color:darkgreen'>Two boat service now.</span>"; 
    }
    return false; //"";
}


////////////  Use ferryscheduleinclude.txt 12/6/22. ////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////

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
    // CalcDays();  moved to main 
	$flag = str_replace("gD", '$gD', $flag);
	$flag = str_replace("gM", '$gM', $flag);
	$flag = str_replace("gW", '$gW', $flag);
	$flag = str_replace("memorialday", "529", $flag);

	$flag = str_replace("laborday", "904", $flag);
	$flag = str_replace("thanksgiving", "1123", $flag);
    $r = eval('return' . $flag . ";");
    //if($debug) echo " eval=$r for $flag<br>\n";  // debug
    return $r;
}

/////////////////////////////////////////////////////////////////////////////
// CakcDays - calculate the special days used in the rules.  Matches calculations in AIA index.js.
//  Entry   $gtimestamp is set
//  Exit    sets $gDayofWeek, $gDayofMonth, $gMonthDay, $gWeekofMonth globals
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
        //echo " Inlist {$arguments[0]},  {$arguments[$i]}<br>\n";
        if ($arguments[0] == $arguments[$i]) return true; 
    }
    //echo "Inlist returns false<br>\n";
    return false;
}

///////////////////////////////////////////////////////////////////////////
// GetSchedule - read ferryscheduleinclude.txt and extract the ferry schedule, which is one line
//  entry   file
//          keyword
//  exit    schedule string
function GetSchedule($file, $keyword) {
    $f = file_get_contents($file);
    if($f===false) exit("could not read $file");
    $i = strpos($f, $keyword);
    if($i < 1) exit("no $keyword in file string");
    $i += strlen($keyword) + 1;  // skip keyword
    $j = strpos($f, "\n", $i);
    if($j==$i) $j = strpos($f, "\r", $i);
    if($j <= $i) exit ("no end of line in file string");
    $s = substr($f, $i, $j-$i);
    //echo "GetSchedule i=$i,j=$j,s= $s ***************\n";
    return $s; // return the substring
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
    global $ferryperformanceinclude;
    global $ferryrunlog;
    $t = timeX();  // unix timestamp in seconds
    $D7Ontime = 0; $D7runs=0; $D7=0; $D7late=0; $D7cancelled=0;// 7 day ontime
    $D30Ontime=0; $D30runs=0; $D30=0; $D30late=0; $D30cancelled=0;// 30 day ontime
    $D365Ontime=0; $D365runs=0; $D365=0; $D365late=0; $D365cancelled=0;//365 days


    $handle = fopen($ferryrunlog, "r"); // open the file for reading
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
        $m = "<a href='https://www.anderson-island.org/ferryontime.php'><i class='material-icons'>&#xe8b5;</i><b>Ferry OnTime:</a> Last $D7 Days</b> " . 
        intval($runsontime*100/$D7runs) . "% ($D7late runs > 10min late, $D7cancelled cancelled),<br><b> Last $D30 days</b> " .
        intval($D30runsontime*100/$D30runs) . "%, <b>Last $D365 days</b> " .
        intval(($D365runs-$D365cancelled-$D365late)*100/$D365runs) . "%<br><br>\n";
        file_put_contents($ferryperformanceinclude, $m);
        //echo "D7Ontime-$D7Ontime, D7runs=$D7runs, D30Ontime=$D30Ontime,D30runs=$D30runs,M=$m"; // debug
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
        echo "$hhmm ST=" . ftime($nrST) . ", AI=" . ftime($nrAI) . "<br><br>\n";
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////// version 2 FerryLate /////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////
//  checkforLateFerry2 - check for late ferry - V2 uses gFerryTimes array
//    - check for ferry being late and adds a message. Writes 'ferryrunlog.txt' for statistics.
//      Don't call if running 2 ferrys. Its too confusing and $ferrystate is unsafe for multiple boats.
//  entry   $ferrytouse = name of ferry to use, CA or S2.  "" to just use globals..
//          globals $ferrystate = atST, toAI, atAI, toST.  NOT SAFE FOR MULTIPLE BOATS
//          $timetoarrival = min to arrival if state = toAI/toST
//          $gFerry... arrays filled
//  exit    returns prefix to ferry message: "LATE nn m for XX hh:mm run", or "ONTIME for XX hh:mm run", or ""
//          Writes ontime/late msg to ferryrunlog.txt if the ferry has left: datetime,S/A,Ontime/LATE,delay,scheduledruntime
//          When ferry leaves, writes to 'ferryrunlog.txt'
//          side effects:  if late, writes to stdout
//          $SAVED["ferrystatexx"] = ferry state: atST, atAI, toST, toAI
//          $SAVED["ferrivarrivaltimeMMAIxx"], []"ferryarrivaltimeMMSTxx"] = arrival time in MM - min since midnight
//          $SAVED["delaytimexx"] = $delaytime in min
//          $SAVED['waitingforrunMMSTxx'], ['waitingforrunMMAIxx'] = the ferry run we are waiting for, in MM
//          $SAVED['nextrunMMxx'] = time of next run OUTPUT ONLY
//
//  Detecting CANCELLED runs: When the time of next run changes from the time of the run you are waiting on,
//      the run you are waiting on is assumed to be cancelled and is logged as a cancelled run.  
//      This should happen 51 minutes after the scheduled run time.
//
function checkforLateFerry2($ferrytouse, $ferrystate, $timetoarrival) {
    //global $ferrystate, $timetoarrival; PASSED IN
    global $SAVED;
    global $deltamin; // age of ferry status
    global $gUseTestData, $debug;
    
    // handle 2 ferries by continuing to use the one we used previously.
    //  Only uses ferrystate and timetoarrival.  

    if($ferrytouse =="") echo "ERROR ferrytouse = null in checkforLastFerry2";

    if($debug) echo "ferrytouse=$ferrytouse,ferrystate=$ferrystate,timetoarrival=$timetoarrival<br>\n";

    if($ferrystate=="") return "";  // unable to determine state;
    $priorferrystate = $SAVED["ferrystate$ferrytouse"];  // use the SAVE array to remember position
    $SAVED["ferrystate$ferrytouse"] = $ferrystate;  // update current ferry state
    $traveltime = $timetoarrival; // time to travel AI-St or St-AI in minutes. Already adjusted by $deltamin.
    $loadtime = 8; // time to unload & load the ferry
    $dockingtime = 3; // time to dock the ferry and begin unloading - 1 3 min cycle
    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $loctime = localtimeX();  // returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday
    $nowMM = $loctime[2] * 60 + $loctime[1]; // - 3;  // local time in minutes since midnight. 
    if($loctime[2] >= 20) $loadtime = 5; // if >8pm, load time is just 5 minutes. 
    $ferryarrivaltimeMM = 0;

    // All arithmetic is done in minutes since midnight: xxxxMM
    switch($ferrystate) {
        case "atST": // docked at ST
            // Save Ferry arrival time at ST
            if($priorferrystate != "atST") {  // if ferry was coming to ST, it has just arrived, so save its arrival time
                $ferryarrivaltimeMM = $nowMM - floor($deltamin); // adjust for age of ferry position data
                $SAVED["ferryarrivaltimeMMST$ferrytouse"] = $ferryarrivaltimeMM;  //  file_put_contents("ferryarrivaltimeMMST", $nowMM);
            } else $ferryarrivaltimeMM = $SAVED["ferryarrivaltimeMMST$ferrytouse"];  // file_get_contents("ferryarrivaltimeST");
            if($ferryarrivaltimeMM > $nowMM) $ferryarrivaltimeMM = 0; // arrivaltime has to be before nowMM. allow for end of day and wierd stuff
            $ETDMM = max($nowMM, $ferryarrivaltimeMM+$loadtime); // ETDMM = arrival time + load time.
            $nextrunMM = gettimeofnextrunMM("ST", 50, $ferrytouse);  // next run time in minutes-since-midnight. up to 50 minutes late for next run.
            $waitingforrunMM = $SAVED["waitingforrunMMST$ferrytouse"];
            // If the run we are waiting on changed in port, the previous run must have been cancelled. 
            if($waitingforrunMM!="") {
                if($nextrunMM>$waitingforrunMM) {
                    CheckForCancelledRuns($waitingforrunMM);  // check for cancelled runs
                }
            }
            $SAVED["waitingforrunMMST$ferrytouse"] = $nextrunMM; // remember the run we are waiting on
            $delaytime = $ETDMM - $nextrunMM;
            $ferryport = "St";
            break;

        case "toAI": // travelling from ST to AI
            // Ferry just left ST for AI.  Log ST departure for 'waitingforrunMMST' run.
            if($priorferrystate == "atST") {  // if ferry just jeft ST
                $wfrST = $SAVED["waitingforrunMMST$ferrytouse"];
                if($wfrST=="" || $wfrST==0 || $wfrST==null) $wfrst = gettimeofnextrunMM("ST", 30, $ferrytouse); // if not waiting for a run, get run time 30 min ago
                if($debug) "Ferry left ST for AI: run $wfrST \n";
                LogFerryRun2("ST", $SAVED["delaytime$ferrytouse"], $wfrST, $ferrytouse);
                CheckForCancelledRuns($wfrST);  // check for cancelled runs occuring before the run that just left.
                $SAVED["waitingforrunMMST$ferrytouse"] = "";  // just sailed. clear waiting for run to ST
            }
            $waitingforrunMM = $SAVED["waitingforrunMMAI$ferrytouse"];
            $nextrunMM = gettimeofnextrunMM("AI", 30, $ferrytouse); // next run time minutes since midnight second
            // Detect cancelled run
            if($waitingforrunMM!="") {  // if we are waiting for a run
                if($nextrunMM>$waitingforrunMM) {
                    CheckForCancelledRuns($waitingforrunMM);  // check for cancelled runs
                }
            }
            $SAVED["waitingforrunMMAI$ferrytouse"] = $nextrunMM; // remember the run we are waiting on
            $ETDMM = $nowMM + $traveltime + $loadtime + $dockingtime; // ESTIMATED TIME OF DEPARTURE. 
            $delaytime = $ETDMM - $nextrunMM;  // calculate delay    
            $ferryport = "AI";
            break;

        case "atAI": // docked at AI
            // Save ferry arrival time at AI
            if($priorferrystate != "atAI") {  // if ferry was coming to AI, it has just arrived, so save its arrival time
                $ferryarrivaltimeMM = $nowMM -floor($deltamin);  // adjust for age of ferry position message
                $SAVED["ferryarrivaltimeMMAI$ferrytouse"] = $ferryarrivaltimeMM; //  file_put_contents("ferryarrivaltimeMMAI", $nowMM);
            } else $ferryarrivaltimeMM = $SAVED["ferryarrivaltimeMMAI$ferrytouse"]; //$ferryarrivaltimeMM = file_get_contents("ferryarrivaltimeMMAI");
            if($ferryarrivaltimeMM > $nowMM) $ferryarrivaltimeMM = 0; // arrival time has to be before nowMM. allow for end of day and wierd stuff
            $ETDMM = max($nowMM, $ferryarrivaltimeMM+$loadtime); // ETDMM = arrival time + load time.
            $nextrunMM = gettimeofnextrunMM("AI", 50, $ferrytouse);  // next run time minutes since midnight second. up to 50 min late.
            $waitingforrunMM = $SAVED["waitingforrunMMAI$ferrytouse"];
            // If the run we are waiting on changed in port, the previous run must have been cancelled.  
            if($waitingforrunMM!="") {  // if we are waiting for a run
                if($nextrunMM>$waitingforrunMM) {
                    CheckForCancelledRuns($waitingforrunMM);  // check for cancelled runs
                }
            }
            $SAVED["waitingforrunMMAI$ferrytouse"] = $nextrunMM; // remember the run we are waiting on
            $delaytime = $ETDMM - $nextrunMM;  // calculate delay       
            $ferryport = "AI";
            break;

        case "toST": // travelling from AI to ST
            // Ferry just left AI for ST.  Log AI departure for 'waitingforrunMMAI' run.
            if($priorferrystate == "atAI") {  // if ferry just left AI
                if($debug) "Ferry left AI for ST: run " . MMtohhmm($SAVED["waitingforrunMMAI$ferrytouse"]) . " \n";
                LogFerryRun2("AI", $SAVED["delaytime$ferrytouse"], $SAVED["waitingforrunMMAI$ferrytouse"], $ferrytouse);
                CheckForCancelledRuns($SAVED["waitingforrunMMAI$ferrytouse"]);  // check for cancelled runs
                $SAVED["waitingforrunMMAI$ferrytouse"] = "";
            }
            $waitingforrunMM = $SAVED["waitingforrunMMST$ferrytouse"];
            $nextrunMM = gettimeofnextrunMM("ST", 30, $ferrytouse);  // next run time in minutes-since-midnight 
            // Detect cancelled run
            if($waitingforrunMM!="") {  // if we are waiting for a run and it changes, the run we were waiting on must have been cancelled.
                if($nextrunMM>$waitingforrunMM) {
                    CheckForCancelledRuns($waitingforrunMM);  // check for cancelled runs
                }
            }
            $SAVED["waitingforrunMMST$ferrytouse"] = $nextrunMM; // remember the run we are waiting on
            $ETDMM = $nowMM + $traveltime + $loadtime + $dockingtime;  // ESTIMATED TIME OF DEPARTURE
            $delaytime = $ETDMM - $nextrunMM;  // calculate delay       
            $ferryport = "St";
            break;
        
        default:
            echo "Error - invalid ferrystate = $ferrystate\n";
    }

    // $delaytime = delay in minutes, i.e. time past the next scheduled run. if <0 it is not late.

    $SAVED["nextrunMM$ferrytouse"] = $nextrunMM; // time of next run
    $SAVED["delaytime$ferrytouse"] = $delaytime;  
    if($debug) echo "delaytime=$delaytime<br>\n";
    if($nextrunMM==0) return "";  // if no nextrun
    //if($delaytime <5) return "<span style='color:darkgreen'>OnTime for $ferryport " . ftimeMM($nextrunMM)  . " run.</span>";  // give 5 minutes of grace for a late boat
    if($delaytime <5) return "<span style='color:white;background-color:green'>&nbsp;OnTime&nbsp;</span><span style='color:darkgreen'>&nbsp;for $ferryport " . ftimeMM($nextrunMM)  . " run</span>";  // give 5 minutes of grace for a late boat
    //if($delaytime <=6) return "";  // give 5 minutes of grace for a late boat
    if($nowMM > $ETDMM) $dETDMM = "";  // if the ETDMM is > nowMM, display it. Otherwise don't.
    else $dETDMM = "ETD " . ftimeMM($ETDMM);  
    //$delaymsg = "<span style='color:red'>Late $delaytime m for $ferryport " . ftimeMM($nextrunMM)  . " run. $dETDMM</span>";  
    $delaymsg = "<span style='color:white;background-color:red'>&nbsp;Late&nbsp;</span><span style='color:red'>&nbsp;$delaytime m for $ferryport " . ftimeMM($nextrunMM)  . " run. $dETDMM</span>";  
    $latedebug =  date('m/d H:i ') . " $ferrystate: time=$nowMM,  nextrun=" . ftimeMM($nextrunMM) . ", traveltime=$traveltime, delaytime=$delaytime, arrivaltime=" .
        ftimeMM($ferryarrivaltimeMM) . ", $dETDMM |";  

    if($delaytime > 12) echo $latedebug . "\n $delaymsg";
    return $delaymsg;
}
/////////////////////////////////////////////////////////////////////////////////////////////
//  ftimeMM - format time in Minutes since Midnight to hh:mm. e.g. 65 becomes 01:05
function ftimeMM($t) {
    $am = "a";
    if($t >= 12*60) $am = "p";
    if($t >= 13*60) $t = $t - 12*60;
    return  floor($t/60) . ":" . sprintf("%02d", ($t%60)) . $am;
}

//////////////////////////////////////////////////////////////////////////////////
// gettimeofnextrunMM - Returns time of next run, using the ferry times in ferryscheduleinclude.txt (formerly dailycache.txt.
//  That way dailycache.txt rules are used by the AndersonIslandAssistant AND this code.
//  Every morning, BuildFerryTimes reads ferryscheduleinclude.txt and builds gFerryTimes[] 
//      with times for all the valid runs.
// 
// next run time in unix seconds. up to 30 minutes late for next run.
// Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
//
//  entry   gFerryTimes[] = run times, gFerryLoc[] = location
//          $backup = how many minutes to turn back the clock to find the next run.
//          
//  exit    returns time of scheduled run, as minutes since midnight: hh*60+mm.  0 if no run.
//
//  CAUTION: resets global timezone to PT
//  NOTE: this looks for the next run based on current time -30.  This makes up for a run being up to
//    30 minutes late.  After 30 minutes late it will find the next run.
//    If running 2 ferries, the backup is always 21 minutes.
//
function gettimeofnextrunMM($STAI, $backup=30, $ferrytouse)  {
    global $gtimestamp, $gDayofWeek, $gDayofMonth, $gMonthDay, $gWeekofMonth;
    global $gFerryTimes, $gFerryLoc, $gFerryStatus, $gFerryMonthDay;
    global $SAVED;  // persistent data
    global $debug;
    global $gUseTestData;
    global $gUTCtime;
    global $running2ferries;
    global $timetoarrivalCA, $timetoarrivalS2; 
    global $ferrystateCA, $ferrystateS2;

    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $gtimestamp = timeX(); // time in seconds
    //echo "-> TIME DEBUG gUTCtime=$gUTCtime, gtimestamp=" . date("H:i:s", $gtimestamp) . "\n";
    // 2 Ferry Kludge: Don't backup time because the ferries don't leave their docks simultaneously.
    $skiprun=false;
    if($running2ferries) $backup = 10; // if 2 ferries, override the backup and use 10 min
    $loctime = localtime($gtimestamp - $backup*60);  // Backup 30 min. returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday
    $s = 0;
    $lt = $loctime[2] * 100 + $loctime[1];  // local time in hhmm.
    if($debug) echo "lt=$lt, gFerryMonthDay=$gFerryMonthDay, gMonthDay=$gMonthDay<br>\n";//DEBUG
    
    //  Steilacoom
    if($STAI == "ST") { // if Steilacoom.  Going to or at ST
        if($running2ferries) {        // 2 ferry kludge to handle the case of sailing to a port where the other ferry already is
            if($ferrytouse=='CA') {
                if($ferrystateCA=='toST' && $ferrystateS2=='atST') $skiprun = true;
                 // for the ferry that is further away from ST, pick the next sailing time
                elseif($ferrystateCA=='toST' && $ferrystateS2=='toST' && ($timetoarrivalCA>$timetoarrivalS2)) $skiprun = true;
            }elseif($ferrytouse=='S2') {
                if($ferrystateS2=='toST' && $ferrystateCA=='atST') $skiprun = true;
                // for the ferry that is further awayh from ST, pick the next sailing time
                elseif($ferrystateS2=='toST' && $ferrystateCA=='toST' && ($timetoarrivalS2>$timetoarrivalCA)) $skiprun = true;
            }
        }

        // loop through steilacoom and find the next scheduled run
        for($i=0; $i<count($gFerryTimes); $i++){
            if($gFerryStatus[$i]=="") {  // if not run yet today.  Skipped to handle fuel runs.
                if (($gFerryLoc[$i]=="ST") && ($lt < $gFerryTimes[$i])) {
                    if($skiprun) {$skiprun = false; continue;}  // skip run if its the next ferry
                    if($debug) echo " ST found = {$gFerryTimes[$i]}<br>\n";  // debug
			        break;
                }
		    }
        }
        if($i >= count($gFerryTimes)) $nextSTRun = 0; // if past last run, return 0
        else $nextSTRun = $gFerryTimes[$i];
        if($debug) echo "ST Local time-50m=$lt. Next Run=$nextSTRun ----------------------<br>\n"; // DEBUG
        return hhmmtoMM($nextSTRun); //(floor($nextSTRun/100)*60) + ($nextSTRun%100);  // convert hhmm to min since midnight

    } else {

        //  Anderson Island - Going to or at AI
        if($running2ferries) {        // 2 ferry kludge to handle the case of sailing to a port where the other ferry already is
            if($ferrytouse=='CA') { 
                if($ferrystateCA=='toAI' && $ferrystateS2=='atAI') $skiprun = true;
                // for the ferry that is further awayh from AI, pick the next sailing time
                elseif($ferrystateCA=='toAI' && $ferrystateS2=='toAI' && ($timetoarrivalCA>$timetoarrivalS2)) $skiprun = true;
            }elseif($ferrytouse=='S2') {  // for the ferry that is further away from AI, pick the next sailing time
                if($ferrystateS2=='toAI' && $ferrystateCA=='atAI') $skiprun = true;
                elseif($ferrystateS2=='toAI' && $ferrystateCA=='toAI' && ($timetoarrivalS2>$timetoarrivalCA)) $skiprun = true; // for the ferry that is further awayh from ST, pick the next sailing time
            }
        }
        for($i=0; $i<count($gFerryTimes); $i++){
            if($gFerryStatus[$i]=="") {  // if not run yet today  Skipped to handle fuel runs.
                if(($gFerryLoc[$i]=="AI") && ($lt < $gFerryTimes[$i])) {
                    if($skiprun) {$skiprun = false; continue;}
                    if($debug) echo " AI found = {$gFerryTimes[$i]}<br>\n";  // debug
                    break;
                }
            }
        }
        if($i >= count($gFerryTimes)) $nextAIRun = 0;
        else $nextAIRun = $gFerryTimes[$i];  // save it
        if($debug) echo "AI Local time-30m $lt. Next Run =$nextAIRun ------------------------<br>\n"; // DEBUG
        return hhmmtoMM($nextAIRun); //(floor($nextAIRun/100)*60) + ($nextAIRun%100);      // convert hhmm to min since midnight
    }
    return 0;
}

//////////////////////////////////////////////////////////////////////////////////////////////////
//  BuildFerryTimes - build gFerryTimes array that contains the ferry time
//  Runs once/day when the date is different from gFerryMonthDay
//  reads file ferryscheduleinclude.txt.
//  Entry: none
//  Exit: builds gFerryTimes, gFerryLoc, gFerryStatus, gFerryMonthDay
//        saves them in $SAVED
//  ferry schedule times are a CSV string:  time(hhmm),<expression>,time(hhmm),<expression>,...
//
function BuildFerryTimes() {
    global $gtimestamp, $gDayofWeek, $gDayofMonth, $gMonthDay, $gWeekofMonth;
    global $gFerryTimes, $gFerryLoc, $gFerryStatus, $gFerryMonthDay;
    global $debug;
    global $SAVED;
    
    $dailycache = "ferryscheduleinclude.txt";
    $STschedule = GetSchedule($dailycache, "FERRYTS");  // get the schedule
    $AIschedule = GetSchedule($dailycache, "FERRYTA");  // get the schedule
    $AI = explode(";", $AIschedule); //create array
    $ST = explode(";", $STschedule); //create array
    //echo "ST=\n"; var_dump($ST);/////////////////////////////////////////////////////////////////////////////////////
    // loop through schedules
    $j = 0;
    for($i=0; $i<count($AI); $i=$i+2){
        if(ValidFerryRun($ST[$i+1])) {
            $gFerryTimes[$j] = intval($ST[$i]);
            $gFerryLoc[$j] = "ST";
            $gFerryStatus[$j] = "";
            $j++;
        }
        if(ValidFerryRun($AI[$i+1])) {
            $gFerryTimes[$j] = intval($AI[$i]);
            $gFerryLoc[$j] = "AI";
            $gFerryStatus[$j] = "";
            $j++;
        }
    }

    $gFerryMonthDay = $gMonthDay;
    $SAVED['gFerryTimes'] = $gFerryTimes;  // save for later reload
    $SAVED['gFerryLoc'] = $gFerryLoc;
    $SAVED['gFerryStatus'] = $gFerryStatus;
    $SAVED['gFerryMonthDay'] = $gFerryMonthDay;
    echo "BuildFerryTimes: gFerryMonthDay= $gFerryMonthDay: $j entries. <br>\n";
    //var_dump($gFerryTimes);
}

///////////////////////////////////////////////////////////////////////////////
//  LogFerryRun2 - log the run to ferryrunlog.txt and mark gFerryStatus as Ontime or Late
//  This routine is called one 3 minute period AFTER the ferry leaves.
//  
//  Entry: SA = ST or AI
//      $delaytime = delay time (late time) in minutes. Sets ontime/late text string..
//      $waitingforrunMM = time of next run in minutes since midnight.
//      $ferrytouse = ferry name
//  Exit Log the ferry sailing to ferryrunlog.txt
//    unixtimestamp,date,A/S,ONTIME/LATE/CANCLLED,delaytime in min, next run time
//      Marks FerryStatus array for sailing time as O or L.  This helps us find skipped/cancelled runs.
//
function LogFerryRun2($SA, $delaytime, $waitingforrunMM, $ferrytouse) {
    global $gFerryTimes, $gFerryStatus, $gFerryLoc;
    global $ferryrunlog;
    global $debug, $gUseTestData;
    global $gUTCtime; // test data time
    global $SAVED;

    if($debug) echo "LogFerryRun2:" .date('m/d/y H:i', timeX()). " SA=$SA, delay=$delaytime; waitingforrunMM=" . MMtohhmm($waitingforrunMM) . ", $ferrytouse<br><\n";
    if($delaytime < -10) {  // special case for fuel runs because code will try to mark a run complete after a skipped run.
        echo "ERROR LogFerryRun2 Run NOT marked because delay time < -10: $delaytime, SA=$SA, waitingforrunMM=" . MMtohhmm($waitingforrunMM) .", $ferrytouse<br><\n";
        return;
    }

    $ont = ($delaytime>=10)? "LATE" : "ONTIME";
    if($waitingforrunMM=="")  $runMM = $SAVED["nextrunMM$ferrytouse"];
    else $runMM = $waitingforrunMM;
    $t = time() - 3*60; // backup 3 minutes
    if($gUseTestData) $t = strtotime($gUTCtime); // test data
    $msg = $t . "," . date('m/d/y H:i', $t) . ",$SA,$ont,$delaytime," . ftimeMM($runMM) . ",$ferrytouse\n";


    //echo $msg;

    //  find run we are waiting for, based on time AND location, and mark it O or L 
    $runt = MMtohhmm($runMM); // convert MM to hhmm
    for($i=0; $i<count($gFerryTimes); $i++) {
        if(($runt==$gFerryTimes[$i]) && ($SA==$gFerryLoc[$i])) break;
    }
    //  if not found
    if($i==count($gFerryTimes)) {
        echo "ERROR LogFerryRun2: cant find run time $runMM $ont for $SA in gFerryTimes for ferry $ferrytouse)<br>\n";
        return;
    }
    // if found but already logged
    if($gFerryStatus[$i]!="") {
        //echo "ERROR LogFerryRun2, FerryStatus not blank: i=$i, gFerryStatus[i]={$gFerryStatus[$i]} for {$gFerryTimes[$i]} {$gFerryLoc[$i]} }\n";
        return;
    }

    // log it and mark it L or O
    file_put_contents($ferryrunlog, $msg, FILE_APPEND );  // log the runj
    $gFerryStatus[$i] = substr($ont, 0, 1); // save status O (Onlime) or L (Late) 
    $SAVED['gFerryStatus'] = $gFerryStatus;
    if($debug) echo " set gFerryStatus i=$i, $ont {$gFerryTimes[$i]} {$gFerryLoc[$i]}  \n";
    ComputeFerryPerformance();  // compute ferry performance to ferryperformance.txt
}

///////////////////////////////////////////////////////////////////////////////////
//  CheckForCancelledRuns - finds any prior runs in the array that are skipped, i.e. not marked
//      and marks them CANCELLED and logs them in run log
//  Skipped runs are runs that occurred before min($waitingfor, now) and are still status ""
//
//  Entry $waitingforMM = run we were waiting for, Minutes since Midnight
//  Exit    skipped runs marked C in gFerryStatus;
//
function CheckForCancelledRuns($waitingforMM) {
    global $gFerryTimes, $gFerryLoc, $gFerryStatus;
    global $ferryrunlog;
    global $SAVED;
    global $gUseTestData, $debug;
    global $gUTCtime;

    if($waitingforMM == "") return; // if no time
    $waitingforhhmm = MMtohhmm($waitingforMM);
    // if now is earlier than the run we are waiting for, check for cancelled run before now
    $loctime = localtimeX();  // returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday
    $nowhhmm = $loctime[2] * 100 + $loctime[1]; // - 3;  // local time in minutes since midnight. 
    if($nowhhmm < $waitingforhhmm) $waitingforhhmm = $nowhhmm; // use current time if < run we are waiting for.
    //echo "CheckForCancelledRuns waitingforMM=$waitingforMM, $waitingforhhmm=$waitingforhhmm<br>\n";

    // find all runs 1 hr prior to $waitingforMM
    for($i=0; $i<count($gFerryTimes); $i++) {
        if((($waitingforhhmm-100) >= $gFerryTimes[$i]) && ($gFerryStatus[$i]=="")) {
            // Run has been skipped. Mark it and log it.
            $gFerryStatus[$i] = "C";

            // log it

            $t = timeX() - 3*60; // backup 3 minutes
            $msg = $t . "," . date('m/d/y H:i', $t) . ",$gFerryLoc[$i],CANCELLED,0," . $gFerryTimes[$i] . "\n";
            file_put_contents($ferryrunlog, $msg, FILE_APPEND );
            echo "Ferry run CANCELLED {$gFerryLoc[$i]}, {$gFerryTimes[$i]}, waiting for " . MMtohhmm($waitingforMM) . " \n";
            if($debug) echo "$msg \n";
        }
    }
    $SAVED['gFerryStatus'] = $gFerryStatus;  // save status
}   

/////////////////////////////////////////////////////
// MMtohhmm -- minutes to midnight converted to hhmm
//  entry   MM (minutes to midnight)  h*60+m
//  exit    hhmm (hours, minutes)     h*100+m
function MMtohhmm($MM) {
    return floor($MM/60)*100 + ($MM%60); 
}
//////////////////////////////////////////////////////
// hhmmtoMMM -- hhmm converted to minutes to midnight
//  entry    hhmm (hours, minutes)     h*100+m
//  exit     MM (minutes to midnight)  h*60+m
function hhmmtoMM($hhmm) {
    return floor($hhmm/100)*60 + ($hhmm%100); 
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//  localTESTime - returns a loctime array based on test data
//  entry $gUTCtime ISO time to user
//  exit localtime array: 0sec,1min,2hour,3dayofmonth,4month(jan=0),5years since 1900, 6dayofweek(sun=0),7dayofyear,8isdaylightsavingstime
function localTESTtime(){
    global $gUTCtime;
    $t = strtotime($gUTCtime);   //
    return localtime($t);

}
// localtimeX - returns local time or log test time if $gUseTestData is set
//  exit    returns 'localtime' array
function localtimeX() {
    global $gUTCtime, $gUseTestData;
    if(!$gUseTestData) return localtime();
    return localtime(strtotime($gUTCtime));
}
// localtimeX - returns local time or log test time if $gUseTestData is set
//  exit    returns unix time stamp in seconds since 1970
function timeX() {
    global $gUTCtime, $gUseTestData;
    if(!$gUseTestData) return time();
    return strtotime($gUTCtime);
}

?>