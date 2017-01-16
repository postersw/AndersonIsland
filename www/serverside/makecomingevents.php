<?php
/////////////////////////////////////////////////////////////////////////////////////
//  makecomingevents.php -only copy 2 months of coming events from comingeventsmaster.txt to comingevents.txt.
//  this allows comingeventsmaster to be much larger than the comingevents that is sent to the app
//  run every night at 11:50pm
//  rfb. 1/15/17
//  comingeventsmaster.txt MUST have a year line: 0101;0000;0000;E;20nn in both the comingevents section and the activities section

chdir("/home/postersw/public_html");  // move to web root
$y = date("Y"); // year, e.g. 2017
$m = date ("m"); // month with leading zero
$d = date("d"); // day with leading zero
$mds = $y . $m . $d; // mmdd
$me = $m + 2;
if($me > 12) $me = $me - 12;
$mde = $y. sprintf("%02d", $me) . $d;
$s = "";

$fm = fopen("comingeventsmaster.txt", "r") or die("Unable to open comingeventsmaster.txt!");
$fce = fopen("comingevents.txt", "w") or die("Unable to open comingevents.txt!");
copycefile(); // copy coming events for current 2 months
// skip to activities
while(!feof($fm) && (substr($s, 0, 10) != "ACTIVITIES")) $s = fgets($fm); // skip to activities
fwrite($fce, $s);  // write activities
copycefile();  // copy activities for current 2 months
fclose($fm);
fclose($fce);

return;


//////////////////////////////////////////////////////////////////////////////
// copycefile - copy coming events file.  smart copy only copies 2 months.
// requires a line for each new year that is 0101;0000;0000;E;yyyy4
// copies $fm to $fce
// stops on eof, ACTIVITIES, or 2 months after current month/day
// exit $s = last string read
//
function copycefile() {
    global $y,$m, $d, $mds, $mde;
    global $fce, $fm;
    global $s;
    $i = 0;

    // loop between start and end dates (including the year)
    while(!feof($fm)) {
        $s = fgets($fm);
        if(substr($s, 0, 14)=="0101;0000;0000") {  // if a year, change the year
            $y = substr($s, 17, 4);
            fwrite($fce, $s); // always write year to output
        }
        if(strlen($s) < 5) continue; // skip blanks
        if(substr($s, 0, 10) == "ACTIVITIES") break;
        if( ($y . substr($s, 0, 4)) < $mds) continue;  // if not there yet, skip
        if( ($y . substr($s, 0, 4)) > $mde) break;  // if past end date, quit
        fwrite($fce, $s); // write to output
        $i++;
    }
    echo "Copied $i records to comingevents.txt for $mds to $mde.  Stopped at: $s<br/>";
}

?>