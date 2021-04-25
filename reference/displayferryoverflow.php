<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  displayferryoverflow - display pictures of the ferry lanes just after a ferry has left. 
//  called by user
//  Lane camera pictures are saved in the folder Overflow as Adhhmm.jpg or Sdhhmm.jpg of the scheduled run,
//      DAdhhmm.jpg  DSdhhmm.jpg for Dock cameras.
//      where d = 1 - 7 for Mon-Sun
//
//  Bob Bedoll. 4/24/21
//

chdir("/home/postersw/public_html/Overflow");
//chdir("C:\A");////////////////// DEBUG for local PC //////////////////////////
date_default_timezone_set("America/Los_Angeles"); // set PDT
echo "<html><head>";
echo '<meta name="viewport" content="initial-scale=1, width=device-width,height=device-height,target-densitydpi=medium-dpi,user-scalable=no" />';
echo "<style>body {font-family:'HelveticaNeue-Light','HelveticaNeue',Helvetica,Arial,sans-serif;} table,td {border:1px solid black;border-collapse:collapse;font-size: larger} A {text-decoration: none;} </style></head>";
echo "<h1>Ferry Overflow Pictures</h1>Tap on a time to see a picture of the ferry lanes taken <i>just as the ferry leaves</i>. If there are cars left, then the ferry filled up for this run. Pictures are saved from the last 7 days. <p>";

BuildRunTimeTable();

exit();


//////////////////////////////////////////////////////////////////////////////////
//  BuildRunTimeTable() 
// Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
//  entry   
//  exit    builds table to display
function BuildRunTimeTable() {
    $ST = array(445,545,705,820,930,1035,1210,1445,1550,1700,1810,1920,2035,2220); // ST departures
    $AI = array(515,620,735,855,1005,1110,1245,1515,1625,1735,1845,1955,2110,2250); // AI departures
    $Day = array("", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    echo "<table>";
    for($d=1; $d<8; $d++) {
        echo "<tr><td colspan='2' style='background-color:blue;color:white'>$Day[$d]</td></tr><tr><td style='background-color: cyan'>Steilacoom&nbsp;&nbsp</td><td style='background-color: cyan'>Anderson Is&nbsp;&nbsp</td></tr>";
        // all runs for the day
        $s = 0;
        if($d >5 ) $s = 1; // skip early runs on sat, sun
        for($i=$s; $i<count($ST); $i++){
            $Stime = formattime($ST[$i]);
            $Atime = formattime($AI[$i]);
            echo "<tr><td><a href='overflowcameras.php?f=S$d" . sprintf('%04d', $ST[$i]) . "'>$Stime</td>";
            echo     "<td><a href='overflowcameras.php?f=A$d" . sprintf('%04d', $AI[$i]) . "'>$Atime</td></tr>";
        }
        echo "<tr><td colspan='2'>&nbsp</td></tr>";
    }
    echo "</table>Pictures are saved from the past 7 days. Each new picture replaces the previous picture that is exactly 7 days (168 hours) old.</html>";
}

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