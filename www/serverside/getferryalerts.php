<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  getferryalerts - gets the ferry alerts from the rss feed and saves the latest one in alerts.txt
//  alerts are cleared after 4 hours or if they are removed from the rss feed
//  Bob Bedoll. 3/13/16.
//		4/09/16. Changed alertfile to alert.txt
//  Sample RSS feed:

echo("PHP START");
chdir("/home/postersw/public_html");
$alertclearhours = 5;  // hours to clear an alert
$alertfile = "alert.txt";  // alert file the phone reads
$alertlog = "alertlog.txt";
$alertrssurl = "http://www.co.pierce.wa.us/RSSFeed.aspx?ModID=63&CID=Pierce-County-Ferry-Rider-Alert-26"; // url for rss alert

//  Read the RSS feed. Isn't this easy! php is great.
// try 10 times to get content
for($i=0; $i<10; $i++) {
    $x = simplexml_load_file($alertrssurl);
    $title = $x->channel->title;
    //echo("  |x->channel->title=" . $x->channel->title);
    if($title != "") break; // if we have content
}
if($title == "") {
	ClearAlertFile($alertfile, $alertlog);
	logalertlast("no title");
	exit(0); // if no reply 
}
// get actual message
$title = $x->channel->item[0]->title; 
if($title == "") {
	ClearAlertFile($alertfile, $alertlog);
	exit(0); // if no title 
}

$desc = $x->channel->item[0]->description;
$alertts = $x->channel->item[0]->pubDate;
$alertts = substr($alertts, 5);
$alertts[2] = "/";
$alertts[6] = "/";
$alertts[11] = ":";
//change Fri, 11 Mar 2016 21:30:08 -0800 into "10/Oct/2000:13:55:36 -0700"
// strip out day, and strip out -ms
echo ("  |pubDate=" . $alertts . " " . $title);
// check expiration
date_default_timezone_set("America/Los_Angeles"); // set PDT
$talert=strtotime($alertts); // covert to timestamp
//echo("  |talert:" . $talert . "  |"); echo(date("Y-m-d-H-i-s", $talert));// debug
$t=time(); // current seconds in PDT I hope
//echo("  |current:" . $t . "|"); echo(date("Y-m-d-H-i-s", $t));// debug
$deltat = $t - $talert;
//echo (" delta t hrs = " . ($deltat/3600));

// if alert is >5 hrs old, clear it
 if($deltat > ($alertclearhours*3600)) { // if > 4 hours old
     ClearAlertFile($alertfile, $alertlog);
     exit(0);
 }

// write alert to file
//echo ("writing alert to file.");
$alertts  = substr($x->channel->item[0]->pubDate, 5, 20);
$alertstring = $alertts . " " . $title . "\n" . $desc;
$alc = file_get_contents($alertfile);  // read the alert file
if($alc == $alertstring) {
	logalertlast("alert already written");
	return; // if already written
}

// write it to file, and log it
$fh = fopen($alertfile, 'w');
fwrite($fh, $alertstring);
fclose($fh);

// log it
 $fhl = fopen($alertlog, 'a');
fwrite($fhl, date("Y-m-d-H-i-s") . "|" . $alertstring . "\n");
fclose($fhl);  
echo ("wrote to file: " . $alertstring);
logalertlast("wrote to alert file");

// now send alert using Pushbots and Google Cloud Messaging
PushANotification( $alertts . " " . $title );
exit(0);

/////////////////////////////////////////////////////////////////
//  ClearAlertFile - writes an empty string to the alert file
//
function ClearAlertFile($alertfile, $alertlog) {
     logalertlast("cleared alert file");
     $alc = file_get_contents($alertfile);
     if($alc=="") return; // if already empty
     
     $fh = fopen($alertfile, 'w');
     fwrite($fh, "");
     fclose($fh);   
     echo("ClearAlertFile cleared the $alertfile");
     // log it
     $fhl = fopen($alertlog, 'a');
     fwrite($fhl, date("Y/m/d H:i:s") . " Cleared alert file.\n");
     fclose($fhl);  
	 logalertlast("physically cleared alert file");
     //echo("ClearAlertFile wrote to log");
}

/////////////////////////////////////////////////////////////////
//  loglastalert - writes an  string to the alertlast file
//
function logalertlast($s) {
	$alertlast = "alertlast.txt";
    $fh = fopen($alertlast, 'w');
    fwrite($fh, date("Y/m/d H:i:s") . $s);
    fclose($fh);  
}
 

////////////////////////////////////////////////////////////////////////
//  Push notification - send a notificatyion using Pushbots to all android users
function PushANotification($note) {
    // Push The notification with parameters
    require_once('PushBots.class.php');
    $pb = new PushBots();
    // Application ID
    $appID = '570ab8464a9efaf47a8b4568';
    // Application Secret
    $appSecret = '297abd3ebd83cd643ea94cbc4536318d';
    $pb->App($appID, $appSecret);
 
    // Notification Settings
    $pb->Alert($note);
    $pb->Platform(1);  // android
    // Push it !
    $res = $pb->Push();
    echo($res['status']);
    echo($res['code']);
    echo($res['data']);
}

?>
