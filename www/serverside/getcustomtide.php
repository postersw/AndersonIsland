<?php
/////////////////////////////////////////////////////////////
//  getcustomtide - gets the tides for a user specified date.
//  gets the tide tables from NOAA, reformats it into the AERIS format that AIA understands, and return it.
//  Added 10/2/17 because Aeris is no longer free.
//  Parameters: &from=mm/dd/yyyy&to=+48hours
//  NOTE: Requires dailycache.txt to contain
//          CUSTOMTIDELINK
//          http://www.anderson-island.org/getcustomtide.php?
//
//  AERIS return format for backward compatability:
//  {"success":true,"error":null,"response":{"id":"9446705",
//  "periods": [ {"dateTimeISO": "2012-04-08T04:47:00-07:00","type": "l", "heightFT": -0.3},...]
//  returned in local standard/daylight time.
//
//  rfb. 10/2/17.

    // code for NOAA

    $link = "https://tidesandcurrents.noaa.gov/api/datagetter?station=9446705&product=predictions&units=english&time_zone=lst_ldt&application=ports_screen&format=json&datum=MLLW&interval=hilo";

    $d = $_GET["from"];  // start date
    if(!preg_match('/^\d\d?\/\d\d?\/20\d\d$/', $d)) {  // prevent bad things in the date.
        echo "invalid date";
        return 0;
    }
    $ts = strtotime($d);  // convert date string to a unix time stamp (seconds since 1970).
    $link = $link . "&begin_date=" . date("Ymd%20H:i", $ts) . "&range=72";
    //echo $link ."<br/>"; //debug

    // read the data from NOAA. Try 10 times. Then give up.
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
    //echo $str . "<br/>"; ///////// DEBUG/

    $strout = reformatdata($str);  // reformat

    echo $strout;  // return the data
    return 0;

/////////////////////////////////////////////////////////////////////////////////////////
// reformatdata - reformat data from NOAA format to AERIS format to keep AIA v 1.15 and prior working.
//  because on 10/1/17, AERIS is no longer free. So I have switched to NOAA, but have not changed the AIA app.
// entry: str = noaa tide data in NOAA JSON format
// exit: returns tide data in AERIS JSON format to keep AIA happy.
// NOAA format:
//  { "predictions" : [ {"t":"2017-10-01 04:15", "v":"6.077", "type":"L"},{"t":"2017-10-01 09:30", "v":"10.284", "type":"H"},{"t":"2017-10-01 16:00", "v":"1.774", "type":"L"},{"t":"2017-10-01 22:49", "v":"12.398", "type":"H"},{"t":"2017-10-02 05:00", "v":"5.162", "type":"L"},{"t":"2017-10-02 10:25", "v":"10.911", "type":"H"},{"t":"2017-10-02 16:50", "v":"1.536", "type":"L"},{"t":"2017-10-02 23:26", "v":"12.731", "type":"H"},{"t":"2017-10-03 05:39", "v":"4.179", "type":"L"},{"t":"2017-10-03 11:13", "v":"11.589", "type":"H"},{"t":"2017-10-03 17:35", "v":"1.410", "type":"L"},{"t":"2017-10-03 23:59", "v":"13.024", "type":"H"},{"t":"2017-10-04 06:15", "v":"3.163", "type":"L"},{"t":"2017-10-04 11:58", "v":"12.221", "type":"H"},{"t":"2017-10-04 18:16", "v":"1.477", "type":"L"},{"t":"2017-10-05 00:29", "v":"13.284", "type":"H"},{"t":"2017-10-05 06:51", "v":"2.142", "type":"L"},{"t":"2017-10-05 12:42", "v":"12.752", "type":"H"},{"t":"2017-10-05 18:56", "v":"1.776", "type":"L"},{"t":"2017-10-06 00:59", "v":"13.508", "type":"H"},{"t":"2017-10-06 07:28", "v":"1.154", "type":"L"},{"t":"2017-10-06 13:27", "v":"13.149", "type":"H"},{"t":"2017-10-06 19:37", "v":"2.309", "type":"L"},{"t":"2017-10-07 01:30", "v":"13.669", "type":"H"},{"t":"2017-10-07 08:07", "v":"0.261", "type":"L"},{"t":"2017-10-07 14:14", "v":"13.391", "type":"H"},{"t":"2017-10-07 20:19", "v":"3.049", "type":"L"},{"t":"2017-10-08 02:03", "v":"13.715", "type":"H"},{"t":"2017-10-08 08:48", "v":"-0.451", "type":"L"},{"t":"2017-10-08 15:05", "v":"13.463", "type":"H"},{"t":"2017-10-08 21:04", "v":"3.941", "type":"L"} ]}
//  predictions is an array of t (time), v (height in feet), type (L or H)
// AERIS format:
//  {"id": "8723165", "loc": {"long": -80.185,"lat": 25.7783  },  "place": {"name": "miami miamarina, biscayne bay","state": "fl", "country": "us"  },
//  "periods": [ {"timestamp": 1333874820,"dateTimeISO": "2012-04-08T04:47:00-04:00","type": "l", "heightFT": -0.3, "heightM": -0.09},...]

    function reformatdata($reply) {
        $jreply = json_decode($reply);  // decode the json reply
        //var_dump($jreply);
        //echo count($jreply->predictions) . " items. <br/>";

        // if an error
        if (count($jreply->predictions) == 0) {
            echo "ERROR - no tides returned. \n";
            die( "ERROR - no tides returned.");
        }
        $str = '{"success":true,"error":null,"response":{"id":"9446705","periods":[';
        $n = 0;
        foreach ($jreply->predictions as $tide) {
            if($n > 0) $str = $str . ",";
            $t = $tide->t;  //"2017-10-01 04:15" -> "2012-04-08T04:47:00-07:00"
            $str = $str . '{"dateTimeISO": "' . substr($t, 0, 10) . "T" . substr($t, 11, 5) . ':00-07:00",' .
                '"type": "' . strtolower($tide->type) . '", "heightFT": ' . number_format($tide->v, 1) . "}";
            $n = $n + 1;
        }
        $str = $str . "]}}";

        return $str;
    }

?>