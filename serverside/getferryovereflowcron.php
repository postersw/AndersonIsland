<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  getferryoverflow - gets pictures of the ferry lanes just after a ferry has left. 
//  Run by cron every 5 minutes, 
//  Lane camera pictures are saved in the folder Overflow as 
//      Not used as of 9/5/21: Adhhmm.jpg or Sdhhmm.jpg of the scheduled run for line camera
//      DAdhhmm.jpg or DSdhhmm.jpg for Dock cameras.
//      XAdhhmm.jpg or XSdhhmm.jpg for lane camera after the ferryposition.txt says the ferry has left.
//      LAdhhmm.txt or LS.... for log of date/time pictures were taken
//      log.txt is a continuous log of the filename written.
//      where d = 1 - 7 for Mon-Sun
//                  X,Y,Z=-1,New Years Day,+1; L,M,N=-1,Memorial Day,+1; I,J,K=July3,4,5;    
//                  K,L,M=-1,Labor Day,+1; S,T,U=-1,Thanksgiving,+1; B,C,D=-1,Christmas,+1;  
//
//  Entry: Reads file "ferryposition.txt" to get the current file position. 
//          pictures are saved until the ferry leaves the dock.
//
//  Bob Bedoll. 4/24/21
//              4/29/21 take the Xtra picture exactly at sailing time.
//              5/23/21 add repeats if no picture. Log no picture.
//              6/10/21 changes times. wait 1 m. pictures. wait 1m. pictures.
//              9/03/21 save last picture before ferry leaves by checking 'ferryposition.txt'.
//              9/06/21 Accept 'return to ...'.
//              9/05/21 Ensure 1st picture is saved. save picture ever 2 min.
//              9/8/21  Back to 2.5 min waittime
//              9/13/21 Turn off debug messages
//              11/28/23 Add Holiday pictures. Calculate holidates for Memorial, Labor, Thanksgiving.
//

chdir("/home/postersw/public_html/Overflow");
$sleeptime = 150;  // 2.5 minutes
//chdir("C:\A");////////////////// DEBUG for local PC //////////////////////////
date_default_timezone_set("America/Los_Angeles"); // set PDT
$STurl = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/stllane.jpg"; // Steilacoom camera
$STdock = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/stlferry.jpg";
$AIurl = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/ailane.jpg"; // AI camera
$AIdock = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/aiferry.jpg"; // AI camera
//echo "ver 9/5/21 ";
$runtime = CalcRunTime();  // return dhhmm where d=1-7, hh = 00-23, mm=0-60 CURRENT time
//echo " runtime=$runtime ";
$filename = CheckRunTime($runtime);  // return A|Sdhhmm where d=1-7, hh = 00-23, mm=0-60 SCHEDULED ferry run time
//echo " filename=$filename ";
if($filename == "") exit();  // exit if to skip capture

//  Time to capture a picture of a scheduled run.

$dt = date("m/d/y h:i");
$holidayfilename = CheckHolidayFilename($filename);  // returns a holiday filename if a holiday

