<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  overflowcameras.php  display the overflow cameras for lanes and dock
//  overflowcameras.php?f=Adhhmm         where A = A or S, d = 1-7, hhmm = 24 hr time
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
    $f = $_GET["f"];
    if(substr($f,0,1) == "S") $dock = "Steilacoom";
    else $dock = "Anderson Island";
    $dt = file_get_contents("L$f.txt");
    $d = substr($f,1,1);  // day index
    echo "$dock overflow on $Day[$d] for " . formattime(substr($f, 2)) . " run<br/><br/>"; 
    echo "<img src='Overflow/$f.jpg'></img><hr/>";
    echo "<img src='Overflow/D$f.jpg'></img><hr/>";
    echo "($dt)";
    echo "</div></body></html>";
    exit(0);

//////////////////////////////////////////////////////////////////
//  formattime convert interger time to display time with am/pm
function formattime($t) {
    $h = floor($t/100);
    $m = sprintf('%02d', $t % 100); // min
    $am = "am";
    if($h > 12) {
        $h = $h - 12;
        $am = "pm";
    }
    return "$h:$m $am";
}
?>