<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  tanneroutage - called by Tanner with a post that returns an xml file of the form:
//  xml version="1.0"
//  <nbrOut>20</nbrOut>
//  <nbrServed>1000</nbrServed>

//if ($request->getMethod() !== 'POST') {
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
echo "<pre>";
print_r($_FILES);
foreach($_FILES as $file) {
    echo "key=value: " . $file . " tmp_name: ". $file['tmp_name'] . "\n";
    $r = move_uploaded_file($file['tmp_name'], $target_file);
    break;
}

$msg = ProcessXMLFile($target_file);
file_put_contents($logfile, date("Y/m/d H:i:s") .  $msg);  // log it
if(!strpos($msg, "ERR:") ) file_put_contents($outagefile, $msg);  // write to outage file
echo "<br/>" . $msg . "<br/>";
exit;

///////////////////////////////////////////////////////////////////////////////////////
//  ProcessXMLFile (filename)  read the tanner xml file and decode it
//  returns string with the outage message, OR "ERR <error message>"
//  looks for tags <nbrOut>  and <nbrServed>
//  Entry $f = file name to read
//  Exit: returns string with the outage message, OR "ERR <error message>"
//function ProcessXMLFile($f) {
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
//  ProcessXMLFile (filename)  read the tanner xml file and decode it
//  returns string with the outage message, OR "ERR <error message>"
//  looks for tags <nbrOut>  and <nbrServed>
//  Entry $f = file name to read
//  Exit: returns string with the outage message, OR "ERR <error message>"
function ProcessXMLFile($f) {
    $s = file_get_contents($f);
    //echo "<pre>" . $s . "</pre>";
    if(!strpos($s, "<outageSummary")) return "ERR: not outageSummary";
    $nbrOut = TagValue($s, "nbrOut");  // get the tag value
    $nbrServed = TagValue($s, "nbrServed");  // get the tag value
    echo "nbrout: " . $nbrOut . ", nbrserved: " . $nbrServed . "<br/>";
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
function TagValue($s, $tag) {
    $i = strpos($s, "<" . $tag . ">");
    if($i==0) return "ERR: <" . $tag . "> not found";
    $taglen = strlen($tag) + 2;
    $j = strpos($s,  "</" . $tag . ">", $i);
    if($j==0) return "ERR: </".  $tag . "> not found";
    return substr($s, $i+$taglen, $j-$i-$taglen);
}

?>