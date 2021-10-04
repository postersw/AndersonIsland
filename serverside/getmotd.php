<?php
//////////////////////////////////////////////////////////////////////////////
// getmotd.php merges the motd.txt file into the dailycache.txt file every night.
//  run by cron at 11pm nightly.
// ALWAYS replaces all messages between MOTD and the ending /nl
// Strips out all \n.   Always end a message with a </br>
// input file: dailycache.txt.   If there is an MOTD, it MUST be in line 2 as MOTD, and the MOTD must immediately follow it until the next \n.
// input file: motd.txt
//  <MOTD>
//  Optional motd messages to always include. only 1 line up to the /nl which is stripped.
//  </MOTD>
//  <DATE mmddstart mmddend>
//   optional motd message to include starting mmddstart, and ending mmddend.  only 1 line up to the \n. \n is stripped.
//  </DATE>
//  .... repeated as necessary
//  <MOTDLAST>
//   optional messages to include after the scheduled messages. Only 1 line up to the \n
//  </MOTDLAST>
//
//  10/3/21. RFB. Initial version.
//

$test = true;
$motdfile = "motd.txt";
$dailycachefile = "dailycache.txt";
if($test) $dailycachefile = "dailycache_test.txt";
date_default_timezone_set('America/Los_Angeles');

// read daily cache and check first line for DAILYCACHE
$dcf = fopen($dailycachefile, "r");
if($dcf==false) exit("$dailycachefile does not exist");
if(feof($dcf)) exit("$dailycachefile is empty");
$ln = fgets($dcf);
if($ln=="") exit("daily cache is empty");
if($ln!="DAILYCACHE\n") exit ("first line of $dailycachefile is not DAILYCACHE");
//$dcout = $ln;  // data to write back to DAILY CACHE

// check 2nd line for MOTD  & discard.  leave DC positioned after the MOTD
$ln = fgets($dcf);
if($ln=="MOTD\n") {
    $ln = fgets($dcf);// get the actual motd, which we will discard
}
$motdout = "";

// check motd.txt file
$motdf = fopen($motdfile, "r"); 
if($motdf==false) exit("No $motdfile");
// check 1st line of motd.txt for <MOTD>
$ln = fgets($motdf);
if($ln == "") exit("Empty $motdfile");  // if no motd file, just quite
if($ln != "<MOTD>\n")  exit("$motdfile missing &lt MOTD &gt and is skipped");
// check 2nd line of motd.txt for </MOTD>
$ln = fgets($motdf);
if($ln != "</MOTD>\n") {
    $motdout = substr($ln, 0, strlen($ln)-1); // remove trailing \n
    $ln = fgets($motdf);  // get next line
}
if($ln != "</MOTD>\n")  exit("$motdfile missing &lt /MOTD &gt");


// check for date rows:   <DATE yymmdd,yymmdd>\n msg \n</DATE> ...
while(true) {
    $ln = fgets($motdf);
    if(substr($ln, 0, 5)== "<DATE") {
        $dates = explode(" ", $ln);  // get the dates
        $ln = fgets($motdf);
        if($ln == "</DATE>\n") continue;  // if no actual <DATE line, skip it
        echo ("ds=$dates[1], de=$dates[2].  ");
        if(checkmotddate(preg_replace('~\D~', '', $dates[1]), preg_replace('~\D~', '', $dates[2]))) {  // if date is active
            $motdout .= substr($ln, 0, strlen($ln)-1);  // add line without \n
        }
        $ln = fgets($motdf);  // read line after msg. should be </DATE>
        if($ln != "</DATE>\n") exit("no ending /Date for $ln");
    }
    else break;
}

// check for row after the date rows
if($ln != "") {
    if($ln != "<MOTDLAST>\n") exit("ill formed MOTDLAST: $ln");
    $ln = fgets($motdf); // get motdlast
    if($ln != "</MOTDLAST>\n")  {
        $motdout .= substr($ln, 0, strlen($ln)-1);  // add line without \n
        $ln = fgets($motdf);
    }
    if($ln != "</MOTDLAST>" && $ln != "</MOTDLAST>\n" ) exit("no closing /MOTDLAST: $ln");
}

fclose($motdf);   // close modfile
echo ("MOTD:\n$motdout <br/>\n");  // if there is an motd
flushdailycache($motdout);
exit();


//////////////////////////////////////////////////////////////////////////////////
//  flushdailycache - writes $motdout to dailycache.txt as MOTD, then copies the rest of dailycache.txt , replacing the original
//  entry   $motdout = motd string to add to daily cache, with NO \n
//          $dcf = daily cache file handle
//          $dcout = data to write back to dailycachefile
//          $dailycachefile = daily cache file name
//
function flushdailycache($motdout) {
    global $dcf, $dailycachefile;
    $i = 0;
    if($motdout != "") {
        $dcout  = "DAILYCACHE\nMOTD\n" . $motdout . "\n";
    } else     $dcout = "DAILYCACHE\n";

    // read dailycache into $dcout
    while (!feof($dcf)) {
        $ln = fgets($dcf);
        $i++;
        if($ln=="MOTD\n") exit("MOTD is in dailycache.txt but not in line 2.");
        $dcout = $dcout . $ln;
    }
    fclose($dcf);
    // now copy dcout to dcf
    file_put_contents($dailycachefile, $dcout);
    echo("wrote $i lines to $dailycachefile ");
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  checkmotddate - returns true if the date is within date1 - date2
//
//  entry   dstart is start date, mmdd
//          dend is end date, mmdd
//  exit    true if current date is with date1-date2, else false
//
function checkmotddate($dstart, $dend) {
    echo ("checkmotddate $dstart $dend; ");
    $d1 = intval($dstart);
    $d2 = intval($dend);

    if($d1 > $d2) exit("end date $dend is < start date $dstart");
    if($d1 < 101 || $d1 > 1231) exit(" invalid start date $dstart");
    if($d2 < 101 || $d2 > 1231) exit(" invalid end date $dend");

    $dnow = date("md");  // get mmdd
    echo " date: $dnow ";
    echo ("d1=$d1, d2=$d2, dnow=$dnow. ");
    if(($d1>=$dnow) && ($d2<=$dnow)) return true;
    return false;    
}

?>