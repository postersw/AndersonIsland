<?php
// Switch ferry schedule.  Runs once/day to automatically update the
//     current and next ferry schedule lines in dailycache.txt.
// Copies the dated schedule entries from ferryschedule.txt to the dailycache.txt file iff its date is for tomorrow,
//  and also copies the NEXT set of schedule entries with the cutover date.
// This script is meant to run once/day at 2300;
// Bob. 7/2/2016.
//
// The format of 'dailycache.txt' MUST BE:
//  DAILYCACHE
//  FERRYTIMESS
//      ferry S times
//  FERRYTIMESA
//      ferry A times
//  FERRYTIMESK
//      ferry K times
//  FERRYTIMESS2
//      ferry S times
//  FERRYTIMESA2
//      ferry A times
//  FERRYTIMESK2
//      ferry K times
//  FERRYDATE2
//      date of cutover to ferrytimes2 as: mm/dd/yyyy
//
// The format of ferryschedule.txt MUST BE:
// <ferry>date1
//  FERRYTIMESS
//      ferry S times
//  FERRYTIMESA
//      ferry A times
//  FERRYTIMESK
//      ferry K times
// </ferry>
// <ferry>date2 ...
//  Replaces (6) FERRYTIMESS,A,K lines on the evening of date1-1, and
//  Replaces FERRYTIMESS2,A2,K2,FERRYDATE2 lines with the NEXT set of 6 ferry lines, and uses date2 for the FERRYDATE2 line
//
$ferryschedule = "ferryschedule2.txt";
$dailycache = "dailycache.txt";
chdir("/home/postersw/public_html");  // move to web root
$test = false;
if($test) {
    //$ferryschedule = "test" . $ferryschedule;
    $dailycache = "test" . $dailycache;
    echo ("Test Run<br/>");
}
date_default_timezone_set("America/Los_Angeles");  // write the time in PDT/PST
$today = date("m/d/Y"); //  e.g. 06/13/2016
$tomorrow = date("m/d/Y", time() + (24*3600)); // today+24 hours
//echo ("Switch Ferry Schedule:" . $today . "<br/>");

// 1 find the schedule for tomorrow. if none, exit.
$s = file_get_contents($ferryschedule);
if($s == '') {echo("No $ferryschedule"); exit(0);}
$s = str_replace("\r\n", "\n", $s); // remove any returns, leaving only line breaks I hope
$sa = explode("\n", $s);  // break the file up into lines
//echo("count=" . count($sa) . "<br/>");
for($i=0; $i<count($sa); $i++) {
    if(substr($sa[$i], 0, 2) == "//") continue; // skip comments
    if($sa[$i] == "<ferry>" . $tomorrow) break;
}

if($sa[$i] != "<ferry>" . $tomorrow) {echo("No ferry schedule for $tomorrow");exit(0);}
echo("Ferry schedule for $tomorrow found.</br>");

// 2 read dailycache.txt
$dc = file_get_contents($dailycache);
if($dc == "") {echo("$dailycache is empty"); exit(0);}

// 3 delete all the ferry stuff from beginning of dailycache to FERRYMESSAGE.

$ie = strpos($dc, "FERRYMESSAGE\n");
//echo("j=$j for FERRYMESSAGEEND<br/>");
if($ie == 0) {echo(" FERRYMESSAGE at $ie not found.<br/>"); exit(0);}
$dc = substr($dc, $ie); // delete EVERYTHING up to FERRYMESSAGE

// 4 copy the new schedule (6 lines) onto $sch. verify the labels.
$sch = "";
if($sa[$i+1] != "FERRYTIMESS") {echo("FERRYTIMESS not found");exit(0);}
$sch = $sch . $sa[$i+1] ."\n" . $sa[$i+2] ."\n";
if($sa[$i+3] != "FERRYTIMESA") {echo("FERRYTIMESA not found");exit(0);}
$sch = $sch . $sa[$i+3] ."\n" . $sa[$i+4] ."\n";
if($sa[$i+5] != "FERRYTIMESK") {echo("FERRYTIMESK not found");exit(0);}
$sch = $sch . $sa[$i+5] ."\n" . $sa[$i+6] ."\n";
$i = $i + 7;

// 5 now find the next <ferry> schedule and copy 6 lines  onto sch
$date2 = "";
for($i=$i; $i<count($sa); $i++) {
    if(substr($sa[$i], 0, 2) == "//") continue; // skip comments
    if(substr($sa[$i], 0, 7) == "<ferry>") {
        $date2 = substr($sa[$i], 7);
        echo("Ferry schedule for DATE2: $date2 found.</br>");
        if($sa[$i+1] != "FERRYTIMESS") {echo("FERRYTIMESS not found");exit(0);}
        $sch = $sch . $sa[$i+1] ."2\n" . $sa[$i+2] ."\n";
        if($sa[$i+3] != "FERRYTIMESA") {echo("FERRYTIMESA not found");exit(0);}
        $sch = $sch . $sa[$i+3] ."2\n" . $sa[$i+4] ."\n";
        if($sa[$i+5] != "FERRYTIMESK") {echo("FERRYTIMESK not found");exit(0);}
        $sch = $sch . $sa[$i+5] ."2\n" . $sa[$i+6] ."\n";
        break;
    }
}

if($date2=="")  {echo("DATE2 not found");exit(0);}
$sch = $sch . "FERRYDATE2\n" . $date2 . "\n";

// 6 copy the $sch schedule into the front of dailycache.txt.

file_put_contents($dailycache, ("DAILYCACHE\n" . $sch . $dc));

// 7 log it
str_replace("\n", "<br/>", $sch);
echo ("<br/>Replaced ferry schedule for $tomorrow with:<br/>" . $sch);

?>