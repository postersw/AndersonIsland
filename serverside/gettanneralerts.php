<?php
/////////////////////////////////////////////////////////////
//  gettanneralerts - gets the outage status from https://odin.ornl.gov/odi/nisc/tannerelectric
//  web site, decodes it, and writes it to tanneroutage.txt.
//  tanneroutage.txt. is picked up by the getalerts.php script and sent to the app.
//  The outage message is also saved in tanneroutagelog.txt. 
// 
//  The XML format from odin is:
// <PubOutages xmlns="http://iec.ch/TC57/2014/PubOutages#">
//   <Outage>
//     <mRID>8479c252-d920-46cf-8a9a-1222245fabf5</mRID>
//     <communityDescriptor>Anderson Island</communityDescriptor>
//     <metersAffected>2</metersAffected>
//     <OutageArea>
//       <earliestReportedTime>2021-05-11T17:19:55.000Z</earliestReportedTime>
//       <metersServed>1246</metersServed>
//       <outageAreaKind>zipcode</outageAreaKind>
//     </OutageArea>
//   </Outage>
// </PubOutages>
//
// The format of tanneroutage.txt is:  time: No Outages. Tap for Map.
//                                or:  time OUTAGE: nn Houses Out (n%). Tap for map.
// The format of tanneroutagelog.txt is:  time OUTAGE: nn Houses Out (n%). \n
// The format of tanneroutagetime.txt is: hh:mm dow   and is set when an outage starts and cleared whenever there is no outage.
//  The cron script will email me every time the status changes.
//
//  This job is run by cron every 4 minutes:
//    CRON: */4 * * * * 	/usr/local/bin/php -q /home/postersw/public_html/gettanneralerts.php
//
//  RFB. 4/16/21
//       5/7/21. Production live.
//       5/11/21. Debugged for actual output.
//       5/13/21. Add outage start time.
//       6/14/21. Write to stdout for No response start or Outage start.
//       7/19/21. Always log date on state change.
//       8/08/21. Fixed 'No Outages' test so that state change is always logged.
//       9/14/21. Get date/time of last tanner feed from odin.
//       10/28/21. Don't log outage message if it hasn't changeed.
//

    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $tanneroutagelink = "https://odin.ornl.gov/odi/nisc/tannerelectric";
    $tanneroutagefile = "tanneroutage.txt";  // one line outage info for the app
    $tanneroutagelog = "tanneroutagelog.txt";  // log file 
    $tanneroutagetimefile = "tanneroutagetime.txt";
    $tweet = "<br/><a href='http://twitter.com/tannerelectric'>Tap for Twitter feed</a>";
    $tannerreply = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><PubOutages xmlns="http://iec.ch/TC57/2014/PubOutages#"/>';  // reply with NO outage
    $tannerreplyoutage = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><PubOutages xmlns="http://iec.ch/TC57/2014/PubOutages#">';  // reply if there is an outage
    $tannernooutage = '<PubOutages><outage/></PubOutages>';
    $shorttime = date("g:i a");
    $shortdate = date("m/d/y");
    $dateday = date("g:i a D");
    $realdate = date("m/d/y g:i a");

    //$AI = "<communityDescriptor>53053</communityDescriptor>";  // pierce county FIPS number
    $AI = "<communityDescriptor>Anderson Island</communityDescriptor>";  // AI identifier
    chdir("/home/postersw/public_html");  // move to web root

    $oldmsg = file_get_contents($tanneroutagefile);
    $str = file_get_contents($tanneroutagelink); // read the input

    // get the status of the last time
    $uts = gettimeoflaststatus();
    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $statusdate = "none";
    if($uts > 0) {
        $statusdate = date("m/d/y g:i a", $uts);
        $shorttime = date("g:i a", $uts);
        $shortdate = date("m/d/y", $uts);
        $dateday = date("g:i a D", $uts);
        //echo "shorttime=$shorttime, $shortdate, $dateday ";
    }
    $msg =   $shorttime . ": No Outages.";

    // NO RESPONSE - issue email first time. 
    if($str == "") {
        if(strpos($oldmsg, "Status Unavailable") === false) {
            echo "$shortdate $shorttime Tanner Status 'Unavailable'. No response to $tanneroutagelink.starting now. Was '$oldmsg'";
            file_put_contents($tanneroutagelog, "$realdate $shortdate $shorttime No Response. status date=$statusdate \n", FILE_APPEND);  // log it
        }
        $msg = $shorttime . ": Status Unavailable.<p hidden>No Outages</p>";  // the hidden 'No Outages' ensures that the tanner icon is not turned red.
        file_put_contents($tanneroutagefile, $msg . $tweet);
        exit();
        //exit("gettanneralerts: No reply from odin.ornl.gov/odi/nisc/tannerelectric");
    }

    $strl = strlen($tannerreply);


    // look for the NO OUTAGE reply
    if((strpos($str, $tannernooutage) > 0) || (substr($str, 0, $strl) == $tannerreply)){  // if there is no reply past the header we assume no outage, which I don't like.
        if(strpos($oldmsg, "No Outages.")=== false) {   // if previous status was an outage, log the change
            echo "$shortdate $shorttime Tanner Status 'No Outages' starting now. Was '$oldmsg'";
            file_put_contents($tanneroutagelog, "$realdate $shortdate $shorttime No Outages \n", FILE_APPEND);  // log it
        }
        file_put_contents($tanneroutagefile, $msg . $tweet);
        $outagestarttime = file_get_contents($tanneroutagetimefile);  // read saved outage start time
        if($outagestarttime!="") file_put_contents($tanneroutagetimefile, "");  // clear any outage time
        exit(0);
    }

    // check for an outage reply
    // $strl = strlen($tannerreplyoutage);
    // if(substr($str, 0, $strl) != $tannerreplyoutage) exit("gettanneralerts: incorrect tanner reply: $str");
    
    // there is a tanner outage, but not necessarily AI. 

    //echo $str;  // display the string I get // DEBUG FOR NOW
    $i = strpos($str, $AI);  // AI Community Descriptor
    //echo " <br/>community descriptor i = $i<br/>";
    if($i===FALSE){  // if there is no AI community descriptor we assume no outage, which I don't like.
        if(strpos($oldmsg, "No Outages.")=== false) {  // if previous status was an outage, log the change
            echo "$shortdate $shorttime Tanner Status 'No Outages' starting now. Was '$oldmsg'";
            file_put_contents($tanneroutagelog, "$realdate $shortdate $shorttime No Outages \n", FILE_APPEND);  // log it
        }
        file_put_contents($tanneroutagefile, $msg . $tweet);
        $outagestarttime = file_get_contents($tanneroutagetimefile);  // read saved outage start time
        if($outagestarttime!="") file_put_contents($tanneroutagetimefile, "");  // clear any outage time
        exit(0);
    }

    // There is an AI reply, so we assume an AI outage. 
    // Now extract the metersAffected and metersServed that occurs after the <communityDescriptor> structure.

    $str = substr($str, $i + strlen($AI)); // 
    $nbrOut = TagValue($str, "metersAffected");  // number out

    // get or set outage start time
    $outagestarttime = file_get_contents($tanneroutagetimefile);  // read saved outage start time
    if($outagestarttime=="") { // if no start time, create one
        $outagestarttime = $dateday; // hh:mm am ddd, eg 8:05 am Sun
        file_put_contents($tanneroutagetimefile, $outagestarttime);  // save the outage start time
        echo "$shortdate $shorttime Tanner Outage starting now. $nbrOut Out. Was '$oldmsg'";
    }
    if($nbrOut == "") {
       $msg =  "<span style='color:red;font-weight:bold'>$shorttime OUTAGE in progress since $outagestarttime. Tap for Map.</span>";

    } else {
        $nbrServed = TagValue($str, "metersServed");
        //echo "nbrServed=$nbrServed<br/>";
        if($nbrServed == "") $nbrServed = 1246;
        $msg =  "<span style='color:red;font-weight:bold'>$shorttime OUTAGE: " . $nbrOut . " Houses Out (" . (int)($nbrOut/$nbrServed*100) . "%) since $outagestarttime. Tap for Map.</span>";
    }

    // write out status for the app
    file_put_contents($tanneroutagefile, $msg . $tweet);

    // log it if a change from $oldmsg
    $oldmsginspan = TagValue($oldmsg, "span");
    $msginspan = TagValue($msg, "span");
    //echo (" oldmsginspan=$oldmsginspan, msginspan=$msginspan ");  // debug
    if(($oldmsginspan=="") || (substr($msginspan, 10) != substr($oldmsginspan, 10))) {   // if a change in status (skip time)
        file_put_contents($tanneroutagelog,"$realdate $msg  status-date=$statusdate \n", FILE_APPEND);  // log it
        echo(" LOGGED MSG.");
    }

    return 0;

