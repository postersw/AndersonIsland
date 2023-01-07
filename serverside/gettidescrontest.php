<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  gettidecron - gets the tides json structure from tidesandcurrents.noaa.gov
//  web site and writes it to tidedata.txt in 'aeris' format.
//  this file is included into 'dailycache.txt' by a <include directive. 
//
//  Called by cron every 6 hours (4 times/day). 
//  AERIS format:
//  {"success":true,"error":null,"response":{"id":"9446705",
//  "periods": [ {"dateTimeISO": "2012-04-08T04:47:00-07:00","type": "l", "heightFT": -0.3},...]
//  returned in local standard/daylight time.
//
//  rfb. 6/4/16. 6/6/16.
//  rfb. 10/1/17. Call NOAA instead of Aeris because Aeris is no longer free.  But format the return to look like Aeris.
//  rfb. 9/3/22.  Check for tides<-0.5' and issue a warning message to lowtidewarninginclude.txt.  This file is pickedup by in include in getdailycache.txt.
//  rfb. 9/6/22.  Change file to lowtideswarninginclude.
//  rfb. 12/17/22. Add hightidewarning to 'lowtidewarninginclude.txt' if tide >= 14.5'. 
//  rfb. 1/6/23.  Calculate low tide at ferry run times for the low tide warning.
//
    $SAVED = [];  // saved data
    $debug = true; // true for debug print
    $file = "tidedata.txt";
    $lowtidefile = "lowtidewarninginclude.txt";
    chdir("/home/postersw/public_html");  // move to web root
    date_default_timezone_set('America/Los_Angeles');

    // code for NOAA

    $link = "https://tidesandcurrents.noaa.gov/api/datagetter?station=9446705&product=predictions&units=english&time_zone=lst_ldt&application=ports_screen&format=json&datum=MLLW&interval=hilo";
    $timetoday = time();
    $mtoday = date("m", $timetoday);  // month today
    $dtoday = date("d", $timetoday);  // day today
    $htoday = date("H", $timetoday);  // hour, 24 hour format
    $ts = time() - (3600*24);  // back up 1 day   unix time stamp
    $y = date("Y", $ts); // year, e.g. 2017   local time
    $m = date ("m", $ts); // month with leading zero
    $d = date("d", $ts); // day with leading zero
    $link = $link . "&begin_date=" . date("Ymd%20H:i", $ts) . "&range=200";

    if($debug) echo "today=$mtoday/$dtoday $htoday, link=$y/$m/$d<br>";


    // try 10 times to get the noaa tide data
    for ($x = 0; $x <= 10; $x++) {
        $str = "";
        $str = file_get_contents($link);
        if($str != "") break;
    }
    if($str == "") {  // if no data
        echo("tide cron run: NO NOAA DATA after 10 tries!!!");
        return 0;
    }
    // check for success
    $j = strpos($str, '{ "predictions" : [');
    if($j < 0) {  // if not success
        echo("tide cron run: NOAA ERROR !!!\n $str");
        return 0;
    }

    // write data to tidedata.txt file
    $strout = reformatdata($str);  // reformat to aeris format
    $j = file_put_contents($file, $strout);  // save the data
    if($j <= 0) {  // if not success
        echo("tide cron run: file_put_contents ERROR !!!\n $j $strout");
        return 0;
    }
    //DEBUG. echo("NOAA tide cron run successful:\n $strout");
    return 0;


