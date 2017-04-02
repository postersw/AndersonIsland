<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  getferryalerts - gets the ferry alerts from the rss feed and saves the latest one in alerts.txt
//  alerts are cleared after 4 hours or if they are removed from the rss feed.
//  To change the default message (from "") change $DefaultMessage in function ClearAlertFile().
//
//  Bob Bedoll. 3/13/16.
//		4/09/16. Changed alertfile to alert.txt
//      4/16/16. Added call to pushbots. activated for android only.
//      4/20/16. Shortened date displayed to user.
//      4/22/16. Added IOS.
//      7/02/16. Reformatted date as "Jul 02, 4:45p"
//      8/17/16. Added 'Default Message' to be displayed if there is no alert.
//      4/01/17. Add "DELAY: " for late, behind, or delayed. This keyword is a flag to the app to display DELAYED.
//  Sample RSS feed:
//<item>
//<title>The ferry is currently running 22 minutes late.</title>
//<link>
//http://www.co.pierce.wa.us/AlertCenter.aspx?AID=548
//</link>
//<pubDate>Sat, 02 Jul 2016 16:58:06 -0800</pubDate>
//<description>
//The ferry is currently running 22 minutes late. Thank you for your patience.
//</description>
//

chdir("/home/postersw/public_html");
date_default_timezone_set("America/Los_Angeles"); // set PDT
$alertclearhours = 4;  // hours to clear an alert
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
$title = trim($x->channel->item[0]->title);
if($title == "") {
	ClearAlertFile($alertfile, $alertlog);
	exit(0); // if no title
}

$desc = trim($x->channel->item[0]->description);
$alertts = $x->channel->item[0]->pubDate;
$alertts = substr($alertts, 5);
$alertts[2] = "/";
$alertts[6] = "/";
$alertts[11] = ":";
//change Fri, 11 Mar 2016 21:30:08 -0800 into "10/Oct/2000:13:55:36 -0700"
// strip out day, and strip out -ms
//echo ("  |pubDate=" . $alertts . " " . $title);
// check expiration

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
//$alertts  = substr($x->channel->item[0]->pubDate, 5, 7) . substr($x->channel->item[0]->pubDate, 17, 5);
$alertday  = substr($x->channel->item[0]->pubDate, 8, 3) . " " . substr($x->channel->item[0]->pubDate, 5, 2);
$alerthr = substr($x->channel->item[0]->pubDate, 17, 2);
$alertam = "a";
if($alerthr > "12") {  // convert to 12 hour
    $alerthr = $alerthr - 12;
    $alertam = "p";
}
$alertmin = substr($x->channel->item[0]->pubDate, 19, 3);
$alertdatestring = $alertday . ", " . $alerthr . $alertmin . $alertam;
//$alertdatestring = date("m/d h:ia", $talert); // 7/2 5:17pm This should have worked, but it didn't to DST correctly.

// test for a delay
$delay = "";
if((strpos($title, " late") > 0) || (strpos($title, " behind") > 0) || (strpos($title, " cancel") > 0) || (strpos($title, " delay") > 0) ) $delay = "DELAY: ";

if($title != $desc) $title = $title . " ...>";
$alertstring = $alertdatestring . " " . $delay . $title . "\n" . $desc;
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
fwrite($fhl, date("Y/m/d H:i:s") . "|" . $alertstring . "\n");
fclose($fhl);
echo ("wrote to file: " . $alertstring);
logalertlast("wrote to alert file");

// now send alert using Pushbots and Google Cloud Messaging
PushANotification(  $alerthr . $alertmin . $alertam . " " . $delay . $title );
exit(0);

/////////////////////////////////////////////////////////////////
//  ClearAlertFile - writes an empty string to the alert file
//
function ClearAlertFile($alertfile, $alertlog) {
    $DefaultMessage = "";
    //$DefaultMessage = "<span style='color:black;'>8/22: Both Ferry Lane Webcams are now UP.</span>";
     logalertlast("cleared alert file");
     $alc = file_get_contents($alertfile);
     //if($alc=="") return; // if already empty
     if($alc==$DefaultMessage) return; // if already empty

     $fh = fopen($alertfile, 'w');
     fwrite($fh, $DefaultMessage);
     fclose($fh);
     echo("ClearAlertFile cleared  $alertfile and wrote: $DefaultMessage");
     // log it
     $fhl = fopen($alertlog, 'a');
     fwrite($fhl, date("Y/m/d H:i:s") . " Cleared alert file and wrote: $DefaultMessage.\n");
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
    $pb->Platform(array("0","1"));  // android
    // Push it !
    $res = $pb->Push();
    echo($res['status']);
    echo($res['code']);
    echo($res['data']);
}

?>
