<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  getferryalertsTEST - gets the ferry alerts from the rss feed and saves the latest one in alerts.txt
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
//      10/14/21. This fails now. simplexmlload returns null becasue CloudFlare security is blocking any script that can't prove it is javascript.
//      10/17/21. Temporary. RSS feed is read from file FerryRSSfile.txt, which is manually updated.
//      10/22/21. Use email instead of RSS feed.  RSS feed is commented out for now, till it works again. It is broken due to Cloudflare security.
//      10/27/21. Activated full email use.
//       5/16/22. Switched to use hornblower feed. 
//
//  Sample JSON feed: from  "https://us-central1-nyc-ferry.cloudfunctions.net/service_alerts?propertyId=hprcectyf";
//[{"createdDate":"1652555109859",
// "expirationDate":"1653177600000",
// "notificationBody":"Extreme low tides are predicted this summer beginning the week of May 16, 2022. ... \n\nRiders...\n",
// "notificationTitle":"Service Alert – Extreme Low Tides beginning Monday, May 16",
// "reasonForNotification":" ",
// "notificationType":" "},...
// ]
//
//  NOTIFICATION MESSAGE FORMAT SENT TO PHONE:
//  hh:mm DELAYED nn: <ferry message title>
//
//  NOTIFICATION MESSAGE WRITTEN TO ALERT FILE:
//  hh:mm DELAYED nn: <ferry message title> \n <ferry message description>

class Alert{
    public $title;  // the title line.  Includes link to full msg.
    public $notifymsg;  // msg to send via OneSignal.  No link.
    public $detail;  // detail displayed in detail msg.  If there is more than the title.
    public $timestamp; // date/time of alert, unix time stamp.
    public $expiration;  // expiration. unix time stamp.
}

chdir("/home/postersw/public_html");
//chdir("C:\A");////////////////// DEBUG for local PC //////////////////////////
date_default_timezone_set("America/Los_Angeles"); // set PDT
$alertclearhours = 4;  // hours to clear an alert
$alertfile = "alert.txt";  // alert file the phone reads
$alertlog = "alertlog.txt";

//  Get the alert. returned in $AlertObj
$AlertObj = getAlertsfromHornblower() ;
// no response so clear the alert file.
if($AlertObj->title == "") {
	ClearAlertFile($alertfile, $alertlog);
	logalertlast("no title");
	exit("No ferry alert title"); // if no reply
}
 
// check time. If alert is >4 hrs old, clear it and stop.
$t=time(); // current seconds in PDT I hope
$talert = $AlertObj->timestamp;
echo("  |alert timestamp:" . $talert . "|"); echo(date("Y-m-d-H-i-s", $talert));// debug
echo("  |current:" . $t . "|"); echo(date("Y-m-d-H-i-s", $t));// debug
$deltat = $t - $talert;
echo (" delta t hrs = " . ($deltat/3600));
// if alert is >4 hrs old, clear it and stop  TEMPORARILY DISABLED FOR DEBUG> REINSTATE IT.
if($deltat > ($alertclearhours*3600)) { // if > 4 hours old
    //echo " Alert is > $alertclearhours hours old ";
    ClearAlertFile($alertfile, $alertlog);
    exit(0);
}

// test for a delay
$delay = "";
$title = $AlertObj->title;
$desc = $AlertObj->detail;
if((strpos($title, " late") > 0) || (strpos($title, " behind") > 0) || (strpos($title, " delay") > 0) ) {
    $delay = "DELAYED: ";
    $matches = "";
    if(preg_match('/\d\d (minutes|minuets|mins|min) (late|behind)/', $title, $matches)) $delay = "DELAYED " . substr($matches[0], 0, 2) . " MIN: ";
    else if(preg_match('/delayed \d\d minutes/', $title, $matches)) $delay = "DELAYED " . substr($matches[0], 8, 2) . " MIN: ";
    else if(preg_match('/\d\d minute delay/', $title, $matches)) $delay = "DELAYED " . substr($matches[0], 0, 2) . " MIN: ";
}

// log it
if($AlertObj->notifymsg != $desc) $title = $title . " ...>";
$alertdatestring = date("m/d h:ia", $talert); // date/time of alert
$alertstring = $alertdatestring . " " . $delay . $title . "\n" . $desc;
//echo " alertstring=$alertstring|";

// exit if the message is not being changed.
$alc = file_get_contents($alertfile);  // read the alert file
if($alc == $alertstring) {
	logalertlast("alert already written");
	exit(0); // if already written
}

var_dump($AlertObj);
echo "<br/>alertstring: " . $alertstring;
exit(0);

// write it to the alertfile, and log it
$fh = fopen($alertfile, 'w');
fwrite($fh, $alertstring);
fclose($fh);

// log it
$fhl = fopen($alertlog, 'a');
fwrite($fhl, date("Y/m/d H:i:s") . "|" . $alertstring . "\n");
fclose($fhl);
echo ("wrote to file: " . $alertstring);
logalertlast("wrote to alert file");

