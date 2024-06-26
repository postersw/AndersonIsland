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
// The format of tannersave.json is json and loads the $SAVED array:
//              "outagestarttime"  hh:mm dow  string and is set when an outage starts and cleared whenever there is no outage.
//  The cron script will email me every time the status changes.
//
//  This job is run by cron every 5 minutes:
//    CRON: */5 * * * * 	/usr/local/bin/php -q /home/postersw/public_html/gettanneralerts.php
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
//       6/20/22. Fixed for change to status format.
//       11/4/22. Issue error if 'hasError'=true.
//       12/10/22. Implement tannersavefile.json to persist data in $SAVED
//       12/18/22. Go back to status unavailable if hasError=true
//       6/3/23.   Name change to TANNER ELECTRIC COOP
//       6/22/23.  Remove call to check status because it wasn't working reliably.
//

    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $tanneroutagelink = "https://odin.ornl.gov/odi/nisc/tannerelectric";
    $tanneroutagefile = "tanneroutage.txt";  // one line outage info for the app
    $tanneroutagelog = "tanneroutagelog.txt";  // log file 
    //$tanneroutagetimefile = "tanneroutagetime.txt";
    $savefile = "tannersave.json"; // json persistant save file
    $tweet = "<br/><a href='http://twitter.com/tannerelectric'>Tap for Twitter feed</a>";
    $tannerreply = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><PubOutages xmlns="http://iec.ch/TC57/2014/PubOutages#"/>';  // reply with NO outage
    $tannerreplyoutage = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><PubOutages xmlns="http://iec.ch/TC57/2014/PubOutages#">';  // reply if there is an outage
    $tannernooutage = '<PubOutages><outage/></PubOutages>';
    $tannererror = ""; // no error
    $shorttime = date("g:i a");
    $shortdate = date("m/d/y");
    $dateday = date("g:i a D");
    $realdate = date("m/d/y g:i a");

    $AI = "<communityDescriptor>53053</communityDescriptor>";  // pierce county FIPS number
    //$AI = "<communityDescriptor>Anderson Island</communityDescriptor>";  // AI identifier
    chdir("/home/postersw/public_html");  // move to web root
    $SAVED = json_decode(file_get_contents($savefile), TRUE); // load persistant data

    $oldmsg = file_get_contents($tanneroutagefile);
    $str = file_get_contents($tanneroutagelink); // read the outage status
    if($str=="") $tannererror = "No reply to $tanneroutagelink. ";
    $uts = 0;

    // get the status of the last time
    // if a status error, ignore the return status and treat it as a tanner outage. Short term bug fix 11/30. Remove when tanner fixes the communityDescriptor.
    // $uts = gettimeoflaststatus();
    // if($uts==0) {  // if a status error, ignore the return status and treat it as a tanner outage. Short term bug fix 11/30. Remove when tanner fixes the communityDescriptor.
    //     //echo $tannererror;
    //     $str = "";  // 11/30. Treat as an outage.
    //     ///$msg =  "<span style='color:red;font-weight:bold'>$shorttime OUTAGE: Tap for Map.</span>";
    //     $msg = $shorttime . ": Status Unavailable. Tap for Map.<p hidden>No Outages</p>";
    //     file_put_contents($tanneroutagefile, $msg . $tweet);
    //     exit(0);
    // }


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
            echo "$shortdate $shorttime Tanner Status 'Unavailable'. $tannererror Was '$oldmsg'";
            file_put_contents($tanneroutagelog, "$realdate $shortdate $shorttime No Response. $tannererror date=$statusdate \n", FILE_APPEND);  // log it
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
        //$outagestarttime = file_get_contents($tanneroutagetimefile);  // read saved outage start time
        $outagestarttime = $SAVED["outagestarttime"]; 
        //if($outagestarttime!="") file_put_contents($tanneroutagetimefile, "");  // clear any outage time
        if($outagestarttime!=0) {   // clear the outage start time
            $SAVED["outagestarttime"] = "";
            SaveData();
        }; 
        exit(0);
    }

    // check for an outage reply
    // $strl = strlen($tannerreplyoutage);
    // if(substr($str, 0, $strl) != $tannerreplyoutage) exit("gettanneralerts: incorrect tanner reply: $str");
    
    // there is a tanner outage, but not necessarily AI. 

    $i = strpos($str, $AI);  // AI Community Descriptor
    if($i===FALSE){  // if there is no AI community descriptor we assume no outage, which I don't like.
        // NO AI OUTAGE
        if(strpos($oldmsg, "No Outages.")=== false) {  // if previous status was an outage, log the change
            echo "$shortdate $shorttime Tanner Status 'No Outages' starting now. Was '$oldmsg'";
            file_put_contents($tanneroutagelog, "$realdate $shortdate $shorttime No Outages \n", FILE_APPEND);  // log it
        }
        file_put_contents($tanneroutagefile, $msg . $tweet);
        //$outagestarttime = file_get_contents($tanneroutagetimefile);  // read saved outage start time
        //if($outagestarttime!="") file_put_contents($tanneroutagetimefile, "");  // clear any outage time
        $SAVED["outagestarttime"] = "";
        SaveData();  // save it
        exit(0);
    }

    // There is an AI reply, so we assume an AI outage. 
    // Now extract the metersAffected and metersServed that occurs after the <communityDescriptor> structure.

    $str = substr($str, $i + strlen($AI)); // 
    $nbrOut = TagValue($str, "metersAffected");  // number out

    // get or set outage start time
    $outagestarttime = "";
    if(array_key_exists("outagestarttime", $SAVED)) $outagestarttime = $SAVED["outagestarttime"]; //file_get_contents($tanneroutagetimefile);  // read saved outage start time
    if($outagestarttime=="") { // if no start time, create one
        $outagestarttime = $dateday; // hh:mm am ddd, eg 8:05 am Sun
        $SAVED["outagestarttime"] = $outagestarttime;
        //file_put_contents($tanneroutagetimefile, $outagestarttime);  // save the outage start time
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
    }

    SaveData();  // save $SAVED array
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
//          0 if no tanner time stamp or if a hasError = true.
// https://odin.ornl.gov/odi/status returns json data structure:
// {"receivedDate":"2021-09-14T20:11:09Z","utility":"tannerelectric","vendor":"nisc"},
// {"receivedDate":"2022-06-21T00:10:24.117+00:00","eiaId":"tannerelectric","name":"Tanner Electric Coop","dataId":"b4778a9a-6350-48af-bc5f-053abae78da5","hasError":false},
    
