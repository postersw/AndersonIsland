<?php
//////////////////////////////////////////////////////////////////////////////
// getcomingevents.php - returns the comingevents as a text file.
// copies comingevents.txt to output.
// Only used by version 1.3 apps (March 2016).  Not used by version 1.6 apps (June 2016).
//  Slowly becoming obsolete.
//  7/2/16 - switch to PDT/PST.   
header('Content-Type: application/text');
$diskfile = 'comingevents.txt';
$fh = fopen($diskfile, 'r');
// read it and write each line to output
while(! feof($fh)) {
    $line = fgets($fh);
    // test date here before echoing it
    echo($line);
}
fclose($fh);
date_default_timezone_set("America/Los_Angeles");  // write the time in PDT/PST
$log = 'comingeventslog.txt';
$tlh = fopen($log, 'a');
fwrite($tlh, date('c') . 'CE access from ' . $_SERVER['REMOTE_ADDR'] . "\n");

?>