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
//  1.26 1/23   Skip boat if docked at backup-boat dock. Always display CA before S2 if both active.
//  1.27 2/1/21 Case of boat returning to AI. Check course for a return heading.
//  1.28 2/2/21 Make position log a csv file.
//  1.30 2/11/21 Use heading to determine next port if it is unambiguous. Otherwise use previous port.
//  1.32 5/25/21 Make font dark blue.
//  1.33 7/19/21 Make text italic
//  1.34 5/26/22 Make text red if ferry is delayed.
//  1.35 6/07/22 Detect a late ferry and add a LATE message

$ver = "1.35";  // 6/07/22
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

// get position
$fa = getposition();
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

// always display 'CA' before 'S2' when both are active.

if($pi==0) $pstr = "";
elseif($pi==1) $pstr = $px[0];
elseif(strpos($px[0], "'S2'") > 0) $pstr = $px[1] . "<br/>" . $px[0];  // always display CA before S2
else $pstr = $px[0] . "<br/>" . $px[1];
//echo $pstr;

// write to ferry position file

file_put_contents($ferrypositionfile, "<div style='font-family:sans-serif;font-size:smaller;color:darkblue'>Ferry $p</div>");  // html file for iframe

$ferrycolor = "darkblue";
$alert = file_get_contents($ferryalertfile);  // if the ferry is delayed, set the font color to red.
if(strpos($alert, "DELAYED:") > 0) $ferrycolor = "red";
$pstr = "<span style='color:$ferrycolor'>$pstr</span>";

//  calculate if ferry is late and add message
if(count($fa)==1) $pstr = checkforLateFerry() + $pstr;  // if 1 boat only, check for late
file_put_contents("ferryposition.txt", $pstr); // txt file for getalerts.php

// log it to csv file 

