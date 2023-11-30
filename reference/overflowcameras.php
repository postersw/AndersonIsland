<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  overflowcameras.php  display the overflow cameras for lanes and dock
//  overflowcameras.php?f=Adhhmm         where A = A or S, d = 1-7,8-all,A-Z-holiday, hhmm = 24 hr time
//                    OR f=d where d=1-7 for all cameras for a day, 8=all days, A-Z for holiday
//        Holidays: X,Y,Z=-1,New Years Day,+1; L,M,N=-1,Memorial Day,+1; I,J,K=July3,4,5;    
//                  K,L,M=-1,Labor Day,+1; S,T,U=-1,Thanksgiving,+1; B,C,D=-1,Christmas,+1;  
//  Called by displayferryoverflow.php when user selects a choice
//  Entry: files stored in /Overflow by getferryovereflow.php
//  File name is Adhhmm.jpg or Sdhhmm.jpg for lanes camers
//              DAdhhmm.jpg or DShhmm.jpg for dock camera
//              LAdhhmm.txt or LSddmm.txt for date/time when picture was taken
//
//  RFB  4/24/21
//       4/30/21
//       5/16/21 Prevent caching of images.
//       5/31/21 Accept 8 to display all days
//       6/11/21 Fix random number use to prevent picture caching
//       9/04.21 Show only 2 pictures. Simplify captions.
//       11/26/23. Add holidays.           
//       11/29/23. Remove dependence on time schedule when displaying all files for a day: f=d
//
//$Day = array("", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
// $Day must match $Day in displayferryoverflow.php
$Day = array("", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday", "ALL DAYS",
"New Year's Eve", "New Year's Day Jan 1", "Jan 2", "Memorial Day Eve","Memorial Day","Day after Memorial Day","July 3","July 4","July 5",
     "Labor Day Eve","Labor Day","Day after Labor Day",
     "Thanksgiving Eve","Thanksgiving","Fri after Thanskgiving", "XMas Eve Dec 24","Xmas Dec 25","Dec 26"
       );
// $dL must match $dL in displayferryoverflow.php
$dL = array("","1","2","3","4","5","6","7","8","X","Y","Z","L","M","N","I","J","K","O","P","Q","S","T","U","B","C","D"); // must batch $Day

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

    // Display All runs for 1 Day.
    if($s==1) {  // 1 character is the day only
        // if "8", display entire week
        if($f=="8") {
            for($i=1; $i<8; $i++) DisplayOneDay($i);
        }
        else DisplayOneDay($f);
        if(is_numeric($f)) echo "<p/>Pictures from the last 7 days, taken just as the ferry leaves.";
        else echo "<p/>Pictures from last Holiday (potentially last year), taken just as the ferry leaves.";
        echo "</div></body></html>";   
        exit(0);
    }
    if($s != 6) exit(); // must be 6

    // Display ONE run for explicit time
    $d = substr($f, 1, 1);     // $d = index into $dL and $Day arrays. 
    if($d == "8") {  // if display for entire week loop through all days for 1 time.
        for($i=1; $i<8; $i++) DisplayOneTime(substr($f, 0, 1) . $i . substr($f, 2)); //<S|A><1-7><hhmm>
    }
    else DisplayOneTime($f);

    if(is_numeric($d)) echo "<br/>Pictures from the last 7 days, taken just as the ferry leaves.";
    echo "</div></body></html>";
    exit(0);

////////////////////////////////////////////////////////////////////////////////////////
// DisplayOneTime Show pictures for a time
//  Entry   $f=Adhhmm or Sdhhmm
//          where A = A or S, d = 1-7, 8 for all days, x for holidays
//                hhmm = 24 hr time
//  Exit    writes html to display photos using <img ...>
//
function DisplayOneTime($f) {
    global $Day, $dL;
    $rnd = rand(1,32000); // use random number to prevent caching
    if(substr($f,0,1) == "S") $dock = "Steilacoom";
    else $dock = "Anderson Island";
    $dt = file_get_contents("L$f.txt");
    $di = array_search(substr($f,1,1), $dL); // index into $dL array
    $ft = formattime(substr($f, 2));
    echo "<strong>$dock overflow on $Day[$di] for $ft run</strong><br/> Taken $dt<br/>"; 
    //echo "<img src='Overflow/$f.jpg?d=$rnd' alt='$ft lane not available'></img> ";
    echo "<img src='Overflow/X$f.jpg?d=$rnd' alt='$ft lanes not available'></img><br/>";
    echo "<img src='Overflow/D$f.jpg?d=$rnd' alt='$ft dock not available'></img> ";
    echo "<hr/>";
}

// ///////////////////////////////////////////////////////////////////////////////////////////
// //  DisplayOneDayX All cameras for a day  OBSOLETE. Requires file time to match schedule.
// //  entry   $d = day index, single letter
// //
// function DisplayOneDayX($d) {
//     global $Day, $dL;
//     $ST = array(445,545,705,820,930,1035,1210,1445,1550,1700,1810,1920,2035,2220); // ST departures
//     $AI = array(515,620,735,855,1005,1110,1245,1515,1625,1735,1845,1955,2110,2250); // AI departures

//     $s = 0;
//     //$rnd = str_replace("/", "", $dt); // use date as random numbwer to prevent caching
//     if($d >5 ) $s = 1; // skip early runs on sat, sun
//     $di = array_search(substr($f,1,1), $dL);  // convert Day letter into index
//     echo "<strong>Overflow on $Day[$di] for Steilacoom: </strong><br/> ";
//     for($i=$s; $i<count($ST); $i++){
//         $f = "S" . $d . sprintf('%04d', $ST[$i]);
//         DisplayOneTime($f);
//     }

//     echo "<hr/><strong>Overflow on $Day[$di] for Anderson Island: </strong><br/> ";
//     for($i=$s; $i<count($AI); $i++){
//         $f = "A" . $d . sprintf('%04d', $AI[$i]);
//         DisplayOneTime($f);
//     }
// }


///////////////////////////////////////////////////////////////////////////////////////////
//  DisplayOneDay Displays ALL cameras for a day without using run times
//      Automatically compensates for changes in ferry schedule by displaying all files for a day.
//      But could pick up garbage from leftover files.
//
//  entry   $d = day index, single letter, 1-7 (day of week), A-Z (holiday)
//
function DisplayOneDay($d) {
    global $Day, $dL;

    $files = scandir("/home/postersw/public_html/Overflow"); // list of all files, sorted alpha
    $di = array_search($d, $dL);  // convert Day letter into index for $Day
    echo "<strong>Overflow on $Day[$di] for Steilacoom: </strong><br/> ";
    $fmatch = "LS" . $d;   //"LSd"
    foreach($files as $f){  // loop through all L files for Steilacoom
        if(substr($f, 0, 3) == $fmatch) DisplayOneTime(substr($f,1,6));   // display based on Sdhhmm
    }

    echo "<hr/><strong>Overflow on $Day[$di] for Anderson Island: </strong><br/> ";
    $fmatch = "LA" . $d;   //"ASd"
    foreach($files as $f){
        if(substr($f, 0, 3) == $fmatch) DisplayOneTime(substr($f,1,6));   // display based on Adhhmm
    }
}

//////////////////////////////////////////////////////////////////
//  formattime convert interger time to display time with am/pm
//  entry   hhmm in integer
//  exit    hh:mm am|pm string
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