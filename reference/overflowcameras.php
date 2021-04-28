<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  overflowcameras.php  display the overflow cameras for lanes and dock
//  overflowcameras.php?f=Adhhmm         where A = A or S, d = 1-7, hhmm = 24 hr time
//                      f=d where d=1-7 for all cameras for a day
//  Called by displayferryoverflow.php
//  Entry: files stored in /Overflow by getferryovereflow.php
//  File name is Adhhmm.jpg or Sdhhmm.jpg for lanes camers
//              DAdhhmm.jpg or DShhmm.jpg for dock camera
//              LAdhhmm.txt or LSddmm.txt for date/time when picture was taken
//
//  RFB  4/24/21
//
$Day = array("", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

    echo "<!DOCTYPE html><html><head><title>Overflow Cameras</title>";
    echo '<meta name="viewport" content="initial-scale=1, width=device-width,height=device-height,target-densitydpi=medium-dpi,user-scalable=no" />';
    echo '<meta http-equiv="Cache-Control" content="no-cache" />';
    echo '</head><body><div>';
    
    chdir("/home/postersw/public_html/Overflow");

    // if f=d, display pictures for one day

    $f = $_GET["f"];
    if(!ctype_alnum($f)) exit("invalid"); // prevent invalid characters
    if($f == "") exit(0);
    $s = strlen($f);
    if($s==1) {  // 1 character is the day only
        OneDay($f);
        exit(0);
    }
    if($s != 6) exit(); // must be 6

    // display cameras for the explicit time
    if(substr($f,0,1) == "S") $dock = "Steilacoom";
    else $dock = "Anderson Island";
    $dt = file_get_contents("L$f.txt");
    $d = substr($f,1,1);  // day index
    echo "$dock overflow on $Day[$d] for " . formattime(substr($f, 2)) . " run<br/><br/>"; 
    echo "<img src='Overflow/$f.jpg'></img><hr/>";
    echo "<img src='Overflow/D$f.jpg'></img><hr/>";
    echo "<hr/><img src='Overflow/X$f.jpg'></img><hr/>";
    echo "($dt)";
    echo "</div></body></html>";
    exit(0);

///////////////////////////////////////////////////////////////////////////////////////////
//  All cameras for a day
//  entry   $d = day index, 1=7
function OneDay($d) {
    global $Day;
    $ST = array(445,545,705,820,930,1035,1210,1445,1550,1700,1810,1920,2035,2220); // ST departures
    $AI = array(515,620,735,855,1005,1110,1245,1515,1625,1735,1845,1955,2110,2250); // AI departures

    $s = 0;
    if($d >5 ) $s = 1; // skip early runs on sat, sun
    echo "Overflow on $Day[$d] for Steilacoom: <br/> ";
    for($i=$s; $i<count($ST); $i++){
        echo formattime($ST[$i]) . ":<br/>";
        $f = "S" . $d . sprintf('%04d', $ST[$i]);
        echo "<img src='Overflow/$f.jpg'></img> ";
        echo "<img src='Overflow/D$f.jpg'></img><br/>";
        $dt = file_get_contents("L$f.txt");
        echo "($dt)<hr/>";
    }

    echo "<hr/>Overflow on $Day[$d] for Anderson Island: <br/> ";
    for($i=$s; $i<count($AI); $i++){
        echo formattime($AI[$i]) . ":<br/>";
        $f = "A" . $d . sprintf('%04d', $AI[$i]);
        echo "<img src='Overflow/$f.jpg'></img> ";
        echo "<img src='Overflow/D$f.jpg'></img><br/>";
        $dt = file_get_contents("L$f.txt");
        echo "($dt)<hr/>";
    }
}

//////////////////////////////////////////////////////////////////
//  formattime convert interger time to display time with am/pm
function formattime($t) {
    $h = floor($t/100);
    $m = sprintf('%02d', $t % 100); // min
    $am = "am";
    if($h >= 12) {
        if($h>12) $h = $h - 12;
        $am = "pm";
    }
    return "$h:$m $am";
}
?>