$tlh = fopen($log, 'a');
$s1 = ""; $s2 = "";
if($fa[0][0] == $MMSICA) {  // if 1st boat is CA
    $s1 =  implode(",", $fa[0]) . "," . $px[0];
    if(count($fa)>1) $s2 = implode(",", $fa[1]) . "," . $px[1];
    else $s2 = ",,,,,,,,,,,,";
} else  { // if 1st boat is S2
    if(strpos($px[0], "'S2'") < 0) {
        $t = $px[0]; $px[0] = $px[1]; $px[1] = $t;
    }
    $s2 =  implode(",", $fa[0]) . "," . $px[0];
    if(count($fa)>1) $s1 = implode(",", $fa[1]) . "," . $px[1];
    else $s1 = ",,,,,,,,,,,,";
}
date_default_timezone_set("America/Los_Angeles"); // set UTC
fwrite($tlh, date('c') . ",$ver,$s1,$s2," . round($deltamin,1) ."\n");
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
    global $MMSI, $lat, $long, $longAI, $longSt,$latAI,$latSt,$latKe,$longKe, $DAItoSt, $crossingtime, $course, $deltamin, $ferryport, $speed, $mi, $ri;
    global $ferrystate, $timetoarrival;

    $AItoSt = .074; // steilacoom to AI longitude
    $latKeIs = 47.1725; // Ketron course latitude flag. Just south of the Steilacoom dock
    $ketron = "";
    // if above the tip of ketron but headed SE, OR
    // if below the tip of Ketron, do a general stopping  with estimated arrival based on latitude, or leaving based on course
    if(($lat <= $latKeIs) || ($lat<47.177 && $long>-122.640 && $long<-122.624 && $course>100 && $course <180 )) { // if southerly westerly course, assume arriving.
    //if($lat <= $latKeIs) { 
        $ferrystate = "toST"; // travelling to steilacoom
        $timetoarrival = 10; // time to arrive in steilacoom
        if(($long<$longKe) || ($course>99 & $course < 340))  { // if southerly westerly course, assume arriving.
            $ri = "file_download";
            $t = floor(abs(($lat-$latKe)/(47.177-$latKe)) * 10);  // min left based on latitude left
            if($t <= 0) {
                $timetoarrival = 10; // time to arrival at steilacoom
                if($speed > 50) $t = 1;// if boat > 5 knts, give 1 more minute
                else return "docking at Ketron";
            }
            $timetoarrival = $t + 10; // time to arrival at steilacoom
            return "stopping @Ketron in $t min";
        }
        else $ketron = "leaving Ketron  ";
    }

    $ferryport = file_get_contents($MMSI); // get last ferry port

    // Default: if coming from S, it is headed to A, . Added 1.29 2/22/21
    // $courseto = destination port.  $ferryport = previous port
    if($ferryport=="S") $courseto = "A";
    else $courseto = "S";
    // override courseto when compass course is unambiguous
    if($course > 225 && $course < 351) $courseto = "A";  // heading to AI
    if($course > 35  && $course < 186) $courseto = "S";  // heading to steilacoom

    // override ferry port when close to steilacoom and course is headed to steilacoom.  Not used after 1.29
    //if($long > -122.615 && $long < -122.600 && $courseto == "S") $ferryport = "A";
    // override ferry port when course is opposite

    // Headed to AI
    //if($ferryport=="S" || ($course > 225 && $course < 340) ) { // last port was S, or headed to AI 
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
        if($ferryport == "A") return $ketron . "returning to AI in $t m";
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
                return "docking @Steilacoom";
            }
        }
        if($ferryport == "S") return $ketron . "returning to Steilacoom in $t m";    
        return $ketron . "arriving @Steilacoom in $t m";
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
    global $ferrystate, $timetoarrival;

    $latKeIs = 47.167; // north tip of ketron //$longKe = -122.6289;
    if($long > ($longAI-$longE) && $long < ($longAI+$longE))  {  
        // At AI
        file_put_contents($MMSI, "A");
        $ri = "font_download";
        $ferrystate = "atAI";  // at AI
        return "docked @AI";

    } elseif($long > ($longSt-$longE) && $long < ($longSt+$longE))  {
        // At Steilacoom
        file_put_contents($MMSI, "S");
        $ri = "home";
        $ferrystate = "atST";  // at steilacoom
        return "docked @Steilacoom";

    } elseif($long > ($longKe-.001) && $long < ($longKe+.002) && ($lat < $latKeIs) ) {
        // special case for monday morning ketron run that is steilacoom-ketron-steilacoom onlyh
        if($lt[2]>=16 && $lt[2]<=17) file_put_contents($MMSI, "A");  //  if 9am, always pretend it came from anderson so it will report returning to Steilacoom
        $ri = "do_not_disturb_on";
        $ferrystate = "toST"; // moving to ST;
        $timetoarrival = 10; // time to arrival at ST in minutes
        return "at Ketron";  // allow for extended docking

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

//////////////////////////////////////////////////////////////////////////////
// check for late - check for ferry being late and adds a message
//      Don't call if running 2 ferrys. Its too confusing.
//  entry   $ferrystate, 
//          $timetoarrival
//  exit    returns prefix to ferry message, or ""
function checkforLateFerry() {
    global $ferrystate, $timetoarrival;
    
    if($ferrystate=="") return "";  // unable to determine state;
    $traveltime = $timetoarrival; // time to travel AI-St or St-AI in minutes
    $loadtime = 5; // time to unload & load the ferry
    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $loctime = localtime();  // returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday
    $now = loctime[2] * 60 + loctime[1];  // local time in minutes since midnight.

    // All arithmetic is done in minutes since midnight.
    switch($ferrystate) {
        case "atST": // docked at ST
            $STtime = getTimeofNextRun($now, "ST");  // next run time minutes since midnight seconds. up to 20 minutes late for next run.
            if($now <= $STtime) return "";
            $delaytime = $now - $STtime;
            //if(($now + $traveltime + $loadtime) < $AItime) return ""; // if ferry will not be late
            //$delaytime = (($now + $traveltime + $loadtime) - $AItime)/60;  // calculate delay
            break;

        case "toAI": // travelling to AI
            $AItime = getTimeofNextRun($now, "AI"); // next run time minutes since midnight second
            if(($now + $traveltime + $loadtime) < $AItime) return ""; // if ferry will not be late
            $delaytime = (($now + $traveltime + $loadtime) - $AItime)/60;  // calculate delay
            break;

        case "atAI": // docked at AI
            $AItime = getTimeofNextRun($now, "AI");  // next run time minutes since midnight second
            if($now < $AItime) return ""; // if ferry will not be late
            $delaytime = $now - $AItime;  // calculate delay
            break;

        case "toST": // travelling to ST
            $STtime = getTimeofNextRun($now, "ST");  // next run time minutes since midnight second
            if(($now + $traveltime + $loadtime) < $STtime) return ""; // if ferry will not be late
            $delaytime = (($now + $traveltime + $loadtime) - $STtime)/60;  // calculate delay
            break;
    }

    // $delaytime = delay in minutes
    if ($delaytime <4) return "";
    return "<span style='color:red'>DELAYED $delaymin min.</span><br/>";
}


//////////////////////////////////////////////////////////////////////////////////
// getTimeofNextSTRun();  next run time in unix seconds. up to 30 minutes late for next run.
// Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
//
//  entry   $now = UTC timestamp seconds
//          $STAI = "AI" or "ST"
//  exit    returns time of scheduled run, as minutes since midnight: hh*60+mm
//  CAUTION: resets global timezone to PT
// 
function getTimeofNextRun ($now, $STAI)  {

    // this array also used in getferryoverflow.php
    $ST = array(445,545,705,820,930,1035,1210,1445,1550,1700,1810,1920,2035,2220); // ST departures
    $AI = array(515,620,735,855,1005,1110,1245,1515,1625,1735,1845,1955,2110,2250); // AI departures

    // convert UTC to PT
    // NOTE we probably can use the set timezone at this point since we are almost done

    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $utc = time(); // UTC time
    $loctime = localtime($utc - 30*60);  // Backup 30 min. returns array of local time in correct time zone. 1=min, 2=hours, 6=weekday
    $s = 0;
    if($loctime[6]>5) $s = 1; // skip early runs on sat, sun (d=-6, d=7)
    $lt = loctime[2] * 100 + loctime[1];  // local time in hhmm.

    if($STAI = "ST") {
        // loop through steilacoom
        for($i=$s; $i<count($ST); $i++){
            if($lt < $ST[i]) break;
        }
        return (floor($ST[i]/100)*60) + ($ST[i]%100);

    } else {
         // loop through AI
        for($i=$s; $i<count($AI); $i++){
            if($lt < $AI[i]) break;
        }
        return (floor($AI[i]/100)*60) + ($AI[i]%100);      
    }
    return 0;
}


}


?>