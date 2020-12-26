<?php
//////////////////////////////////////////////////////////////////////////////
// getferrypositioncron.php - retrieves current ferry position and calculates an eta.
// leaves the results in <ferryposition.txt> file.  This will be picked up by the getalert script
// which will tuck it into the FERRY/FERRYEND alert messages.
// Ferry position is retrieved from marinetraffic.com.  
//
//  Robert Bedoll. 12/26/20.  
//
$longAI = -122.677; $latAI = 47.17869;   // AI Dock
$longSt = -122.603; $latSt = 47.17347;  // Steilacoom Dock
$longKe = -122.6293; $latKe = 47.162233; // ketron dock
$longE = -.0004;  $latE = .001; // epselon
$ferrypositionfile = "ferryposition.txt";
$crossingtime = 20; // nominal crossing time in minutes

// instantanious position retrieved from maringtraffic.com
$speed = 0;
$lat = 0;
$long = 0;
$course = 0;
$timestamp = 0;

// start
echo "started <br/>";
getposition();
// if ($timestamp diff > 5 min) clear file;
if($speed < 3) $p = reportatdock();  // if LT 2 knots report at dock
else $p = timetocross();
$p = "Ferry " . $p;
echo "$p <br/>"; // debug
// write to ferry position file
put_file_contents($ferrypositionfile, $p);
return;

////////////////////////////////////////////////////////////////////////
// timetocross compute and return remaining time to AI
//  entry   boat in route to AI
//  returns remaining time
function timetocross() {
    global $lat, $long, $longAI, $longSt, $crossingtime, $course;
    $AItoSt = .074; // steilacoom to AI longitude
    // note when long is outside of the docking zone and speed is not > 10, make crossing time slower
    if($course > 180) { 
        $t = (($longAI-$long)/ $AItoSt ) * $crossingtime;
        return " arriving @AI in $crossingtime min.";
    } else {
        $t = (($long-$longSt)/ $AItoSt ) * $crossingtime;
        return " arriving @Steilacoom in $crossingtime min.";
    }
}


/////////////////////////////////////////////////////////////////////////
// reportatdock - reports the dock position
//  exit    returns at AI, at Steilacoom, or At Ketron based only on longditude
function reportatdock() {
    global $lat, $long, $longAI, $longSt, $longKe, $longE;
    $latKeIs = 47.167; // north tip of ketron
    if($long < ($longAI-$longE) && $long > ($longAI+$longE))  return "at Anderson Is";
    else if($long < ($longSt-$longE) && $long > ($longSt+$longE))  return "at Steilacoom";
    else if($long < ($longKe-$longE) && $long > ($longKe+$longE) && ($lat) < $latKeIs)  return "at Ketron";
    else return "stopped at $lat, $long";
}


///////////////////////////////////////////////////////////////
//  getposition - get the ferry position from marinetraffic.com
//  exit - sets globals speed, lat, long, course, timestamp
function getposition() {
    global $lat,$long,$speed,$course,$timestamp;
    $link = "https://services.marinetraffic.com/api/exportvessel/v:5/de761ab1234199b5f2f88ac76452ad66a512bd20/timespan:100/mmsi:367153930/protocol:csv";
    $data = GetData($link);
    if($data == "") {
        unlink($ferrypositionfile);
        exit("no data");
    }
    $a = explode(",",$data); // unpack CSV data into array
    $lat = $a[9];
    $long = $a[10];
    $speed = $a[11];
    $course = $a[13];
    $timestamp = $a[15];
    // debug
    echo "lat=$lat, long=$long, speed=$speed, course=$course, timestamp=$timestamp <br/>";
}

///////////////////////////////////////////////////////////////////////////////
// Get data after 10 tries
//  Entry   link = address
//  returns data. 
//      if no data, it deletes ferrypositionfile.
function GetData($link) {
    global $ferrypositionfile;
    for ($x = 0; $x <= 2; $x++) {
        $str = "";
        //echo " GetData $x. ";
        $str = file_get_contents($link);
        //echo "str=$str";
        if($str != false && $str != "") {return $str;}
        sleep(10);
        echo " GetData Try $x. ";
    }
    echo ("<br/>getferryposition cron run: NO marinetraffic.com DATA after 2 tries for $link<br/>");
    return "";
}
?>