/////////////////////////////////////////////////////////////////////////////////////////
// reformatdata - reformat data from NOAA format to AERIS format to keep AIA v 1.15 and prior working.
//                and issues low tide warning message.
//  because on 10/1/17, AERIS is no longer free. So I have switched to NOAA, but have not changed the AIA app.
// entry: str = noaa tide data in NOAA JSON format
// exit: returns tide data in AERIS JSON format to keep AIA happy.
//       Writes low tide warning message to "lowtidewarning.txt"  (9/3/22)
//
// All times are local tide PST/PDT - NOT UTC
// NOAA format (input):
//  { "predictions" : [ {"t":"2017-10-01 04:15", "v":"6.077", "type":"L"},{"t":"2017-10-01 09:30", "v":"10.284", "type":"H"},{"t":"2017-10-01 16:00", "v":"1.774", "type":"L"},{"t":"2017-10-01 22:49", "v":"12.398", "type":"H"},{"t":"2017-10-02 05:00", "v":"5.162", "type":"L"},{"t":"2017-10-02 10:25", "v":"10.911", "type":"H"},{"t":"2017-10-02 16:50", "v":"1.536", "type":"L"},{"t":"2017-10-02 23:26", "v":"12.731", "type":"H"},{"t":"2017-10-03 05:39", "v":"4.179", "type":"L"},{"t":"2017-10-03 11:13", "v":"11.589", "type":"H"},{"t":"2017-10-03 17:35", "v":"1.410", "type":"L"},{"t":"2017-10-03 23:59", "v":"13.024", "type":"H"},{"t":"2017-10-04 06:15", "v":"3.163", "type":"L"},{"t":"2017-10-04 11:58", "v":"12.221", "type":"H"},{"t":"2017-10-04 18:16", "v":"1.477", "type":"L"},{"t":"2017-10-05 00:29", "v":"13.284", "type":"H"},{"t":"2017-10-05 06:51", "v":"2.142", "type":"L"},{"t":"2017-10-05 12:42", "v":"12.752", "type":"H"},{"t":"2017-10-05 18:56", "v":"1.776", "type":"L"},{"t":"2017-10-06 00:59", "v":"13.508", "type":"H"},{"t":"2017-10-06 07:28", "v":"1.154", "type":"L"},{"t":"2017-10-06 13:27", "v":"13.149", "type":"H"},{"t":"2017-10-06 19:37", "v":"2.309", "type":"L"},{"t":"2017-10-07 01:30", "v":"13.669", "type":"H"},{"t":"2017-10-07 08:07", "v":"0.261", "type":"L"},{"t":"2017-10-07 14:14", "v":"13.391", "type":"H"},{"t":"2017-10-07 20:19", "v":"3.049", "type":"L"},{"t":"2017-10-08 02:03", "v":"13.715", "type":"H"},{"t":"2017-10-08 08:48", "v":"-0.451", "type":"L"},{"t":"2017-10-08 15:05", "v":"13.463", "type":"H"},{"t":"2017-10-08 21:04", "v":"3.941", "type":"L"} ]}
//  predictions is an array of t (time), v (height in feet), type (L or H)
// AERIS format (output):
//  {"id": "8723165", "loc": {"long": -80.185,"lat": 25.7783  },  "place": {"name": "miami miamarina, biscayne bay","state": "fl", "country": "us"  },
//  "periods": [ {"timestamp": 1333874820,"dateTimeISO": "2012-04-08T04:47:00-04:00","type": "l", "heightFT": -0.3, "heightM": -0.09},...]

    function reformatdata($reply) {
        global $lowtidefile, $mtoday, $dtoday, $htoday;
        global $debug;
        $lowtidetrigger = -0.5; // low tide trigger in feet
        $hightidetrigger = 14.6; // high tide trigger in feet
        //echo $reply . "<br/>"; //debug//
        $jreply = json_decode($reply);  // decode the json reply
        //var_dump($jreply);
        //echo count($jreply->predictions) . " items. <br/>";

        // if an error
        if (is_null($jreply) || (count($jreply->predictions) == 0)) {
            echo "ERROR - no tides returned. \n";
            die( "ERROR - no tides returned.");
        }
        $str = '{"success":true,"error":null,"response":{"id":"9446705","periods":[';
        $lowtidewarning = "";
        $hightidewarning = "";
        $n = 0;

        // loop through tide predictions from NOAA
        foreach ($jreply->predictions as $tide) {
            if($n > 0) $str = $str . ",";
            $t = $tide->t;  //"2017-10-01 04:15" -> "2012-04-08T04:47:00-07:00"
            // convert to AERIS format for backward compatibility
            $str = $str . '{"dateTimeISO": "' . substr($t, 0, 10) . "T" . substr($t, 11, 5) . ':00-07:00",' .
                '"type": "' . strtolower($tide->type) . '", "heightFT": ' . number_format($tide->v, 1) . "}"; 
            if($debug) echo "tide time=$t<br>";
            // check for extreme low or high tides TODAY
            if((intval($mtoday)==intval(substr($t,5,2))) && (intval($dtoday)==intval(substr($t,8,2)))) {  // if today
                switch ($tide->type){
                    // if today low tide < -.5', create a warning message.  Added 9/1/22
                    case "L":  // if low tide
                        $lowtide = floatval($tide->v);
                        $lowtidetime = intval(substr($t, 11, 2)) * 100 + intval(substr($t, 14, 2)); // low tide in hhmm
                        echo "lowtide=$lowtide<br>";
                        if($lowtide <= $lowtidetrigger) { // if <= -1' 
                            $hr = intval(substr($t, 11, 2)); // tide hour
                            $lowtidewarning = FerryTrailerAlert($hr, $hightide, $hightidetime, $lowtide, $lowtidetime);
                        }
                        break;
                    // if today high tide > 14.5', create a warning
                    case "H": // if high tide issue a warning
                        $hightide = floatval($tide->v);
                        $hightidetime = intval(substr($t, 11, 2)) * 100 + intval(substr($t, 14, 2)); // high tide in hhmm
                        if($hightide >= $hightidetrigger) { // if >= 14.5' 
                            $hr = intval(substr($t, 11, 2)); // tide hour
                            if(($htoday<=($hr+2))) { // if  less than 2 hours ago
                                $hightidewarning = "<span style='color:black;font-weight:bold'>High tide alert: " . number_format($tide->v, 1) . "' tide at " . timeampm(substr($t, 11,5)) . "</span><br/>";
                                echo $hightidewarning; echo "hr=$hr, htoday=$htoday ";
                            }
                        }
                        break;
                }
            }
            
            $n = $n + 1;
        }
        $str = $str . "]}}";
        file_put_contents($lowtidefile, $lowtidewarning . $hightidewarning);  // issue low & high tide warnings
        return $str;
    }


