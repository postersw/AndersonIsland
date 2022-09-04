<?php
//////////////////////////////////////////////////////////////////////////////
// getmotd.php writes the motd.txt file commands to motdinclude.txt file every night.
//  run by cron at 12:02am nightly.
//
// input file: motd.txt
//  <MOTD>
//  Optional motd messages to always include. only 1 line up to the \n,  \n is stripped.
//  </MOTD>
//  <DATE mmddstart-mmddend [mmddstart-mmddend] ... >
//   optional motd message to include, starting mmddstart, and ending mmddend.  As many time ranges may be added as needed.  only 1 line up to the \n. \n is stripped.
//  </DATE>
//  .... repeated as necessary
//  <MOTDLAST>
//   optional messages to include after the scheduled messages. Only 1 line up to the \n
//  </MOTDLAST>
//
//  exit: writes all relevant motd lines to the 'motdinclude.txt' file as one long line ending with \n.  All messages must end with <br/> to provide formatting.
//        the 'motdinclude.txt' file is included into the dailycache.txt file using a '<include motdinclude.txt>' line in dailycache.txt.
//
//  10/3/21. RFB. Initial version.
//  7/21/22  RFB. Accept multiple date ranges for the same message.
//  8/30/22. RFB. Create an motdinclude.txt file.  Do not change dailycache.txt anymore.
//  9/4/22   RFB. Include "lowtidewarning.txt file.

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


// check for MOTD date rows:   <DATE mmdd1-mmdd2 [mmdd3-mmdd4] ... >\n msg \n</DATE> ...
while(true) {
    $ln = fgets($motdf);
    if(substr($ln, 0, 5)== "<DATE") {
        $dateranges = explode(" ", substr($ln, 6));  // get the date ranges
        if(count($dateranges) == 0) exit("no data range for $ln");
        $ln = fgets($motdf);  // check the next line
        if($ln == "</DATE>\n") continue;  // if no actual <DATE line, skip it
        // loop through the date ranges mmdd1-mmdd2
        foreach($dateranges as $dl) {
            if($dl=="") continue;
            if($dl==">") break;
            $dates = explode("-", $dl);  // split mmdd1-mmdd2 on the dash
            $ds = preg_replace('~\D~', '', $dates[0]);  // strip all non digits
            if($ds=="") continue;
            if(count($dates)==1) $de = $ds;  // if just mmdd1
            else $de = preg_replace('~\D~', '', $dates[1]); // else use mmdd2
            echo ("$dl: ds=$ds, de=$de  \n");
            if(checkmotddate($ds, $de)) {  // if date is active
                $motdout .= substr($ln, 0, strlen($ln)-1);  // add line without \n
                echo (" Added ds=$ds-$de: $ln ");
            } //else echo (" Skipped $ln ");
        }
        $ln = fgets($motdf);  // read line after msg. should be </DATE>
        if($ln != "</DATE>\n") exit("no ending /Date for $ln");
    }
    else break;
}

// include lowtidewarning.txt; note it must not have a \n
$ltw = file_get_contents("lowtidewarning.txt");
if($ltw <> "") $motdout .= str_replace("\n", "", $ltw);

// check for MOTDLAST row after the date rows
if($ln != "") {
    if($ln != "<MOTDLAST>\n") exit("ill formed MOTDLAST: $ln");
    $ln = fgets($motdf); // get motdlast
    if($ln != "</MOTDLAST>\n")  {
        $motdout .= substr($ln, 0, strlen($ln)-1);  // add line without \n
        $ln = fgets($motdf);
    }
    if($ln != "</MOTDLAST>" && $ln != "</MOTDLAST>\n" ) exit("no closing /MOTDLAST: $ln");
}

// insert motd into $dcout
fclose($motdf);   // close modfile
file_put_contents("motdinclude.txt", $motdout . "\n");

echo ("MOTD:\n$motdout <br/>\n");  // if there is an motd
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
function checkmotddate($dstart, $dend) {
    $d1 = intval($dstart);
    $d2 = intval($dend);

    if($d1 > $d2) exit("end date $dend is < start date $dstart");
    if($d1 < 101 || $d1 > 1231) exit(" invalid start date $dstart");
    if($d2 < 101 || $d2 > 1231) exit(" invalid end date $dend");

    $dnow = intval(date("md"));  // get mmdd
    //echo ("d1=$d1, d2=$d2, dnow=$dnow. ");
    if(($d1<=$dnow) && ($dnow<=$d2)) {return true;}
    return false;    
}

?>