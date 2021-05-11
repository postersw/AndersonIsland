<?php
/////////////////////////////////////////////////////////////
//  gettanneralerts - gets the outage status from https://odin.ornl.gov/odi/nisc/centrallincolnpud
//  web site and writes it to tanneroutage.txt.
//  this file is picked up by the getalerts.php script and sent to the app.
//  format is:
//      <PubOutages xmlns="http://iec.ch/TC57/2014/PubOutages#">
//          <Outage>
//              <mRID>235c3081-04be-4179-b716-50eeed0bffeb</mRID>
//              <communityDescriptor>53053</communityDescriptor>
//              <metersAffected>6</metersAffected>
//              <OutageArea>
//                  <earliestReportedTime>2021-04-16T17:14:56.000Z</earliestReportedTime>
//                  <metersServed>20655</metersServed>
//                  <outageAreaKind>county</outageAreaKind>
//              </OutageArea>
//          </Outage>
//      </PubOutages>
//  CRON: */4 * * * * 	/usr/local/bin/php -q /home/postersw/public_html/gettanneralerts.php
//  RFB. 4/16/21
//       5/7/21. Production live.
//       5/10/21. Debugging info sent to stdout.
//
date_default_timezone_set("America/Los_Angeles"); // set PDT
    $tanneroutagelink = "https://odin.ornl.gov/odi/nisc/tannerelectric";
    $tanneroutagefile = "tanneroutage.txt";
    $tweet = "<br/><a href='http://twitter.com/tannerelectric'>Tap for Twitter feed</a>";
    $tannerreply = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><PubOutages xmlns="http://iec.ch/TC57/2014/PubOutages#"/>';  // reply from odin.ornl
    $msg =   date("g:i a") . ": No Outages.";
    //$AI = "<communityDescriptor>53053</communityDescriptor>";  // pierce county FIPS number
    $AI = "<communityDescriptor>Anderson Island</communityDescriptor>";  // pierce county FIPS number
    chdir("/home/postersw/public_html");  // move to web root

    $str = file_get_contents($tanneroutagelink); // read the input
    if($str == "") $str = file_get_contents($tanneroutagelink);  // try again if no result
    if($str == "") $str = file_get_contents($tanneroutagelink);
    if($str == "") exit("gettanneralerts: No reply from odin.ornl.gov/odi/nisc/tannerelectric");
    $strl = strlen($tannerreply);
    if(substr($str, 0, $strl) != $tannerreply) exit("gettanneralerts: incorrect tanner reply: $str");

    // look for ANY   context past the header
    if($str == $tannerreply){  // if there is no reply past the header we assume no outage, which I don't like.
        file_put_contents($tanneroutagefile, $msg . $tweet);
        exit(0);
    }

    // there is a tanner outage, but not necessarily AI. 
    echo $str;  // display the string I get // DEBUG FOR NOW
    $i = strpos($str, $AI);  // pierce county Community Descriptor
    if($i===FALSE){  // if there is no AI community descriptor we assume no outage, which I don't like.
        file_put_contents($tanneroutagefile, $msg . $tweet);
        exit(0);
    }

    // There is an AI reply, so we assume an AI outage. 
    // Now extract the metersAffected and metersServed that occurs after the <communityDescriptor> structure.

    $str = substr($str, $i + strlen($AI)); // 
    $nbrOut = TagValue($str, "metersAffected");
    if($nbrOut = "") {
       $msg =  "<span style='color:red;font-weight:bold'>" . date("g:i a") . " OUTAGE in progress. Tap for Map.</span>";
    } else {
        $nbrServed == TagValue($str, "metersServed");
        if($nbrServed == "") $nbrServed = 1100;
        $msg =  "<span style='color:red;font-weight:bold'>" . date("g:i a") . " OUTAGE: " . $nbrOut . " Houses Out (" . (int)($nbrOut/$nbrServed*100) . "%). Tap for Map.</span>";
    }
    file_put_contents($tanneroutagefile, $msg . $tweet);
    echo $msg;  // debug
    return 0;

//////////////////////////////////////////////////////////////////////
// TagValue returns the string value in the xml tag between <tag> and </tag>
//  entry $s = string
//          $tag = xml tag without <>
// Returns  string between tags, or error message
function TagValue($s, $tag, $is) {
    $i = strpos($s, "<" . $tag . ">", $is);
    if($i==FALSE) return "";
    $taglen = strlen($tag) + 2;
    $j = strpos($s,  "</" . $tag . ">", $i);
    if($j==FALSE) exit("ERR: </".  $tag . "> not found");
    return substr($s, $i+$taglen, $j-$i-$taglen);
    }

?>