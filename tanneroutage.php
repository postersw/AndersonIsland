<?php
if ($request->getMethod() !== 'POST') {
    http_response_code(405);
    exit('Use POST method.');
}

$target_dir = ""; //"uploads/";
$target_file = $target_dir . "tanneroutage.xml";  // saved xml file copy of upload
$logfile = "tannerlogfile.txt"; // log file
$outagefile = "tanneroutage.txt";  // alert file sent to app
$uploadOk = 1;


// Check if file already exists
//if (file_exists($target_file)) {
//    echo "Sorry, file already exists.";
//    $uploadOk = 0;
//}
// Check file size
//if ($_FILES["fileToUpload"]["size"] > 10000) {
//    echo "Sorry, your file is too large.";
//    $uploadOk = 0;
//}
// Allow certain file formats
//if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
//&& $imageFileType != "gif" ) {
//    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
//    $uploadOk = 0;
//}
// Check if $uploadOk is set to 0 by an error
//if ($uploadOk == 0) {
//    echo "Sorry, your file was not uploaded.";
//   exit("File not uploaded");
//}

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
if(!strpos($msg, "ERR:") ) file_put_contents($outagefile, $msg);  // log it
echo "<br/>" . $msg . "<br/>";
exit;

///////////////////////////////////////////////////////////////////////////////////////
//  ProcessXMLFile (filename)  read the tanner xml file and decode it
//  returns string with the outage message, OR "ERR <error message>"
//  looks for tags <nbrOut>  and <nbrServed>
//  Entry $f = file name to read
//  Exit: returns string with the outage message, OR "ERR <error message>"
function ProcessXMLFile($f) {
    $s = file_get_contents($f);
    if(stlen($s)==0) return "ERR: 0 length file";
    if(!strpos($s, "<outageSummary")) return "ERR: not outageSummary";
    $nbrOut = TagValue($s, "nbrOut");  // get the tag value
    if(strpos($nbrOut, "ERR:") ) return $nbrOut;
    $nbrServed = TagValue($s, "nbrServed");  // get the tag value
    if(strpos($nbrServed, "ERR:") ) return $nbrServed;
    if($nbrOut == 0) {  // if no outages
        $msg =  date("H:i") . ": No Outages.";
    } else {
        $msg =  "<span style='color:red;font-weight:bold'>" . date("H:i") . " OUTAGE: " . $nbrOut . " Out (" . (int)($nbrOut/$nbrServed*100) . "% Out). Tap for info.</span>";
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
    $i = strpos($s,  "</" . $tag . ">", $i);
    if($i==0) return "ERR: </".  $tag . "> not found";
    return substr($s, $i+$taglen, $j-$i-$taglen);
}
//    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
//        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
//    } else {
//        echo "Sorry, there was an error uploading your file.";
//    }
//}
?>