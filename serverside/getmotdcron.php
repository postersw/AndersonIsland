<?php
//////////////////////////////////////////////////////////////////////////////
// getmotd.php writes the motd.txt file commands to motdinclude.txt file every night.
//  run by cron at 12:02am nightly.
//
// input file: motd.txt
//  <MOTD>
//  Optional motd messages to always include. only 1 line up to the \n,  \n is stripped.
//  </MOTD>
//  // comment always skipped.
//  <DATE mmddstart-mmddend[:hhmmend 24-hour endtime] [mmddstart-mmddend] ... >
//          separated by space or comma.
//          e.g. 0101-0131 0418,0503:1100,0610-0612:1300
//   optional motd message to include, starting mmddstart, and ending mmddend @ hhmmend
//            As many date ranges may be added as needed.  
//            The endtime is optional and is separated by a :, in 24 hour time., e.g. 0503:1100
//                   The message will be started at 0001 and removed the LAST day after hh:mmend. (but only every 4 hours.)
//            Only 1 line up to the \n. \n is stripped.
//  </DATE>
//  .... repeated as necessary
//  <MOTDLAST>
//   optional messages to include after the scheduled messages. Only 1 line up to the \n
//  </MOTDLAST>
//
//  exit: writes all relevant motd lines to the 'motdinclude.txt' file as one long line ending without \n.  All messages must end with <br/> to provide formatting.
//        the 'motdinclude.txt' file is included into the dailycache.txt file using a '<include motdinclude.txt>' line in dailycache.txt, where a newline is also generated.
//
//  10/3/21. RFB. Initial version.
//  7/21/22  RFB. Accept multiple date ranges for the same message.
//  8/30/22. RFB. Create an motdinclude.txt file.  Do not change dailycache.txt anymore.
//  9/4/22   RFB. Include "lowtidewarning.txt file.
//  9/6/22.  RFB. Remove 'lowtidewarning.txt' file include.  Remove \n from output. \n must be in the dailycache.txt file.
//  9/11/22. RFB. Accept // for comments.
//  10/3/22. RFB. Improve handling of // and blank lines.
//  12/15/23. RFB. Accept end time. Accept comma a delimiter.

$test = false;  // set true to go to dailycaCHE_test.txt
$motdfile = "motd.txt";
//$dailycachefile = "dailycache.txt";
//if($test) $dailycachefile = "dailycache_test.txt";
date_default_timezone_set('America/Los_Angeles');
chdir("/home/postersw/public_html");  // move to web root

    //  read dailycache into buffer $dcout
    //$dcout = file_get_contents($dailycachefile);
    //if($dcout==false) exit("$dailycachefile does not exist");
    //if($dcout=="") exit("$dailycachefile is empty");
    //if(substr($dcout, 0, 11) !="DAILYCACHE\n") exit ("first line of $dailycachefile is not DAILYCACHE");

    // find MOTD and delete it from $dcout
    // $i = strpos($dcout, "MOTD\n");
    // if($i > 10) {
    //     $j = strpos($dcout, "\n", $i+6);  // $j = end of motd
    //     $dcout = substr_replace($dcout, "", $i, ($j-$i+1));  // delete the motd;
    //     echo (" Deleted motd from $i to $j\n ");
    // }

// check motd.txt file
$motdout = "";
$motdf = fopen($motdfile, "r"); 
if($motdf==false) exit("No $motdfile");
// check 1st line of motd.txt for <MOTD>
$ln = fgetnc($motdf);
if($ln == "") exit("Empty $motdfile");  // if no motd file, just quite
if($ln != "<MOTD>\n")  exit("$motdfile missing &lt MOTD &gt and is skipped");
// check 2nd line of motd.txt for </MOTD>
$ln = fgetnc($motdf);
if($ln != "</MOTD>\n") {
    $motdout = substr($ln, 0, strlen($ln)-1); // remove trailing \n
    $ln = fgetnc($motdf);  // get next line
}
if($ln != "</MOTD>\n")  exit("$motdfile missing &lt /MOTD &gt");