// send alert using OneSignal 5/24/18.  Message is 2 lines: The Delay, then the message
$msgtitle = "FERRY ALERT";
if($delay != "") $msgtitle = "FERRY " . $delay;
$push = date("H:i:s", $talert) . " " . $AlertObj->notifymsg;
echo(" push=$push \n");
PushOSNotification($msgtitle, $push );
exit(0);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
///////////////////////////////////////////////////////////////////////////////////////////
//  getRSS - get the RSS feed.  Temporarily deprecated 10/20/21 due to CloudFlare security.
//  
//  exit    returns Alert object
// function getRSS() {
//     //$alertrssurl = "http://www.co.pierce.wa.us/RSSFeed.aspx?ModID=63&CID=Pierce-County-Ferry-Rider-Alert-26"; // url for rss alert
//     $alertrssurl = "https://www.piercecountywa.gov/RSSFeed.aspx?ModID=63&CID=Pierce-County-Ferry-Rider-Alert-26";  
//     $RSSfile = "FerryRSSfile.txt";  // temporary RSS file as of 10/17/21
//     //  $alertrssurl = "http://www.anderson-island.org/ferry_rsstest.txt";  // TEST URL/////// debug for local pc ///////////////
//     //  Read the RSS feed. Isn't this easy! php is great.
//     // try 10 times to get content
//     for($i=0; $i<3; $i++) {  // try 3 times
//         $x = simplexml_load_file($alertrssurl);
//         //if($x === false) exit(0);  // change made 10/15 because this fails now
//         if($x === false) {
//             echo " load failed from $alertrssurl. ";
//             $x = simplexml_load_file($RSSfile);  // change made 10/15 because this fails nowif($x===false) exit("No return from $alertrssurl");  // if no data, try again
//             if($x === false) echo " load failed from $RSSfile. ";
//         }
//         if(!property_exists($x->channel, "item")) { 
//             ClearAlertFile($alertfile, $alertlog);
//             exit(0); // if no title
//         }
//         $title = $x->channel->title;
//         echo("  x->channel->title=" . $x->channel->title);
//         if($title != "") break; // if we have content
//     }
//     $title = trim($x->channel->item[0]->title);
//     $desc = trim($x->channel->item[0]->description);

//     // get timestamp
//     $alertts = $x->channel->item[0]->pubDate;
//     $alertts = substr($alertts, 5);
//     $alertts[2] = "/";
//     $alertts[6] = "/";
//     $alertts[11] = ":";
//     //change Fri, 11 Mar 2016 21:30:08 -0800 into "10/Oct/2000:13:55:36 -0700"
//     // strip out day, and strip out -ms
//     //echo ("  |pubDate=" . $alertts . " " . $title);
//     // check expiration

//     $talert=strtotime($alertts); // covert to timestamp
//     //echo("  |talert:" . $talert . "  |"); echo(date("Y-m-d-H-i-s", $talert));// debug

//     // return the object
//     $alertobj = new Alert(); // create alert object
//     $alertobj->title = $title;
//     $alertobj->notifymsg = $title;
//     $alertobj->detail = $desc;
//     $alertobj->timestamp = $talert; // unix date stamp
//     return $alertobj;

// }

///////////////////////////////////////////////////////////////////////////////////////////
//  getAlertsfromHornblower - get the Hornblower web feed. works as of 5/16/22.
//  
//  exit    returns Alert object
//
//  Sample JSON feed: from  "https://us-central1-nyc-ferry.cloudfunctions.net/service_alerts?propertyId=hprcectyf";
//[{"createdDate":"1652555109859",
// "expirationDate":"1653177600000",
// "notificationBody":"Extreme low tides are predicted this summer beginning the week of May 16, 2022. ... \n\nRiders...\n",
// "notificationTitle":"Service Alert – Extreme Low Tides beginning Monday, May 16",
// "reasonForNotification":" ",
// "notificationType":" "},...
// ]
function getAlertsfromHornblower() {
    $alertrssurl = "https://us-central1-nyc-ferry.cloudfunctions.net/service_alerts?propertyId=hprcectyf";

    //  Read the RSS feed. Isn't this easy! php is great.
    $x = file_get_contents($alertrssurl);
    if($x === false) {
        echo " load failed from $alertrssurl. ";
        exit(0);
    }
    echo $x;

    $alertobj = new Alert(); // create alert object
    $a = json_decode($x);
    $i = count($a) - 1;  // last element
    $v = $a[$i];
    echo "<br/>i=$i";
    echo "<br/>created Date: " . $v->createdDate;
    echo "<br/>expriationDate: " . $v->expirationDate;
    echo "<br/>notification Title: " . $v->notificationTitle;
    echo "<br/>notification Body: " . $v->notificationBody;
    echo "<br/>________________________<br>";
    $i++;

    $alertobj->title = $v->notificationTitle;
    $alertobj->notifymsg = $v->notificationTitle;
    $alertobj->detail = $v->notificationBody;
    $alertobj->timestamp = intval($v->createdDate)/1000; // unix date stamp
    $alertobj->expiration = intval($v->expriationDate)/1000;
    return $alertobj;

    // foreach($a as $v) {
    //     echo "<br/>i=$i";
    //     echo "<br/>created Date: " . $v->createdDate;
    //     echo "<br/>expriationDate: " . $v->expirationDate;
    //     echo "<br/>notification Title: " . $v->notificationTitle;
    //     echo "<br/>notification Body: " . $v->notificationBody;
    //     echo "<br/>________________________<br>";
    //     $i++;

    //     $alertobj->title = $v->notificationTitle;
    //     $alertobj->notifymsg = $v->notificationTitle;
    //     $alertobj->detail = $v->notificationBody;
    //     $alertobj->timestamp = intval($v->createdDate); // unix date stamp
    //     return $alertobj;
    // }
    // return $alertobj;

}
?>