// if filename is set, capture the camera for Steilacoom or AI
switch(substr($filename, 0, 1)) {
    case "S": // Steilacoom
        $dock = "Steilacoom";
        sleep(120); //wait 2 min
        // capture pictures every 2 min until the ferry leaves, up to 20 minutes.
        for($i=0;$i<12;$i++) {
            // capture pictures for next cycle
            $picture = GetPicture($STdock);
            file_put_contents("D$filename.jpg", $picture);
            if($holidayfilename<>"") file_put_contents("D$holidayfilename.jpg", $picture); // 2nd copy for holiday
            $picture = GetPicture($STurl);
            file_put_contents("X$filename.jpg", $picture);  
            if($holidayfilename<>"") file_put_contents("X$holidayfilename.jpg", $picture);  // 2nd copy for holiday
            $dt = date("m/d/y h:i");
            sleep($sleeptime);    // wait 2 min which is the ferry position update cycle

            // exit if ferry has left
            $position = file_get_contents("../ferryposition.txt"); // updated every 3 minutes by getferrypositioncron
            //echo date("h:i") . ", $filename, i=$i, position=$position \n"; // debug
            if($position=="") break; // if no position
            if(strpos($position, "arriving @AI") > 0) break; // if the ferry has left St, use the last pictures taken
            if(strpos($position, "returning to AI") > 0) break; // if the ferry has left St, use the last pictures taken
        }                   
        break;

    case "A": // AI
        $dock = "AI";
        sleep(120); // wait 2 min
        // capture pictures every 2 min until the ferry leaves, up to 20 minutes.
        for($i=0;$i<12;$i++) {
            // capture pictures for next cycle
            $picture = GetPicture($AIdock); 
            file_put_contents("D$filename.jpg", $picture);
            if($holidayfilename<>"") file_put_contents("D$holidayfilename.jpg", $picture);  // 2nd copy for holiday
            $picture = GetPicture($AIurl);
            file_put_contents("X$filename.jpg", $picture);
            if($holidayfilename<>"") file_put_contents("X$holidayfilename.jpg", $picture);  // 2nd copy for holiday
            $dt = date("m/d/y h:i");
            sleep($sleeptime);  // wait 2 min which is the ferry position update cycle

            //  exit if ferry has left
            $position = file_get_contents("../ferryposition.txt"); // updated every 3 minutes by getferrypositioncron
            //echo date("h:i") . ", $filename, i=$i, position=$position \n"; // debug
            if($position=="") break; // if no position
            if(strpos($position, "arriving @St") > 0) break; // if the ferry has left AI, use the last pictures taken
            if(strpos($position, "returning to St") > 0) break; // if the ferry has left AI, use the last pictures taken 
        }
        break;

    default:
        //echo " no run";
        exit();
}

// log it
if($i==12) $msg = "$dt Ferry did not leave $dock.";  // if timeout on loop
else $msg = "$dt Ferry leaving $dock.";
file_put_contents("L$filename.txt", $msg); // log date and time
if($holidayfilename <> "") file_put_contents("L$holidayfilename.txt", $msg);
file_put_contents("overflowlog.txt", "$dt: $filename\n", FILE_APPEND);  // write to log
if($holidayfilename <> "")file_put_contents("overflowlog.txt", "$dt: $holidayfilename\n", FILE_APPEND);  // write to log
if($holidayfilename <> "") echo "Holiday filename debug: $holidayfilename $dt "; // generate a debugging email
//echo "Debug: wrote $filename $dt";
exit(0);

///////////////////////////////////////////////////////////////////////////////////
// GetPicture - get picture. if none try again in a few seconds.
//  Entry   $url = url for picture
//  Exit    binary of picture.
function GetPicture($url) {
    for($i=0;$i<4;$i++) {  // try 4 times
        $picture = file_get_contents($url);
        if($picture!="") return $picture;  // exit if a picture
        sleep(20);     
    }
    if($picture=="") {  // if no picture, issue a message
        echo ("No $url picture after 4 tries");
        file_put_contents("overflowlog.txt", "$dt: no picture for $url\n", FILE_APPEND);  // write to log
    }
    return $picture;
}

//////////////////////////////////////////////////////////////////////////////////
// CalcRunTime - return run time as dhhmm where d=1-7 (Mo-Su), hh = 00-23, mm=0-60
//  Entry   none
//  Exit    dhhmm where d=1-7 (Mo-Su), hh = 00-23, mm=0-60
function CalcRunTime() {
    $d = date("NHi");
   return ($d);
}


