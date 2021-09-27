<?php
//////////////////////////////////////////////////////////////////////////////
// getmotd.php merges the motd.txt file into the dailycache.txt file every night.
//  run by cron at 11pm nightly.
// ALWAYS replaces all messages between MOTD and the ending /nl
// Strips out all \n.   Always end a message with a </br>
//
// input file: motd.txt
//  <MOTD>
//   motd messages to always include. only 1 line up to the /nl which is stripped.
//  </MOTD>
//  <DATE mmddstart,mmddend>
//   motd message to include starting mmddstart, and ending mmddend.  only 1 line up to the \n. \n is stripped.
//  </DATE>
//  .... repeated as necessary
//  <MOTDLAST>
//   messages to include after the scheduled messages. Only 1 line up to the \n
//  </MOTDLAST>
//
//  2/11/19. RFB. Added REFRESH and emergencymessage.txt.
//  1/1/21.  RFB. Added FERRYPOSITION

// special case for FERRY. Put the EmergencyMessage file in front of the ferry alert.
//
//copyfile("alert.txt", "FERRY");
$motdfile = "motd.txt";
$dailycachefile = "dailycache.txt";

$motd = "";
if(!file_exists($motdfile)) exit(0);

$motdf = fopen($motdfile, "r"); 
if($motd == "") exit(0);

$dc = file_get_contents($dailycachefile);
if($dc=="") {
    echo "can't get daily cache";
    exit("cant get daily cache");
}

// check first line for DAILYCACHE
$ln = getnextline($dc);
if($ln=="") exit("daily cache is empty");
if($ln!="DAILYCACHE") exit ("first line is not DAILY CACHE");
$dcout = "DAILYCACHE\n";

// check 2nd line for MOTD  & discard
$ln = getnextline($dc);
if($ln=="MOTD") {
    $ln = getnextline($dc); // get motd, which we will discard
}

// check 1st line for <MOTD>
$ln = fgets($motdf);
if($ln != "<MOTD>") {
    flushdailycache();  // if not MOTD, flush the cache
    exit();
}

// add MOTD contents
$ln = getnextline($motd);  // either motd lines or </MOTD>
if($ln == "</MOTD>") {
    flushdailycache();
    exit();
}
$dcout = "MOTD\n$ln"; // add motd
$ln = getnextline($motd);
if($ln != "</MOTD>") {exit("no /MOTD after motd line");}

// check for date rows
while(true) {
    $ln = getnextline($motd);
    if(substr($ln, 0, 5)== "<DATE") {
        if(checkdate($ln)) {
            $ln = getnextline($motd);
            $dcout .= $ln;  // add line
        }
        $ln = getnextline($motd);
        if($ln != "</DATE>") exit("no ending /Date for $ln");
    }
    else break;
}

// check for row after the date rows
if($ln != "") {
    if($ln != "<MOTDLAST>") exit("ill formed MOTDLAST: $ln");
    $ln = getnextline($motd); // get motdlast
    $dcout .= $ln;
    $ln = getnextline($motd);
    if($ln != "</MOTDLAST>") exit("no closing /MOTDLAST: $ln");
}

$dcout .= "\n";
flushdailycache();
exit();






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