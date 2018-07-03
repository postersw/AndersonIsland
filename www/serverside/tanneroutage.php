<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  tanneroutage - called by Tanner with a post that returns xml of the form:
//   [<?xml_version] => "1.0"
//<outageSummary xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
// coopID="0"><totals><nbrOut>0</nbrOut><nbrServed>4862</nbrServed></totals>
// <regions type="County"><region><id>North Bend</id><name></name><nbrOut>0</nbrOut><nbrServed>2175</nbrServed></region>
// <region><id>Ames Lake</id><name></name><nbrOut>0</nbrOut><nbrServed>1481</nbrServed></region
//<region><id>Anderson Island</id><name></name><nbrOut>0</nbrOut><nbrServed>1206</nbrServed></region></regions></outageSummary>

//if ($_REQUEST[REQUEST_METHOD] !== 'POST') {
//    http_response_code(405);
//    exit('Use POST method.');
//}

$target_dir = ""; //"uploads/";
$target_file = $target_dir . "tanneroutage.xml";  // saved xml file copy of upload
$logfile = "tannerlogfile.txt"; // log file
$outagefile = "tanneroutage.txt";  // alert file sent to app
$uploadOk = 1;
date_default_timezone_set("America/Los_Angeles"); // set PDT

// move the file without having to know its original name. Get the name from the array.
$logit = print_r($_FILES, TRUE) . "," . print_r($_POST, TRUE) . "," . print_r($_REQUEST, TRUE) . "," . print_r($_SERVER, TRUE);
// get the $POST reqeust as $xml
foreach($_POST as $p) {
    $xml = $p;
    break;
}
$logit = $logit . "\n XML: " . $xml;

$msg = ProcessXMLFile($xml);
file_put_contents($logfile, date("Y/m/d H:i:s") .  $msg . $logit );  // log it
if(!strpos($msg, "ERR:") ) file_put_contents($outagefile, $msg);  // write to outage file
exit;

///////////////////////////////////////////////////////////////////////////////////////
//  ProcessXMLFile (string)  read the tanner xml post and decode it
//  returns string with the outage message, OR "ERR <error message>"
//  looks for tags <nbrOut>  and <nbrServed>
//  Entry $s = xml string
//  Exit: returns string with the outage message, OR "ERR <error message>"
//function ProcessXMLFile($s) {
//    $s = file_get_contents($f);
//    echo "<pre>" . $s . "</pre>";
//    $xml=simplexml_load_string($f);
//    print_r($xml);
//    if($xml===false) return "ERR: Cannot create XML";
//    //echo "<br/>----------------------------<br/>" . $s . "<br/>--------------------------------------<br/>";
//    //if(!strpos($s, "<outageSummary")) return "ERR: not outageSummary";
//    //$nbrOut = TagValue($s, "nbrOut");  // get the tag value
//    echo "nbrout: " . $xml->nbrOut . ", nbrserved: " . $xml->nbrServed . "<br/>";
//    if($xml->nbrOut == 0) {  // if no outages
//        $msg =  date("H:i") . ": No Outages.";
//    } else {
//        $msg =  "<span style='color:red;font-weight:bold'>" . date("H:i") . " OUTAGE: " . $nbrOut . " Out (" . (int)($xml->nbrOut/$xml->nbrServed*100) . "% Out). Tap for info.</span>";
//    }
//    return $msg;
//}

///////////////////////////////////////////////////////////////////////////////////////
//  ProcessXMLFile (xml string)  read the tanner xml string and decode it
//  returns string with the outage message, OR "ERR <error message>"
//  looks for tags <nbrOut>  and <nbrServed>
//  Entry $f = file name to read
//  Exit: returns string with the outage message, OR "ERR <error message>"
function ProcessXMLFile($s) {
    global $logit;
    //$s = file_get_contents($f);
    //echo "<pre>" . $s . "</pre>";
    $i=strpos($s, "<outageSummary");
    if($i===FALSE) return "ERR: not outageSummary";
    $i = strpos($s, "Anderson Island");
    if($i===FALSE) return "ERR: Anderson Island not found";
    $nbrOut = TagValue($s, "nbrOut", $i);  // get the tag value
    $nbrServed = TagValue($s, "nbrServed", $i);  // get the tag value
    $logit = $logit . "\n nbrout: " . $nbrOut . ", nbrserved: " . $nbrServed . "\n";
    if((int)$nbrOut == 0) {  // if no outages
        $msg =  date("H:i") . ": No Outages.";
    } else {
        $msg =  "<span style='color:red;font-weight:bold'>" . date("H:i") . " OUTAGE: " . $nbrOut . " Houses Out (" . (int)($nbrOut/$nbrServed*100) . "%). Tap for info.</span>";
    }
    return $msg;
}

//////////////////////////////////////////////////////////////////////
// TagValue returns the string value in the xml tag between <tag> and </tag>
//  entry $s = string
//          $tag = xml tag without <>
// Returns  string between tags, or error message
function TagValue($s, $tag, $is) {
    $i = strpos($s, "<" . $tag . ">", $is);
    if($i==FALSE) return "ERR: <" . $tag . "> not found";
    $taglen = strlen($tag) + 2;
    $j = strpos($s,  "</" . $tag . ">", $i);
    if($j==FALSE) return "ERR: </".  $tag . "> not found";
    return substr($s, $i+$taglen, $j-$i-$taglen);
}

?>