<?php
//////////////////////////////////////////////////////////////////////////////
// gettides.php - returns the current tides which is a json structure
//   if the day has changed since the last weather tides, it gets the
// current tides from aeris and writes it to a file: gettides.txt
// At the front of the file is a unix time stamp in seconds.

// read it from disk
$diskfile = 'gettides.txt';
$fh = fopen($diskfile, 'r');
$theData = fread($fh, 8192);
fclose($fh);

$tideslog = 'tideslog.txt';
$tlh = fopen($tideslog, 'a');
fwrite($tlh, date('c') . 'tides access fron ' . $_SERVER['REMOTE_ADDR'] . "\n");
header('Content-Type: application/json');

// if the same day, return stored version
if($theData <> "") {  // if we read something
	$oldtime = strstr($theData, "{", true);
	$olddate = date('md', $oldtime);
	$currentdate = date('md');
	// if the same day, return the old tide and log it
	if($olddate == $currentdate) { 
		//echo("using old value from " . $oldtime . " deltatime is " . $deltatime);
		echo(strstr($theData, "{"));
		fclose($tlh); 
		exit;
	}
}

// otherwise get the new value, store it, and return it
$webFile = 'http://api.aerisapi.com/tides/9446705?client_id=pSIYiKH6lq4YzlsNY54y0&client_secret=vMb1vxvyo7Z96DSn7niwxVymzOxPN6qiEEdBk7vS&from=-20hours&to=+96hours';
$fh = fopen($webFile, 'r');
$theData = fread($fh, 8192);
fclose($fh);
echo($theData);  // send tide to the phone

// write it to disk
$diskfile = 'gettides.txt';
$fh = fopen($diskfile, 'w');
$theData = time() . $theData;  // prepend a time stamp
fwrite($fh, $theData);
fclose($fh);

// log it
fwrite($tlh, date('c') . 'tides refreshed from aeris.' . "\n");
fclose($tlh);

?>