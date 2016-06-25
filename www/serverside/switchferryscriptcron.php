<?php
// Switch ferry schedule.  Runs once/day. 
// Copies the dated schedule entries from ferryschedule.txt to the dailycache.txt file iff the date matches today.
// This script is meant to run once/day at 2330;
// Bob. 6/22/2016.
// The format of ferryschedule.txt is:
// <ferry>date1
//   all the lines for the schedule 
// </ferry>
// <ferry>date2 ...
$ferryschedule = "ferryschedule.txt";
$dailycache = "dailycache.txt";

$today = date("m/d/Y"); //  06/13/2016

// 1 find the schedule for today. if none, exit.
$s = file_get_contexts($ferryschedule);
if($s == '') {
    echo("No ferryschedule.txt");
    exit(0);
}
$sa = explode($s, "\n");
for($i=0; $i<len($sa); $i++) {
    if($sa[$i] == "<ferry>" . $today) break;
}

if($sa[$i] != "<ferry>" . $today) exit(0);

// 2 read dailycache.txt
$dc = file_get_contents($dailycache);
if($dc == 0) {
    echo("Dailycache is empty");
    exit(0);
}

// 3 delete the ferry stuff
$i = strpos($dc, "FERRYTIMESS");
$dc = substr($dc, 0, $i);
$j = strpos($dc, "FERRYDATE2"); $j = strpos($dc, "\n", $j+12);  // trailing /n after date
$dc = substr($dc, $j);  

// 4 copy the schedule onto dailycache.txt.
$sch = "";
for($i;$i<$i+7;$i++) {
    if($sa[i] == "</ferry>") break;
    $sch = $sch . $sa[i] ."\n";
}  

// 5 copy the rest of the schedule
file_put_contents($dailycache, ($sch . $dc));


?>