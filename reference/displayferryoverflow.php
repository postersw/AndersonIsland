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
//      LAdhhmm.txt  LSdhhmm.txt for info on date time taken. e.g. '<date> <time> Ferry leaving AI.'
//      XAdhhmm.jpg  XSdhhmm.jpg for ?
//      where d = 1 - 7 for Mon-Sun;
//                  X,Y,Z=-1,New Years Day,+1; L,M,N=-1,Memorial Day,+1; I,J,K=July3,4,5;    
//                  K,L,M=-1,Labor Day,+1; S,T,U=-1,Thanksgiving,+1; B,C,D=-1,Christmas,+1;  
//
//  HOLIDAY Notes: remove the 0 from the front of the day name in $Day as data is accumulated.
//      Holidays starting with a 0 are not displayed
//                   
//  Bob Bedoll. 4/24/21
//              5/10/21. Update overflow days/times
//              5/21/21. All "ALL" as an option.
//              9/03/21. Simplify caption.
//                       This is a test.
//              11/26/23. Add holidays.

chdir("/home/postersw/public_html/Overflow");
//chdir("C:\A");////////////////// DEBUG for local PC //////////////////////////
date_default_timezone_set("America/Los_Angeles"); // set PDT
echo "<html><head>";
echo '<meta name="viewport" content="initial-scale=1, width=device-width,height=device-height,target-densitydpi=medium-dpi,user-scalable=no" />';
echo "<style>body {font-family:'HelveticaNeue-Light','HelveticaNeue',Helvetica,Arial,sans-serif;} table,td {border:1px solid black;border-collapse:collapse;font-size: larger} A {text-decoration: none;} </style></head>";
echo "<h1>Ferry Overflow Pictures</h1>Will a ferry run fill up? Tap on a day or time to see ferry lane pictures taken <i>just as the ferry leaves.</i>. If there are cars left, then the ferry filled up for that
 run.<br/>";
echo "<span style='background-color:pink'>Times in PINK usually fill up. </span><p/>";
BuildRunTimeTable();

exit();


//////////////////////////////////////////////////////////////////////////////////
//  BuildRunTimeTable() 
// Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
//  entry   
//  exit    builds table to display
//      where d = 1 - 7 for Mon-Sun;
//                  S,T,U=Thanksgiving,-1,+1; B,C,D=Christmas,-1,+1; I,J,K=July3,4,5; L,M,N=Memorial Day,-1,+1, 
//                  O,P,Q=Labor Day,-1,+1; X,Y,Z=New Years Day,-1,+1
function BuildRunTimeTable() {
    // SCHEDULED RUNS same for ALL days
    $ST = array(445,545,705,820,930,1035,1210,1445,1550,1700,1810,1920,2035,2220); // ST departures
    $AI = array(515,620,735,855,1005,1110,1245,1515,1625,1735,1845,1955,2110,2250); // AI departures
    // OVERFLOW runs by day  (1=Sunday, ... 8=All)
    $STO = array(11445,11550,11700, 21445,21550,21700, 31445,31550,31700, 41445,41550,41700, 51445,51550,51700,51810, 61445); // ST overflow times:  dhhss, where d = 1-7 M-S, must be in numeric order
    $AIO = array(11110,11245,11515, 21245,21515, 31245,31515, 41245,41515, 51245,51515); // AI overflow times:  dhhss, where d = 1-7 M-S
    // Day or holiday names. Note: Days beginning with 0 are not displayed because there is no data yet.
    $Day = array("", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday", "ALL WEEK",
    "New Year's Eve 2023", "New Year's Day (Jan 1)", "Jan 2", "0Memorial Day Eve","0Memorial Day","0Day after Memorial Day","0July 3","0July 4","0July 5",
     "0Labor Day Eve","0Labor Day","0Day after Labor Day",
     "Thanksgiving Eve 2023","Thanksgiving","Fri after Thanskgiving", "XMas Eve Dec 24, 2023","Xmas Dec 25","Dec 26"
           );
    $dL = array("","1","2","3","4","5","6","7","8","X","Y","Z","L","M","N","I","J","K","O","P","Q","S","T","U","B","C","D"); // must batch $Day
    $amcolor = "#f0ffff";
    //echo "  <a >Tap for Holiday traffic pics from last year)</a><br>";
    echo "<table>";
    echo "<tr ><td colspan='2' style='background-color:blue;color:white'><a href='#holiday' style='color:white' >Holidays [tap here]</td></tr>";
    echo "<tr><td colspan='2'>&nbsp</td></tr>";     
    // display each day
    for($d=1; $d<count($dL); $d++) {

        if($d==9) {  // holiday heading
            echo "<tr id='holiday'><td colspan='2' style='background-color:blue;color:white'><a style='color:white' >HOLIDAYS</td></tr>";
            echo  "<tr><td colspan='2'>Shows all runs for each holiday from last year. <br><a href='#New'>New Years</a> | <a href='#Mem'>Mem. Day</a> | <a href='#Jul'>July 4th</a> | " .
                    "<a href='#Lab'>Labor Day</a> | <a href='#Tha'>Thanksgiving</a> | <a href='#Xma'>XMas</a></td></tr>";
        }
        if(substr($Day[$d],0,1)=="0") continue; // skip a day beginning with 0 because we don't have data for it yet.
        $id = substr($Day[$d],0,3);  // id for the day
        echo "<tr><td colspan='2' id='$id' style='background-color:blue;color:white'><a style='color:white' href='overflowcameras.php?f=$dL[$d]'>$Day[$d] [show all]</td></tr>";
        if($d < 9) {  // if not a holiday, show all times.
            
            // for non-holidays, show all runs for the day
            echo "<tr><td style='background-color: lightblue'>Steilacoom&nbsp;&nbsp</td><td style='background-color: cyan'>Anderson Is&nbsp;&nbsp</td></tr>";
            $s = 0;
            if($d >5 ) $s = 1; // skip early runs on sat, sun, holiday
            for($i=$s; $i<count($ST); $i++){
                $Stime = formattime($ST[$i]);
                $Atime = formattime($AI[$i]);
                if($ST[$i]<1200) $stcolor= "#f0ffff";  // light blue in morning
                else $stcolor="white";
                $aicolor = $stcolor;
                // find if it is marked as an overflow run in the STO or AIO array. Days only. Not for holidays.
                if($d<9) {
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
                }   
                // generate the table entry with the reference to the overflow pictures.
                echo "<tr><td style='background-color:$stcolor'><a href='overflowcameras.php?f=S$dL[$d]" . sprintf('%04d', $ST[$i]) . "'>$Stime</td>";
                echo     "<td style='background-color:$aicolor'><a href='overflowcameras.php?f=A$dL[$d]" . sprintf('%04d', $AI[$i]) . "'>$Atime</td></tr>";
            }
        }
        echo "<tr><td colspan='2'>&nbsp</td></tr>";
    }
    echo "</table>Daily pictures are saved from the past 7 days. Each new picture replaces the previous picture for that day of the week and time of day.<br>";
    echo "</table>Holiday pictures are saved from the previous year's holiday.</html>";
}

//////////////////////////////////////////////////////////////////
//  formattime convert interger time to display time with am/pm
//  Entry: #t = integer time hhmm
//  Exit: returns hh:mm am|pm as text
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