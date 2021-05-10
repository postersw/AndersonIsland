<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  displayferryoverflow - displays a ferry run schedule which lets a user pick a day&time of a ferry
//          run to display pictures of the ferry lanes just after a ferry has left. 
//  called by user from the Anderson Island Assistant: displayferryoverflow.php.  (no parameters)
//
//  Calls: overflowcameras.php to display the actual pictures.
//  Lane camera pictures are saved by getferryoverflow.php (cron job) in the folder Overflow as
//      Adhhmm.jpg or Sdhhmm.jpg of the scheduled run,
//      DAdhhmm.jpg  DSdhhmm.jpg for Dock cameras.
//      where d = 1 - 7 for Mon-Sun
//
//  Bob Bedoll. 4/24/21
//              5/10/21. Update overflow days/times
//

chdir("/home/postersw/public_html/Overflow");
//chdir("C:\A");////////////////// DEBUG for local PC //////////////////////////
date_default_timezone_set("America/Los_Angeles"); // set PDT
echo "<html><head>";
echo '<meta name="viewport" content="initial-scale=1, width=device-width,height=device-height,target-densitydpi=medium-dpi,user-scalable=no" />';
echo "<style>body {font-family:'HelveticaNeue-Light','HelveticaNeue',Helvetica,Arial,sans-serif;} table,td {border:1px solid black;border-collapse:collapse;font-size: larger} A {text-decoration: none;} </style></head>";
echo "<h1>Ferry Overflow Pictures</h1>Will a ferry run fill up? Tap on a day or time to see ferry lane pictures taken <i>just as the ferry is scheduled to leave.</i>. If there are cars left, then the ferry filled up for that
 run.<br/>";
echo "<span style='background-color:pink'>Times in PINK usually fill up. </span><p/>";
BuildRunTimeTable();

exit();


//////////////////////////////////////////////////////////////////////////////////
//  BuildRunTimeTable() 
// Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
//  entry   
//  exit    builds table to display
function BuildRunTimeTable() {
    // SCHEDULED RUNS same for ALL days
    $ST = array(445,545,705,820,930,1035,1210,1445,1550,1700,1810,1920,2035,2220); // ST departures
    $AI = array(515,620,735,855,1005,1110,1245,1515,1625,1735,1845,1955,2110,2250); // AI departures
    // OVERFLOW runs by day
    $STO = array(11445,11550,11700,11810, 21445,21550,21700,21810, 31445,31550,31700,31810, 41445,41550,41700,41810, 51445,51550,51700,51810, 61445, 71445); // ST overflow times:  dhhss, where d = 1-7 M-S, must be in numeric order
    $AIO = array(10735,10855,11005,20735,20855,21005,30735,30855,31005,40735,40855,41005,50735,50855,51005, 61515, 71515); // AI overflow times:  dhhss, where d = 1-7 M-S
    //
    $Day = array("", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $amcolor = "#f0ffff";
    echo "<table>";
    for($d=1; $d<8; $d++) {
        echo "<tr><td colspan='2' style='background-color:blue;color:white'><a style='color:white' href='overflowcameras.php?f=$d'>$Day[$d] [show all]</td></tr><tr><td style='background-color: lightblue'>Steilacoom&nbsp;&nbsp</td><td style='background-color: cyan'>Anderson Is&nbsp;&nbsp</td></tr>";
        // all runs for the day
        $s = 0;
        if($d >5 ) $s = 1; // skip early runs on sat, sun
        for($i=$s; $i<count($ST); $i++){
            $Stime = formattime($ST[$i]);
            $Atime = formattime($AI[$i]);
            if($ST[$i]<1200) $stcolor= "#f0ffff";  // light blue in morning
            else $stcolor="white";
            $aicolor = $stcolor;
            // find if it is an overflow run
            $n = $d*10000 + $ST[$i];
            for($j=0; $j<count($STO); $j++) {
                if($STO[$j] > $n) break;
                if($STO[$j] < $n) continue;
                $stcolor = "pink";
                break;
            }
            // find if it is an overflow run
            $n = $d*10000 + $AI[$i];
            for($j=0; $j<count($AIO); $j++) {
                if($AIO[$j] > $n) break;
                if($AIO[$j] < $n) continue;
                $aicolor = "pink";
                break;
            }
            echo "<tr><td style='background-color:$stcolor'><a href='overflowcameras.php?f=S$d" . sprintf('%04d', $ST[$i]) . "'>$Stime</td>";
            echo     "<td style='background-color:$aicolor'><a href='overflowcameras.php?f=A$d" . sprintf('%04d', $AI[$i]) . "'>$Atime</td></tr>";
        }
        echo "<tr><td colspan='2'>&nbsp</td></tr>";
    }
    echo "</table>Pictures are saved from the past 7 days. Each new picture replaces the previous picture for that day of the week and time of day.</html>";
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