//////////////////////////////////////////////////////////////////////////////////
//  FerryTrailerAlert - issue alert if low tides for ferry run
//  Find the ferry run time for 2 hours on each side of the low tide time,
//    and calculate the tide for each ferry run time.  If < 0.5 feet, issue a warning
//    with the ferry run time and the tide at that time.
//  entry   $hr = hour of the day for low tide warning
//  exit    returns ferry low tide warning message
//
// function FerryTrailerAlert($hr, $hightide, $hightidetime, $lowtide, $lowtidetime){
//     global $lowtidefile, $mtoday, $dtoday, $htoday;
//     global $debug;
//     $lowtidetrigger = -0.5; // low tide trigger in feet
//     $lowtidewarning = "";
//     //$hr = intval(substr($t, 11, 2)); // tide hour
//     if(($hr >5) && ($hr<23) && ($htoday<=($hr+3))) { // if >5am and < 11pm  and less than 3 hours ago
//         //  loop through all hours
//         for($h=$hr-2;$h<$hr+3; $h++) {
//             //  Steilacoom 
//             $STft = getTimeofNextRun("ST", $h*100);  // read ferry schedule
//             if($debug) echo "h=$h, STf=$STft <br>";
//             if($STft > 0 && $oldSTft != $STft) {  // if a different next run
//                 $oldSTft = $STft;
//                 // Calculate Current Tide Height(next, previous ...)
//                 if($STft>$lowtidetime) $STtide = CalculateCurrentTideHeight($hightidetime,$lowtidetime, $hightide,  $lowtide, $STft);  // kludge - reverse high and lowto get right side of curve 
//                 else $STtide = CalculateCurrentTideHeight($lowtidetime, $hightidetime, $lowtide, $hightide,  $STft);
//                 if($debug) echo "STtide = $STtide<br>";
//                 if($STtide<$lowtidetrigger) {
//                     $lowtidewarning .= "<span style='color:red;font-weight:bold'>Ferry alert for trailers: " . number_format($STtide, 1) . "' tide for ST " . ShortTime($STft) . " run</span><br/>";
//                     echo $lowtidewarning; echo "hr=$hr, htoday=$htoday ";
//                 }
//             }
//             // Anderson Island
//             $AIft = getTimeofNextRun("AI", $h*100);  // read ferry schedule
//             if($debug) echo "h=$h, AIft=$AIft <br>";
//             if($AIft> 0 && $oldAIft != $AIft) {
//                 $oldAIft = $AIft;
//                 // Calculate Current Tide Height(next, previous ...)
//                 if($AIft>$lowtidetime) $AItide = CalculateCurrentTideHeight($hightidetime,$lowtidetime, $hightide,  $lowtide, $AIft);  // kludge - reverse high and lowto get right side of curve
//                 else $AItide = CalculateCurrentTideHeight($lowtidetime, $hightidetime, $lowtide, $hightide,  $AIft);
//                 if($debug) echo "AItide = $AItide<br>";
//                 if($AItide<$lowtidetrigger) {
//                     $lowtidewarning .= "<span style='color:red;font-weight:bold'>Ferry alert for trailers: " . number_format($AItide, 1) . "' tide for AI " . ShortTime($AIft) . " run</span><br/>";
//                     echo $lowtidewarning; echo "hr=$hr, htoday=$htoday ";
//                 }
//             }
//         }
//     }
//     return $lowtidewarning;
// }

