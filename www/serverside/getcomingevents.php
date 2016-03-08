<?php
//////////////////////////////////////////////////////////////////////////////
// getcomingevents.php - returns the comingevents as a text file.
// copies comingevents.txt to output.

// read it from disk.  need to add a filter by date. don't send earlier than this week.
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

$log = 'comingeventslog.txt';
$tlh = fopen($log, 'a');
fwrite($tlh, date('c') . 'CE access from ' . $_SERVER['REMOTE_ADDR'] . "\n");

?>