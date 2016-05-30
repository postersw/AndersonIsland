<?php
//////////////////////////////////////////////////////////////////////////////
// getalerts.php - returns the current alerts from the alert files in a single data stream
//     so that we don't have to do multiple gets:
//  FERRY
//  <alert.txt file>
//  FERRYEND
//  BURNBAN
//  <burnban.txt file>
//  BURNBANEND
//  TANNER
//  <tanner.txt file>
//  TANNEREND
// if a file is empty or does not exist, the labels are omitted 

// read it from disk


copyfile("alert.txt", "FERRY");
copyfile("burnban.txt", "BURNBAN");
copyfile("tanneroutage.txt", "TANNER");
return;

// copyfile from file to stdout
//  diskfile = file name
//  label = label that surrounds the content
function copyfile($diskfile, $label) {
    $theData = file_get_contents($diskfile);
    if($theData <> "") {
        echo $label . "\n";
        echo $theData;
        echo $label . "END\n";
    }
}


?>