//////////////////////////////////////////////////////////////////////////////////
//  FerryTrailerAlert - issue alert if low tides for ferry run
//  Find the ferry run time for 2 hours on each side of the low tide time,
//    and calculate the tide for each ferry run time.  If < 0.5 feet, issue a warning
//    with the ferry run time and the tide at that time.
//  entry   $hr = hour of the day for low tide warning
//  exit    returns ferry low tide warning message
//
//  uses ferry schedule in dailycache.txt:
// 445;(gDayofWeek>0)&&(gDayofWeek<6)&&!InList(gMonthDay,1231,101,221,memorialday,619,704,laborday,1111,thanksgiving,thanksgiving+1,1224,1225);
// 545;*;
// 705;*;
// 820;*;
// 930;(!(gDayofWeek==1&&(gWeekofMonth==1||gWeekofMonth==3)));
// 1035;*;
// 1210;*;
// 1230;(InList(gMonthDay,memorialday-3,memorialday-1,704))||((InList(gDayofWeek,5,0,1)&&(gMonthDay>=817)&&(gMonthDay<(906))));
// 1445;*;
// 1515;(InList(gMonthDay,memorialday-3,memorialday-1,704))||((InList(gDayofWeek,5,0,1)&&(gMonthDay>=817)&&(gMonthDay<(906))));
// 1550;*;
// 1625;(InList(gMonthDay,memorialday-3,memorialday-1,704))||((InList(gDayofWeek,5,0,1)&&(gMonthDay>=817)&&(gMonthDay<(906))));
// 1700;*;
// 1735;(InList(gMonthDay,memorialday-3,memorialday-1,704))||((InList(gDayofWeek,5,0,1)&&(gMonthDay>=817)&&(gMonthDay<(906))));
// 1810;*;
// 1920;*;
// 2035;*;
// 2220;(!InList(gMonthDay,1015,1113))
//
function FerryTrailerAlert($hr, $hightide, $hightidetime, $lowtide, $lowtidetime){
    global $lowtidefile, $mtoday, $dtoday, $htoday;
    global $debug;
    $lowtidetrigger = -0.5; // low tide trigger in feet
    $lowtidewarning = "";
    if($hr < 5) return ""; // if low tide is before 5 am
    if($htoday > ($hr+3)) return ""; // if > 3 hours past low tide
    $f = file_get_contents("dailycache.txt");  // read daily cache
    $STschedule = getschedule($f, "FERRYTS");  // get the schedule
    $AIschedule = getschedule($f, "FERRYTA");
    $ST = explode(";", $STschedule); //create array of run time, expression
    $AI = explode(";", $AIschedule); //create array
    $hmin = ($hr-3)*100;  // min and max time as hh00
    $hmax = ($hr+3)*100;
    if($debug) echo "ferrytraileralert hmin=$hmin, hmax=$hmax<br>";
    CalcDays();     // eval rules setup

    // loop through ferry run times and find each scheduled run that falls within the low tide window of low tide time +- 3 hrs.
    for($i=0; $i<count($ST); $i=$i+2){
        // Steilacoom
        $runtime = intval($ST[$i]);  // ferry run time
        if($hmin < $runtime  && $runtime < $hmax) {  // if ferry run is within the low tide window
            if($debug) echo " ST run = {$ST[$i]}<br>";  // debug
            if(ValidFerryRun($ST[$i+1])) {  // if this ferry run is valid
                if($runtime > $lowtidetime) $STtide = CalculateCurrentTideHeight($hightidetime,$lowtidetime, $hightide,  $lowtide, $runtime);  // kludge - reverse high and lowto get right side of curve 
                else $STtide = CalculateCurrentTideHeight($lowtidetime, $hightidetime, $lowtide, $hightide,  $runtime);
                if($debug) echo "STtide = $STtide<br>";
                if($STtide < $lowtidetrigger) {
                    $lowtidewarning .= "<span style='color:red;font-weight:bold'>Ferry alert for trailers: " . number_format($STtide, 1) . "' tide for ST " . ShortTime($runtime) . " run</span><br/>";
                    echo $lowtidewarning; echo "hr=$hr, htoday=$htoday <br>";
                }
            }
        }
        // Anderson Island
        $runtime = intval($AI[$i]);
        if($hmin < $runtime  && $runtime < $hmax) {  // if ferry run is within the low tide window
            if($debug) echo " AI run = {$AI[$i]}<br>";  // debug
            if(ValidFerryRun($AI[$i+1])) {  // if this ferry run is valid
                if($runtime > $lowtidetime) $AItide = CalculateCurrentTideHeight($hightidetime,$lowtidetime, $hightide,  $lowtide, $runtime);  // kludge - reverse high and lowto get right side of curve 
                else $AItide = CalculateCurrentTideHeight($lowtidetime, $hightidetime, $lowtide, $hightide,  $runtime);
                if($debug) echo "AItide = $AItide<br>";
                if($AItide < $lowtidetrigger) {
                    $lowtidewarning .= "<span style='color:red;font-weight:bold'>Ferry alert for trailers: " . number_format($AItide, 1) . "' tide for AI " . ShortTime($runtime) . " run</span><br/>";
                    echo $lowtidewarning; echo "hr=$hr, htoday=$htoday <br>";
                }
            }
        }
    }
    return $lowtidewarning;
}

