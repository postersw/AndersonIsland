<?php
/////////////////////////////////////////////////////////////////////////////////////
//  getdailycache.php - retrieves dailycache.txt, comingevents.txt, tidedata.txt
//  concolidates daily feeds in one big pull, rather than 3 small ones.
//  rfb. 6/6/16.
//  rfb. 5/15/22. Add access control allow origin   
//  rfb. 8/30/22. Revised to allow: <include filename>  as a file to copy
header("Access-Control-Allow-Origin: *");  // added 5/15/22
$ns1 = hrtime(true);
$ns2 = hrtime(true);
copybyline("dailycache2.txt");
$ns3 = hrtime(true);
//echo "\r copy all at once=" . ($ns2-$ns1)/1000000000 . "\r copy by line=" . ($ns3-$ns2)/1000000000;

// the following were changed to include files:
    //copyfile("moondata.txt","MOON");   // moon data
    //copyfile("comingevents.txt", "COMINGEVENTS");
    //copyfile("tidedata.txt", "TIDES");
// log it
date_default_timezone_set("America/Los_Angeles");  // write the time in PDT/PST
$log = 'dailycachelog.txt';
$tlh = fopen($log, 'a');
fwrite($tlh, date('c') . ',V=' . $_GET['VER'] . ",K=" . $_GET['KIND'] . ',N='  . $_GET['N'] . ',P=' . $_GET['P'] . ',I='. $_SERVER['REMOTE_ADDR'] . "\n");
return;

//////////////////////////////////////////////////////////////////////////////
// copyfile from file to stdout
//  diskfile = file name
//  label = label that surrounds the content
// function copyfile($diskfile, $label) {
//     $theData = file_get_contents($diskfile);
//     if($theData <> "" ) {
//         if($label <> "" ) echo $label . "\n";
//         echo $theData;
//         if($label <> "" ) echo $label . "END\n";
//     }
// }

//////////////////////////////////////////////////////////////////////////////
//  copybyline - copy file to stdout, but allow includes of the form '<include filename>'
//  diskfile = file name to copy
//  exit    file echoed to output
function copybyline($diskfile) {
    $file = fopen($diskfile, "r");
    while($b = fgets($file)) {
        if(substr($b, 0, 9) == "<include ") { // process include recursively
            $j = strpos($b, ">");
            if($j == false) exit("invalid include $b");
            $includefile = substr($b, 9, $j-9);
            copybyline($includefile);  // copy the referred-to file
        } else {
            echo $b;
        }
    }
    fclose($file);
}


?>