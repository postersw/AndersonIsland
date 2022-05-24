<?php
//////////////////////////////////////////////////////////////////////////////
// getalerts.php - returns the current alerts from the alert files in a single data stream
//     so that we don't have to do multiple gets:
//  To display an emergency alert message: create text in file 'emergencymessage.txt'.  End with a <br/>.
//  To request immediate refresh (app reload) of dailycache & coming events, create a unique text in 'refresh.txt'. 
//      it will be cleared nightly by getgooglecalendarcron.php.
//
//  FERRY
//  <emergencymessage.txt file>  Note that this file should have a <br/> to separate it from the ferry message.
//  <alert.txt file>
//  FERRYEND
//  BURNBAN
//  <burnban.txt file>
//  BURNBANEND
//  TANNER
//  <tanner.txt file>
//  TANNEREND
//  FERRYPOSITION
//  <ferryposition.txt file>
//  FERRYPOSITIONEND
//  REFRESH
//  <time stamp from refresh.txt file, e.g. 5/15/22. 1214. This is a text string>
//  REFRESHEND
// if a file is empty or does not exist, the labels are omitted
//  Note about REFRESH: set this file to a unique value or timestamp. It will force a refresh/reload of dailycache.txt and the currentevents.txt file.
//
//  2/11/19. RFB. Added REFRESH and emergencymessage.txt.
//  1/1/21.  RFB. Added FERRYPOSITION
//  5/15/22  RFB. Added access control allow origin *

header("Access-Control-Allow-Origin: *");  // added 5/15/22

// special case for FERRY. Put the EmergencyMessage file in front of the ferry alert.
$emergencyfile = "emergencymessage.txt";
$d1 = "";
if(file_exists($emergencyfile)) $d1 = file_get_contents($emergencyfile);
$d2 = file_get_contents("alert.txt");
if($d1 <> "" || $d2 <> "") {
    echo "FERRY\n";
    echo $d1;
    echo $d2;
    echo "FERRYEND\n";
}

copyfile("burnban.txt", "BURNBAN");
copyfile("tanneroutage.txt", "TANNER");
copyfile("ferryposition.txt", "FERRYPOSITION");
copyfile("refresh.txt", "REFRESH");
return;

// copyfile from file to stdout
//  diskfile = file name
//  label = label that surrounds the content
function copyfile($diskfile, $label) {
    if(!file_exists($diskfile)) return;  // exit if file doesn't exist
    $theData = file_get_contents($diskfile);
    if($theData <> "") {
        if($label <> "") echo $label . "\n";
        echo $theData;
        if($label <> "") echo $label . "END\n";
    }
}


?>