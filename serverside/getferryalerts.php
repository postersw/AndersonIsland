<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  getferryalerts - gets the ferry alerts from the rss feed and saves the latest one in alerts.txt
//  Run by cron every 4 minutes.
//  Alerts are cleared after 4 hours or if they are removed from the rss feed.
//  To change the default message (from "") change $DefaultMessage in function ClearAlertFile().
//
//  Bob Bedoll. 3/13/16.
//		4/09/16. Changed alertfile to alert.txt
//      4/16/16. Added call to pushbots. activated for android only.
//      4/20/16. Shortened date displayed to user.
//      4/22/16. Added IOS.
//      7/02/16. Reformatted date as "Jul 02, 4:45p"
//      8/17/16. Added 'Default Message' to be displayed if there is no alert.
//      4/02/17. Add "DELAYED:" or "DELAYED nn MIN: " for late, behind, or delayed. This is displayed by the app.
//      5/24/18. Add call to OneSignal to send the alerts. I'll eventually remove Pushbots.
//      7/9/18. Modify pushbots call to not bump badge.
//      9/30/18. Add warning for Pushbots.
//      10/9/18. Removed Pushbots calls and code. All messages now sent by OneSignal. Saves $29/m.
//      5/13/21. Don't create a DELAY message if a run is Cancelled.
//
//  Sample RSS feed:
//<rss version="2.0">
//<channel>
//<title>Pierce County, WA - Alert Center - Pierce County Ferry Rider Alert</title>
//<link>http://www.co.pierce.wa.us/AlertCenter.aspx</link>
//<lastBuildDate>Sun, 02 Apr 2017 16:47:11 -0800</lastBuildDate>
//<description>Pierce County, WA - Get the latest alerts</description>
//<language>en-us</language>
//<item>
//<title>The ferry is currently running 22 minutes late.</title>
//<link>http://www.co.pierce.wa.us/AlertCenter.aspx?AID=548</link>
//<pubDate>Sat, 02 Jul 2016 16:58:06 -0800</pubDate>
//<description>The ferry is currently running 22 minutes late. Thank you for your patience.</description>
//</channel>
//</rss>
//
//  NOTIFICATION MESSAGE FORMAT SENT TO PHONE:
//  hh:mm DELAYED nn: <ferry message title>
//
//  NOTIFICATION MESSAGE WRITTEN TO ALERT FILE:
//  hh:mm DELAYED nn: <ferry message title> \n <ferry message description>

chdir("/home/postersw/public_html");
//chdir("C:\A");////////////////// DEBUG for local PC //////////////////////////
date_default_timezone_set("America/Los_Angeles"); // set PDT
$alertclearhours = 4;  // hours to clear an alert
$alertfile = "alert.txt";  // alert file the phone reads
$alertlog = "alertlog.txt";
$alertrssurl = "http://www.co.pierce.wa.us/RSSFeed.aspx?ModID=63&CID=Pierce-County-Ferry-Rider-Alert-26"; // url for rss alert
$title = "";

//  $alertrssurl = "http://www.anderson-island.org/ferry_rsstest.txt";  // TEST URL/////// debug for local pc ///////////////
//  Read the RSS feed. Isn't this easy! php is great.
// try 10 times to get content
for($i=0; $i<10; $i++) {  // try 10 times
    $x = simplexml_load_file($alertrssurl);
    if($x===false) continue;  // if no data, try again
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
if(!property_exists($x->channel, "item")) { 
	ClearAlertFile($alertfile, $alertlog);
	exit(0); // if no title
}
$title = trim($x->channel->item[0]->title);
if($title=="") {
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

// if alert is >4 hrs old, clear it
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
if((strpos($title, " late") > 0) || (strpos($title, " behind") > 0) || (strpos($title, " delay") > 0) ) {
    $delay = "DELAYED: ";
    $matches = "";
    if(preg_match('/\d\d (minutes|minuets|mins|min) (late|behind)/', $title, $matches)) $delay = "DELAYED " . substr($matches[0], 0, 2) . " MIN: ";
    else if(preg_match('/delayed \d\d minutes/', $title, $matches)) $delay = "DELAYED " . substr($matches[0], 8, 2) . " MIN: ";
    else if(preg_match('/\d\d minute delay/', $title, $matches)) $delay = "DELAYED " . substr($matches[0], 0, 2) . " MIN: ";
}

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

// now send alert using Pushbots.  Removed 10/9/18.
//$pbwarn = "Upgrade to AIA ver 1.21 to continue receiving alerts: ";
//PushANotification(  $pbwarn . $alerthr . $alertmin . $alertam . " " . $delay . $title );

// send alert using OneSignal 5/24/18.  Message is 2 lines: The Delay, then the message
$msgtitle = "FERRY ALERT";
if($delay != "") $msgtitle = "FERRY " . $delay;
PushOSNotification($msgtitle, $alerthr . $alertmin . $alertam . " " . $title);
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
//  Removed 10/9/18. Deactivated because I switched to OneSignal to avoid paying $29/m.
//function PushANotification($note) {
//    // Push The notification with parameters
//    require_once('PushBots.class.php');
//    $pb = new PushBots();
//    // Application ID
//    $appID = '570ab8464a9efaf47a8b4568';
//    // Application Secret
//    $appSecret = '297abd3ebd83cd643ea94cbc4536318d';
//    $pb->App($appID, $appSecret);

//    // Notification Settings
//    $pb->Alert($note);
//    $pb->Platform(array("0","1"));  // android
//    $pb->Badge("0"); // DON'T BUMP BADGE 7/9/18
//    // Push it !
//    $res = $pb->Push();
//    echo($res['status']);
//    echo($res['code']);
//    echo($res['data']);
//}

/////////////////////////////////////////////////////////
//  PushOneSignalNotification. 5/25/18
//  Entry   title = message title
//          msg = the message
//  Users curl library.
//  https://documentation.onesignal.com/reference#create-notification
//  7/9/18: ios_badgeCount set to 0
//  4/7/19: Rest API Key moved from code to file in root after key was hacked.
//
function PushOSNotification($title, $msg) {
    require ('../private/OneSignal.php');
    $fields = array(
        'app_id' => "a0619723-d045-48d3-880c-6028f8cc6006",
        'included_segments' => array('Active Users'),
        'headings' => array("en" => $title),
        'contents' => array("en" => $msg),
        'ttl' => 4*3600,
        'ios_badgeType' => 'SetTo',
        'ios_badgeCount' => 0
    );
    $fields = json_encode($fields);
    print("\nJSON sent:\n");
    print($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    // "Authorization: Basic YOUR_REST_API_KEY (from the OneSignal web site for my app). After 4/7/19 this key must be set
    //  before the code is uploaded to the web site. 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', $RestAPIKey));
    //                                           'Authorization: Basic YWQyZmE5OGUtNGY0MC00OTAyLWEyOTYtMTUyZjVjZjEyNzA0'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    print($response);
    return $response;
}

?>
