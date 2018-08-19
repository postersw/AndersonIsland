<?php
/////////////////////////////////////////////////////////////////////////////////////
//  getdailycache.php - retrieves dailycache.txt, comingevents.txt, tidedata.txt
//  concolidates daily feeds in one big pull, rather than 3 small ones.
//  rfb. 6/6/16.

copyfile("dailycache.txt", "");
copyfile("moondata.txt","MOON");   // moon data
copyfile("comingevents.txt", "COMINGEVENTS");
copyfile("tidedata.txt", "TIDES");
// log it
date_default_timezone_set("America/Los_Angeles");  // write the time in PDT/PST
$log = 'dailycachelog.txt';
$tlh = fopen($log, 'a');
fwrite($tlh, date('c') . ',V=' . $_GET['VER'] . ",K=" . $_GET['KIND'] . ',N='  . $_GET['N'] . ',P=' . $_GET['P'] . ',I='. $_SERVER['REMOTE_ADDR'] . "\n");
return;

// copyfile from file to stdout
//  diskfile = file name
//  label = label that surrounds the content
function copyfile($diskfile, $label) {
    $theData = file_get_contents($diskfile);
    if($theData <> "" ) {
        if($label <> "" ) echo $label . "\n";
        echo $theData;
        if($label <> "" ) echo $label . "END\n";
    }
}


?>