///////////////////////////////////////////////////////////////////////////
//  ShortTime - convert hhmm to hh:mm string
//  entry   $ft = time in hhmm
//  exit    returns time as string hh:mm a/p
function ShortTime($ft) {
    if ($ft < 1199) $ampm = "a";
    else $ampm = "p";
    if ($ft < 100) return "12:" . (ft % 100) . $ampm;
    else if ($ft < 1299) return (floor($ft / 100)) . ":" . strval($ft % 100) + $ampm;
    else return (floor($ft / 100) - 12) .  ":" . strval($ft % 100) . $ampm;
}

//////////////////////////////////////////////////////////////////////////////
//  timeampm - convert time to am/pm
//  entry   $t = string: hh:mm in 24 hr time.
//  exit    string: hh:mm am|pm
function timeampm($t) {
    $h = intval(substr($t, 0, 2)); // get the hour
    $ap = "am";
    if($h > 11 ) $ap = "pm";
    if($h > 12) $h = $h - 12;
    return $h . ":" . substr($t,3, 2) . $ap;
}

////////////  Use Dailycache.txt 12/6/22. ////////////////////////////////////////////////////////////////////////////////////////////////////////

$gtimestamp = 0;
$gDayofWeek = 0;
$gDayofMonth = 0;
$gMonthDay = 0;
$gWeekofMonth = 0;
//////////////////////////////////////////////////////////////////////////////////
// getTimeofNextRun();  
// Returns time of next run, using the ferry times in dailycache.txt.
//  That way dailycache.txt rules are used by the AndersonIslandAssistant AND this code.
//  Once each half hour, this will read the schedule from dailycache.txt, extract the ferry schedule, and evaluate it
//  to determine the next scheduled run.  Based on the code in index.js in the app.
// 
// next run time in unix seconds. up to 30 minutes late for next run.
// Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
//
//  entry   $STAI = "AI" or "ST"
//          $lt = time as hhmm   (h*100+m)
//  exit    returns time of scheduled run, as hhmm.  0 if no run.
//  NOTE: this looks for the next run based on current time -30.  This makes up for a run being up to
//    30 minutes late.  After 30 minutes late it will find the next run.
// function getTimeofNextRun($STAI, $lt)  {
//     global $gtimestamp, $gDayofWeek, $gDayofMonth, $gMonthDay, $gWeekofMonth;
//     global $SAVED;  // persistent data
//     global $debug;