// check for MOTD date rows:   <DATE mmdd1-mmdd2 [mmdd3-mmdd4] ... >\n msg \n</DATE> ...
while(true) {  // loop through file
    $ln = fgetnc($motdf);
    if(substr($ln, 0, 2) == "//") continue;  // skip comments
    if(substr($ln, 0, 5)== "<DATE") {
        echo "--------------------------------------------------<br>\n";
        $line = substr($ln, 6);
        $line = str_replace(",", " ", $line);  // change comma into space
        $line = str_replace(">", "", $line); // remove any trailing >
        $line = trim(preg_replace("/\s{2,}/", " ", $line));  // collapse duplicate spaces
        $dateranges = explode(" ", $line);  // get the date ranges
        if(count($dateranges) == 0) exit("no data range for $ln");
        $ln = fgetnc($motdf);  // check the next line which is the message
        if($ln == "</DATE>\n") continue;  // if no actual message line, skip it
        $skipped = true;
        // loop through the date ranges mmdd1-mmdd2:hhmm on the <DATE line
        foreach($dateranges as $dl) {
            if($dl=="") continue;
            $tend = "2400";
            $timepos = strpos($dl, ":");
            if($timepos > 2) {  // if a:c or a-b:c, extract the time
                $tend = substr($dl, $timepos+1);  // extract c from a:c or a-b:c
                $dl = substr($dl, 0, $timepos);
            }
            $dates = explode("-", $dl);
            $ds = $dates[0];
            $de = $ds;
            if(count($dates)==2) $de = $dates[1];  // if a-b
            if(!is_numeric($ds)) exit("non-numeric ds: ds=$ds,de=$de,tend=$tend,dl=$dl for $ln ");
            if(!is_numeric($de)) exit("non-numeric de: ds=$ds,de=$de,tend=$tend,dl=$dl for $ln ");
            if(!is_numeric($tend)) exit("non-numeric tend: ds=$ds,de=$de,tend=$tend,dl=$dl for $ln ");
            echo ("$dl: ds=$ds,de=$de,tend=$tend<br>  ");
            if(checkmotddate($ds, $de, $tend)) {  // if date  and time is active
                $motdout .= substr($ln, 0, strlen($ln)-1);  // add line without \n
                echo ("Added ds=$ds-$de:$tend: $ln <br>\n");
                $skipped = false;
                break; // don't bother with any more date ranges
            } 
        }
        if($skipped) echo "Skipped: $ln<br>\n";
        $ln = fgetnc($motdf);  // read line after msg. should be </DATE>
        if($ln != "</DATE>\n") exit("no ending /Date for $ln");
    }
    else break;
}

// check for MOTDLAST row after the date rows
if($ln != "") {
    if($ln != "<MOTDLAST>\n") exit("ill formed MOTDLAST: $ln");
    $ln = fgetnc($motdf); // get motdlast
    if($ln != "</MOTDLAST>\n")  {
        $motdout .= substr($ln, 0, strlen($ln)-1);  // add line without \n
        $ln = fgetnc($motdf);
    }
    if($ln != "</MOTDLAST>" && $ln != "</MOTDLAST>\n" ) exit("no closing /MOTDLAST: $ln");
}

// create motdinclude.txt WITH NO LINE ENDING.   Dailycache.txt must supply the line ending.
fclose($motdf);   // close modfile
file_put_contents("motdinclude.txt", $motdout);

echo ("MOTD:\n$motdout <br/>");  // if there is an motd
    // if($motdout != "") {
    //     $dcout = substr_replace($dcout, "MOTD\n" . $motdout . "\n", 11, 0); // insert motd after DAILYCACHE\n
    // } 
    //exit("stop before writint dailycache");

    //  copy dcout to dailycache
    // $i = file_put_contents($dailycachefile, $dcout);
    // echo ("wrote $i chars to $dailycachefile ");
exit();


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  checkmotddate - returns true if the date is within date1 - date2
//
//  entry   dstart is start date, mmdd
//          dend is end date, mmdd
//  exit    true if current date is with date1-date2, else false
//
function checkmotddate($dstart, $dend, $tend) {
    $d1 = intval($dstart);
    $d2 = intval($dend);

    if($d1 > $d2) exit("end date $dend is < start date $dstart");
    if($d1 < 101 || $d1 > 1231) exit(" invalid start date $dstart");
    if($d2 < 101 || $d2 > 1231) exit(" invalid end date $dend");

    $dnow = intval(date("md"));  // get mmdd
    //echo ("d1=$d1, d2=$d2, dnow=$dnow. ");
    if(($d1<=$dnow) && ($dnow<=$d2)) {
        echo "  DATE TRUE, ";
        if($d2==$dend  && $tend!="2400") {  // check time cutoff on last day of range
            if(intval(date("Hi"))>intval($tend)) { 
                echo " TIME FALSE <br>\n";
                return false;
            } else echo "  TIME TRUE<br>\n";
        }
        return true;
    }
    echo ("    DATE FALSE: $d1 <= $dnow  and $dnow <= $d2 <br>\n");
    return false;    
}

/////////////////////////////////////////////////////////////////////////////////////////////////////
// fgetnc - get string but ignore comments and blank lines
//  entry   $f = file handle
//  exit    returns next line, "" if eof
//
function fgetnc($fh) {
    while($ln = fgets($fh)){
        //echo "$ln<br>\n";
        if(($ln!="\n") && substr($ln, 0,2)!="//") return $ln;
    }
    echo "fgetnc return null";
    return "";
}

?>