//////////////////////////////////////////////////////////////////////
// TagValue returns the string value in the xml tag between <tag> and </tag>
//  entry $s = string
//          $tag = xml tag without <>
// Returns  string between tags, or "" if tag not found
//   <tag ...>value</tag>
//          12345678    start at 2+1, lenght=8-2-1
function TagValue($s, $tag) {
    //$i = strpos($s, "<" . $tag . ">");
    $i = strpos($s, "<" . $tag);
    if($i===FALSE) return "";
    $i = strpos($s, ">", $i+2);
    //$taglen = strlen($tag) + 2;
    $j = strpos($s,  "</" . $tag . ">", $i);
    if($j==FALSE) exit("ERR: </".  $tag . "> not found");
    return substr($s, $i+1, $j-$i-1);
    }

//////////////////////////////////////////////////////////////////////
//  gettimeoflaststatus() get time of last status from the odin status endpoint
//
//  returns unix timestamp of the tanner feed from odin ornl gov odi status.
//          0 if no tanner time stamp
// https://odin.ornl.gov/odi/status returns 
// {"receivedDate":"2021-09-14T20:11:09Z","utility":"tannerelectric","vendor":"nisc"},
    
function gettimeoflaststatus() {
    $tannerstatus = "https://odin.ornl.gov/odi/status";
    $str = file_get_contents($tannerstatus);
    if($str == "") {
        //echo "No response to $tannerstatus";
        return 0;
    }
    //echo $str . "\n"; // debug
    // look for tanner time stamp
    $i = strpos($str, '"utility":"tannerelectric"');
    //echo "i=$i \n";
    if($i===false) {
        echo "No tanner time stamp from $tannerstatus: $str";
        return 0;
    }
    date_default_timezone_set("UCT"); // set UCT
    // get time time and convert it to a unix time stamp
    //$ts = strrpos($str, "receivedDate", -(strlen($str)-$i));  //search backward from $i
    //echo "ts=$ts \n";
    $ts = substr($str, $i-22, 20);
    //echo "ts=$ts \n";
    $uts = strtotime($ts);  // unit time stamp in GMT in SECONDS
    //echo "uts=$uts \n";
    //echo date("m/d/y H:i:s \n", $uts); 
    // calculate difference between now and time stamp
    $now = time();
    $delta = ($now - $uts)/60.0;
    //echo "now=$now, delta=$delta \n";
    // if > 20 minutes, log error, and return error
    if(($now - $uts)> 20*60) echo ("time > 20 min");
    // return time in
    return $uts; 
}
?>