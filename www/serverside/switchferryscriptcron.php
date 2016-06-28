<?php
// Switch ferry schedule.  Runs once/day.
// Copies the dated schedule entries from ferryschedule.txt to the dailycache.txt file iff the date matches today.
// This script is meant to run once/day at 2300  Note that the cron time is GMT, so it should run at 6am GMT. ;
// Bob. 6/22/2016.
// The format of ferryschedule.txt is:
// <ferry>date1
//   all the lines for the schedule
// </ferry>
// <ferry>date2 ...
//
$ferryschedule = "ferryschedule.txt";
$dailycache = "dailycache.txt";
$test = false;
if($test) {
    //$ferryschedule = "test" . $ferryschedule;
    $dailycache = "test" . $dailycache;
    echo ("Test Run<br/>");
}
date_default_timezone_set("America/Los_Angeles");  // write the time in PDT/PST
$today = date("m/d/Y"); //  06/13/2016
//echo ("Switch Ferry Schedule:" . $today . "<br/>");

// 1 find the schedule for today. if none, exit.
$s = file_get_contents($ferryschedule);
if($s == '') {
    echo("No $ferryschedule");
    exit(0);
}
$s = str_replace("\r\n", "\n", $s); // remove any returns, leaving only line breaks I hope
$sa = explode("\n", $s);  // break the file up into lines
//echo("count=" . count($sa) . "<br/>");
for($i=0; $i<count($sa); $i++) {
    if(substr($sa[$i], 0, 2) == "//") continue; // skip comments
    if($sa[$i] == "<ferry>" . $today) break;
}

if($sa[$i] != "<ferry>" . $today) {
    echo("No ferry schedule for $today");
    exit(0);
}
echo("Ferry schedule for $today found.</br>");

// 2 read dailycache.txt
$dc = file_get_contents($dailycache);
if($dc == "") {
    echo("$dailycache is empty");
    exit(0);
}

// 3 delete the ferry stuff between FERRYTIMESS and FERRYMESSAGEEND
$is = strpos($dc, "FERRYTIMESS");
//echo ("is= $is <br/>"); // i = start of FERRYTIMESS
$ie = strpos($dc, "FERRYMESSAGEEND\n");
//echo("j=$j for FERRYMESSAGEEND<br/>");
if($is == 0 || $ie == 0) {
    echo("FERRYTIMESS at $is or FERRYMESSAGEEND at $ie not found.<br/>");
    exit(0);
}
$dc = substr($dc, $ie+16); // delete everything up to and including FERRYMESSAGEEND

// 4 copy the new schedule onto $sch
$sch = "";
$j = $i + 20;
for($i=$i+1;$i<$j;$i++) {
    if($sa[$i] == "</ferry>") break;
    $sch = $sch . $sa[$i] ."\n";
}
if($sa[$i] != "</ferry>") {
    echo("Did not find /ferry");
    exit(0);
}

// 5 copy the $sch schedule into the front of dailycache.txt.
//echo(("DAILYCACHE\n" . $sch . $dc));
file_put_contents($dailycache, ("DAILYCACHE\n" . $sch . $dc));

echo ("<br/>Replaced ferry schedule for $today with:<br/>\n" . $sch)


?>