//     $dailycache = "dailycache.txt";
//     if($debug) echo "lt=$lt <br>";//DEBUG

//     //  Steilacoom
//     if($STAI == "ST") { // if Steilacoom
//         $nextSTRun = intval($SAVED['NextSTRun']); // last saved time
//         //echo " Saved nextSTRun = $nextSTRun<br>";
//         if(($lt<$nextSTRun) && ($nextSTRun-$lt<800)) return $nextSTRun;  // return min since midnight
//         if($nextSTRun==0 && $lt>2100) return 0; // after 9pm a return of 0 for last run is ok.
// 	    $STschedule = getschedule($dailycache, "FERRYTS");  // get the schedule
//         if($debug) echo "---ST Schedule read.<br>";  // DEBUG
// 	    $ST = explode(";", $STschedule); //create array
//         // loop through steilacoom and find the next scheduled run
//         for($i=0; $i<count($ST); $i=$i+2){
//             if($lt < intval($ST[$i])) {
//                 if($debug) echo " ST found = {$ST[$i]}<br>";  // debug
// 			    if(ValidFerryRun($ST[$i+1]))break;
// 		    }
//         }
//         if($i == count($ST)) $nextSTRun = 0; // if past last run, return 0
//         else $nextSTRun = intval($ST[$i]);
//         $SAVED['NextSTRun'] = $nextSTRun;  // save it
//         if($debug) echo "ST Local time-30m=$lt. Next Run=$nextSTRun ----------------------<br>"; // DEBUG
//         return $nextSTRun; // convert hhmm to min since midnight

//     } else {

