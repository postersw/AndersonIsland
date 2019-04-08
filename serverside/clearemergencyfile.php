<?php
//////////////////////////////////////////////////////////////////////////////
// clearemergencyfile.php - clears the emergencymessage.txt file.
//      clearemergencyfile.php?pw=<password> [&msg=the emergency message with %20 or + for spaces];
//  To display an emergency alert message: create text in file 'emergencymessage.txt'.  End with a <br/>.
//  To request immediate refresh (app reload) of dailycache & coming events, create a unique text in 'refresh.txt'.
//      it will be cleared nightly by getgooglecalendarcron.php.
//
//
//  2/13/19. RFB.
$emergencyfile = "emergencymessage.txt";
$pw = $_GET['pw'];
if($pw != '2538480467') die("invalid pw");

if(file_exists($emergencyfile)) unlink($emergencyfile);
echo date("m/d/Y h:i:s") . ": " . $emergencyfile . " cleared.";

// if there is text, save it
if(array_key_exists("msg", $_GET)) {
    $msg =  $_GET['msg'];
    file_put_contents($emergencyfile, $msg);
    echo "Wrote: " . $msg;
}

?>