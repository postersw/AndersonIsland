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
//
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
    //echo $link; //debug
    //return 0;///////////////////////////////////////////////////////////

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
    // write to data file
    //echo $str; // debug

    $strout = reformatdata($str);  // reformat

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
        $lowtidetrigger = -0.5;
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
        $n = 0;

        // loop through tide predictions from NOAA
        foreach ($jreply->predictions as $tide) {
            if($n > 0) $str = $str . ",";
            $t = $tide->t;  //"2017-10-01 04:15" -> "2012-04-08T04:47:00-07:00"
            // convert to AERIS format for backward compatibility
            $str = $str . '{"dateTimeISO": "' . substr($t, 0, 10) . "T" . substr($t, 11, 5) . ':00-07:00",' .
                '"type": "' . strtolower($tide->type) . '", "heightFT": ' . number_format($tide->v, 1) . "}";
            
            // if today low tide < -1', create a warning message.  Added 9/1/22.
            if((intval($mtoday)==intval(substr($t,5,2))) && (intval($dtoday)==intval(substr($t,8,2)))) {  // if today
                //echo "today=$t "; // debug
                if($tide->type == "L") { // if low tide
                    if(floatval($tide->v) <= $lowtidetrigger) { // if <= -1' 
                        $hr = intval(substr($t, 11, 2)); // tide hour
                        if(($hr >5) && ($hr<23) && ($hr<= ($htoday+3))) { // if >5am and < 11pm  and less than 3 hours ago
                            $lowtidewarning = "<span style='color:red;font-weight:bold'>Ferry alert for trailers: " . number_format($tide->v, 1) . "' tide at " . timeampm(substr($t, 11,5)) . "</span><br/>";
                            echo $lowtidewarning; ///////DEBUG///////////
                        }
                    }
                }
            }
            $n = $n + 1;
        }
        $str = $str . "]}}";
        file_put_contents($lowtidefile, $lowtidewarning);
        return $str;
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