//////////////////////////////////////////////////////////////////////////////////
//  CheckRunTime() Returns filename iff it is time to capture a picture, based on 
//              scheduled run time. Must be 0-5 minutes AFTER a scheduled run time. 
// Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
//  entry   $runtime as dhhmm
//  exit    returns Sdhhmm to capture steilacoom, Adhhmm to capture AI where dhhmm is the scheduled departure time
//          returns "" to skip capture
function CheckRunTime($runtime) {
    $ST = array(445,545,705,820,930,1035,1210,1445,1550,1700,1810,1920,2035,2220); // ST departures
    $AI = array(515,620,735,855,1005,1110,1245,1515,1625,1735,1845,1955,2110,2250); // AI departures
    $d = substr($runtime, 0, 1); // day
    $hhmm = intval(substr($runtime, 1)); // hhmm
    //echo " hhmm=$hhmm ";
    $s = 0;
    if($d>5) $s = 1; // skip early runs on sat, sun (d=-6, d=7)
    // loop through steilacoom
    for($i=$s; $i<count($ST); $i++){
        if($hhmm < $ST[$i]) break;
        if($hhmm < ($ST[$i]+5)) return "S" . $d . sprintf('%04d', $ST[$i]);
        //echo " (ST[i]+5)=" . ($ST[$i]+5) . " ";
    }
    // loop through AI
    for($i=$s; $i<count($AI); $i++){
        if($hhmm < $AI[$i]) break;
        if($hhmm < ($AI[$i]+5)) return "A" . $d . sprintf('%04d', $AI[$i]);
    }
    return "";  // skip capture
}

//////////////////////////////////////////////////////////////////////////////////////////////////
//  CheckHolidayFilename - if a holiday, returns the holiday filename.
//      This is used to make a second copy of the file, for the holiday, using the holiday letter
//  entry   $filename = filename as <A|S><d><hhmm>  eg S10530
//              where d = 1-7
//  exit    if a holiday, $filename as <A/S><d><hhmm> where d=holiday code
//                  X,Y,Z=-1,New Years Day,+1; L,M,N=-1,Memorial Day,+1; I,J,K=July3,4,5;    
//                  K,L,M=-1,Labor Day,+1; S,T,U=-1,Thanksgiving,+1; B,C,D=-1,Christmas,+1;  
//          otherwise return ""
//
function CheckHolidayFilename($filename){
    // HOLIDAY day to letter $dL must match displayferryoverflow.php, overflowcameras.php
    $dL = array("","1","2","3","4","5","6","7","8", "X","Y","Z","L","M","N","I","J","K","O","P","Q", "S", "T", "U", "B", "C", "D");
    $h  = array(0 ,  0,  0,  0,  0,  0,  0,  0,  0,1231,101,102,526,527,528,703,704,705,901,902,903,1127,1128,1129,1224,1225,1226);

    $year = date("Y");
    $m = date("m");
    // dates for 202x  (Memorial Day, Labor Day, Thanksgiving). Must be adjusted every year.
    switch($m) {
      // thanksgiving:
      case "11": // 4th thursday in nov
        $i = (int)date("md", strtotime("fourth thursday of november $year"));
        $h[21] = $i-1; $h[22] = $i; $h[23] = $i+1; 
        echo " A thanksgiving:$i,  $h[21], $h[22], $h[23] <br>";
        break;
      // labor day
      case "9":   // first monday of sept
        $i = (int)date("nd", strtotime("first monday of september $year"));
        $h[18] = $i-1; $h[19] = $i; $h[20] = $i+1;
        break;
      // memorial day last Monday in may
      case "5":  // first monday of sept
        $i = (int)date("nd", strtotime("last monday of may $year"));
        $h[12] = $i-1; $h[13] = $i; $h[14] = $i+1;
        break;
    }
    // now find holiday
    $mmdd = (int)(date("nd")); // mmdd  e.g. 523  or 1231
    $i = array_search($mmdd, $h);
    if($i===false) return "";
    return substr($filename,0,1) . $dL[$i] . substr($filename,2);  // replace day with holiday code
}
?>