function gettimeoflaststatus() {
    global $tannererror;
    $tannerstatus = "https://odin.ornl.gov/odi/status";
    $str = file_get_contents($tannerstatus);
    if(($str===false) || ($str == "")) {
        $tannererror = "No response to $tannerstatus";
        return 0;
    }
    
    // look for tanner time stamp
    $i = strpos($str, 'TANNER ELECTRIC COOP');
    if($i===false) {
        $tannererror =  "No tannerelectric time stamp from $tannerstatus: $str";
        return 0;
    }
    //echo substr($str, $i, 200);

    // check the hasError flag
    $j = strpos($str, "hasError", $i);
    $hasError = substr($str, $j+10,4); 
    if($j===false) echo " Tanner hasError flag not found";
    elseif ($hasError != "fals") {  // if an error
        $tannererror = " Tanner has error=true.";
        return 0;
    }

    date_default_timezone_set("UCT"); // set UCT
    
    // get time time and convert it to a unix time stamp
    $iDate = strrpos($str, "receivedDate", $i-strlen($str));  //search backward from $i to find receivedDate
    $ts = substr($str, $iDate+15, 20);
    //echo "iDate=$iDate, ts=$ts\n";
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

////////////////////////////////////////////////////////////////////////////////////
//  SaveData - save the data in the global $SAVED array
function SaveData() {
    global $SAVED, $savefile;
    file_put_contents($savefile, json_encode($SAVED));  // save persistant data,
}
?>