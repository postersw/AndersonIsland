<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  getferryoverflow - gets pictures of the ferry lanes just after a ferry has left. 
//  Run by cron every 5 minutes, 
//  Lane camera pictures are saved in the folder Overflow as 
//      Adhhmm.jpg or Sdhhmm.jpg of the scheduled run for line camera
//      DAdhhmm.jpg or DSdhhmm.jpg for Dock cameras.
//      LAdhhmm.txt or LS.... for log of date/time pictures were taken
//      log.txt is a continuous log of the filename written.
//      where d = 1 - 7 for Mon-Sun
//
//  Bob Bedoll. 4/24/21
//

chdir("/home/postersw/public_html/Overflow");
//chdir("C:\A");////////////////// DEBUG for local PC //////////////////////////
date_default_timezone_set("America/Los_Angeles"); // set PDT
$STurl = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/stllane.jpg"; // Steilacoom camera
$STdock = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/stlferry.jpg";
$AIurl = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/ailane.jpg"; // AI camera
$AIdock = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/aiferry.jpg"; // AI camera
//echo "ver 4/24 1312 ";
$runtime = CalcRunTime();  // return dhhmm where d=1-7, hh = 00-23, mm=0-60 CURRENT time
//echo $runtime . " ";
$filename = CheckRunTime($runtime);  // return A|Sdhhmm where d=1-7, hh = 00-23, mm=0-60 SCHEDULED ferry run time
//echo $filename . " ";

// if filename is set, capture the camera for Steilacoom or AI
switch(substr($filename, 0, 1)) {
    case "S": // Steilacoom
        sleep(120); // wait 2 minutes
        $picture = file_get_contents($STurl);
        if($picture=="") exit("No ST picture");
        file_put_contents("$filename.jpg", $picture);
        $picture = file_get_contents($STdock);
        if($picture=="") exit("No ST picture");
        file_put_contents("D$filename.jpg", $picture);
        break;
    case "A": // AI
        sleep(120); // wait 2 minutes
        $picture = file_get_contents($AIurl); 
        if($picture=="") exit("no AI picture");
        file_put_contents("$filename.jpg", $picture);
        $picture = file_get_contents($AIdock); 
        if($picture=="") exit("no AI dock picture");
        file_put_contents("D$filename.jpg", $picture);
        break;
    default:
        exit();
}
$dt = date("m/d/y h:i");
file_put_contents("L$filename.txt", $dt); // log date and time
file_put_contents("log.txt", "$dt : $filename", FILE_APPEND);  // write to log
echo "wrote $filename $dt";
exit(0);

//////////////////////////////////////////////////////////////////////////////////
// CalcRunTime - return run time as dhhmm where d=1-7 (Mo-Su), hh = 00-23, mm=0-60
function CalcRunTime() {
    $d = date("NHi");
   return ($d);
}


//////////////////////////////////////////////////////////////////////////////////
//  CheckRunTime() d
// Run time array = [hhmm,....] where  hh = 00-23, mm=0-60
//  entry   $runtime as dhhmm
//  exit    returns Sdhhmm to capture steilacoom, Adhhmm to capture AI where dhhmm is the scheduled departure time
//          returns "" to skip capture
function CheckRunTime($runtime) {
    $ST = array(445,545,705,820,930,1035,1210,1445,1550,1700,1810,1930,2035,2220); // ST departures
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
    return "";
}
?>