//         //  Anderson Island
//         $nextAIRun = intval($SAVED['NextAIRun']); // last saved time
//         if($debug) echo " Saved nextAIRun = $nextAIRun<br>";
//         if(($lt<$nextAIRun)&& ($nextAIRun-$lt<800)) return $nextAIRun;  // return min since midnight
//         if($nextAIRun==0 && $lt>2100) return 0; // after 9pm a return of 0 for last run is ok.
//         $AIschedule = getschedule($dailycache, "FERRYTA");
//         if($debug) echo "---AI schedule read<br>";  // DEBUG
// 	    $AI = explode(";", $AIschedule); //create array
//         // loop through AI
//         for($i=0; $i<count($AI); $i=$i+2){
//            if($lt < intval($AI[$i])) {
//                 if(ValidFerryRun($AI[$i+1]))break;
//            }
//         }
//         if($i == count($AI)) $nextAIRun = 0;
//         else $nextAIRun = intval($AI[$i]);  // save it
//         $SAVED['NextAIRun'] = $nextAIRun;
//         if($debug) echo "$STAI Local time-30m $lt. Next Run =$nextAIRun ------------------------<br>"; // DEBUG
//         return $nextAIRun;     
//    }
//    return 0;
// }

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
// CakcDays - calculate the special days used in the ferry eval rules
function CalcDays() {
    global $gtimestamp, $gDayofWeek, $gDayofMonth, $gMonthDay, $gWeekofMonth;
    $gtimestamp = time(); 
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
// GetSchedule - read dailycache.txt and extract the ferry schedule, which is one line
//  entry   f = dailycache.txt contents
//          keyword to find
//  exit    schedule string
function GetSchedule($f, $keyword) {
    //$f = file_get_contents($file);
    if($f===false) die("could not read $file");
    $i = strpos($f, $keyword);
    if($i < 1) die("no $keyword in file string");
    $i += strlen($keyword) + 1;  // skip keyword
    $j = strpos($f, "\n", $i);
    if($j < $i) die ("no end of line in file string");
    return substr($f, $i, $j-$i); // return the substring
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// Calculate current tide height using cosine - assumes a 1/2 sine wave between high and low tide
//  copied from index.js.
// entry: $t2 = next hi/low tide time as hhmm
//        $t1 = previous hi/low tide time as hhmm
//        $tide2, $tide1 = next and previous tide heights;
//        $targettime = time to compute the current tide height for
//  returns current tide height
function CalculateCurrentTideHeight($t2, $t1, $tide2, $tide1, $targettime) {
    $td = RawTimeDiff($t1, $t2);
    $cd = RawTimeDiff($t1, $targettime);
    $c = $cd / $td * pi();
    $c = cos(pi() - $c);// cos(PI to 0) = -1 to 1
    $tide = (($tide2 + $tide1) / 2) + (($tide2 - $tide1) / 2) * $c;
    echo "CalcTideHeight t2=$t2, t1=$t1, tide2=$tide2, tide1=$tide1, targettime=$targettime, tide=$tide<br>";
    return $tide;
}
////////////////////////////////////////////////////////////////////////////////////
// RawTimeDiff returns the time difference in minutes; hhmm2 - hhmm1
//  copied from index.js.
function RawTimeDiff($hhmm1, $hhmm2) {
    $tm = (floor($hhmm1 / 100) * 60) + ($hhmm1 % 100); // time in min
    $ftm = (floor($hhmm2 / 100) * 60) + ($hhmm2 % 100);
    if ($ftm < $tm) $ftm = $ftm + 24 * 60;
    return $ftm - $tm; // diff in minutes
}

//////////////////////////////////////////////////////////////////////////////////
// OLD AERIS CODE.  DEACTIVATED 10/1/17.
//$link = "http://api.aerisapi.com/tides/9446705?client_id=pSIYiKH6lq4YzlsNY54y0&client_secret=vMb1vxvyo7Z96DSn7niwxVymzOxPN6qiEEdBk7vS&from=-15hours&to=+8days";
//$str = file_get_contents($link);
//if($str == "") file_get_contents($link);  // try is 2nd time if 1st one fails
//if($str == "") {  // if no data
//    echo("tide cron run: NO AERIS DATA !!!");
//    //return 0;
//}
// check for success
//$j = strpos($str, '"success":true');
//if($j < 0) {  // if not success
//    echo("tide cron run: ERROR !!!\n $str");
//return 0;
//}
// write to data file
//file_put_contents($file, $str);  // save the data

?>