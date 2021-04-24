<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  getferryoverflow - gets pictures of the ferry lanes just after a ferry has left. 
//  Run by cron every 5 minutes, at 3 min after the hour.
//  Pictures are saved in the folders Soverflow and Aoverflow as Dhhmm.jpg of the scheduled run,
//      where d = 1 - 7 for Mon-Sun
//
//  Bob Bedoll. 4/23/21
//

chdir("/home/postersw/public_html");
//chdir("C:\A");////////////////// DEBUG for local PC //////////////////////////
date_default_timezone_set("America/Los_Angeles"); // set PDT
$STurl = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/stllane.jpg"; // Steilacoom camera
$AIurl = "https://online.co.pierce.wa.us/xml/abtus/ourorg/pwu/ferry/ailane.jpg"; // AI camera

$runtime = CalcRunTime();  // return dhhmm where d=1-7, hh = 00-23, mm=0-60 CURRENT time
echo $runtime . " ";
$filename = CheckRunTime($runtime);  // return A|Sdhhmm where d=1-7, hh = 00-23, mm=0-60 SCHEDULED ferry run time
echo $filename . " ";
switch(substr($filename, 0, 1)) {
    case "S":
        $picture = file_get_contents($STurl);
        if($picture=="") exit("No ST picture");
        chdir("SToverflow");
        file_put_contents("$filename.jpg", $picture);
        echo "wrote $filename ";
        break;
    case "A":
        $picture = file_get_contents($AIurl); 
        if($picture=="") exit("no AI picture");
        chdir("AIoverflow");
        file_put_contents("$filename.jpg", $picture);
        echo "wrote $filename ";
        break;
    default:
        exit("wrote nothing");
}
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
    $ST = [445,545,705,820,930,1035,1210,1445,1550,1700,1810,1930,2035,2220]; // ST departures
    $AT = [515,620,735,855,1005,1110,1245,1515,1625,1735,1845,1955,2110,2250]; // AI departures
    $d = substr($runtime, 0, 1); // day
    $hhmm = intval(substr($runtime, 1)); // hhmm
    $i = 0;
    // loop through steilacoom
    for($i=0; $i<count($ST); $i++){
        if($hhmm<= $ST[i]) break;
        if($hhmm <= ($ST[i]+5)) return "S" . $d . $ST[i];
    }
    // loop through AI
    for($i=0; $i<count($AI); $i++){
        if($hhmm<= $AI[i]) break;
        if($hhmm <= ($AI[i]+5)) return "A" . $d . $AI[i];
    }
    return "";
}
?>
