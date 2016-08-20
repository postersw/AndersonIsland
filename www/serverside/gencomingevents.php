<?php
//////////////////////////////////////////////////////////////////////////////////
//  gencomingevents.php Generate Coming Events using Recurring Events
//  8/1/2016.   rfb.
//
//  comingevents.txt + recurring.txt -> gencomingevents.php -> newcomingevents.txt.
//  EVENTS are not touched (but are copied over into newcomingevents.txt).
//  ACTIVITIES are copied over and ADDED to using recurring.txt.
//      NO activities are deleted.   NO activities are duplicated.
//      New activities are added.
//  Run this script once/month. Change $monthstart and $monthend.
//
//  recurring.txt: recurring events.  This is the driving file.
// week (0=every week, 1-4 = week of month),dayofweek(0-6),starthhmm,endhhmm,type,event,where,sponsor
//  e.g.: 0;1;0900;1000;A;Yoga;MSR-Riviera;Riviera

// globals
echo("started gencomingevents<br/>");
$frecur = "recurring.txt";  // recurring events
$fcein = "comingevents.txt"; // coming events
$fceout = "newcomingevents.txt"; // new coming events
// events
$cemmdd = array(0);
$ceshhmm = array(0);
$ceehhmm = array(0);
$cetype = array("");
$cetherest = array("");
$nce = 0;
// activities
$actmmdd = array(0);
$actshhmm = array(0);
$actehhmm = array(0);
$acttype = array("");
$acttherest = array("");
$nact = 0;
// recurring
$recweek = array(0);
$recdow = array(0);
$recshhmm =array(0);
$recehhmm = array(0);
$rectype = array("");
$rectherest = array("");
$nrec = 0;
// settings
$month = 8; // month to use
$monthstart = 8;
$monthend = 9;
$year = 2016;

// doit
readfrecur($frecur); // read recurring
$filefceout = fopen($fceout, "w") or die("cant open $fceout for write");
readfcein($fcein, $filefceout); // read it in
if($nrec == 0) exit("no recurring events");
dorecur();
writefceout($fceout, $filefceout );
exit("FINAL EXIT");

//////////////////////////////////////////////////////
// readfcein - read the coming events file
//  $fcein = name of input coming events file
//  $filefceout = handle for coming events output file
function readfcein($fcein, $filefceout){
    global $actmmdd,$actshhmm,$actehhmm,$acttype,$acttherest,$nact;
    //  read it in
    $activities = false;
    $file = fopen($fcein, "r") or die("cant open $fcein<br/>");
    while(!feof($file)) {
        $s = fgets($file);
        if(substr($s, 0, 1) == "/") {
            fputs($filefceout, $s);
            continue; // skip comment
        }

        // copy EVENTS to fceout
        if(!$activities) {
            if($s != "ACTIVITIES\n") {
                fputs($filefceout, $s);
                continue; // skip comment
            }
            $activities = true;
            fputs($filefceout, $s);  // write ACTIVITIES
            continue;
        }

        // load activities into arrays
        $a = explode(";", $s, 5);  // split the string and save in an  array
        $actmmdd[$nact] = intval($a[0]);
        $actshhmm[$nact] = $a[1];
        $actehhmm[$nact] = $a[2];
        $acttype[$nact] = $a[3];
        $acttherest[$nact] = $a[4];
        $nact = $nact + 1;
    }
    fclose($file);
    echo("read $nact from $fcein <br/>");

}

//////////////////////////////////////////////////////
// readfrecur - read the recuring events file
//  entry   $frecur = name of recurring events file
//
function readfrecur($frecur){
    global $recweek,$recdow,$recshhmm,$recehhmm,$rectype,$rectherest,$nrec;
    //  read it in
    $file = fopen($frecur, "r") or die("cant open $frecur<br/>");
    while(!feof($file)) {
        $s = fgets($file);
        if(strlen($s)<10) continue; // skip blanks
        if(substr($s, 0, 1) == "/") continue; // skip comment
        $a = explode(";", $s, 6);  // split the string and save in an  array
        $recweek[$nrec] = intval($a[0]);
        $recdow[$nrec] = intval($a[1]);
        $recshhmm[$nrec] = $a[2];
        $recehhmm[$nrec] = $a[3];
        $rectype[$nrec] = $a[4];
        $rectherest[$nrec] = $a[5];
        if(substr($rectherest[$nrec], -1) != "\n") $rectherest[$nrec]=$rectherest[$nrec] . "\n"; // force eol
        $nrec = $nrec + 1;
    }
    fclose($file);
    echo("read $nrec from $frecur <br/>");
}

//////////////////////////////////////////////////////////////
//  dorecur process the recurring event
//  entry: recmmdd = 0 for every week, else n for week
//      recdow = day of week (0 - 6)
function dorecur() {
    global $recweek,$recdow,$recshhmm,$recehhmm,$rectype,$rectherest,$nrec;
    global $monthstart, $monthend;
    // loop through months
    for($month=$monthstart;$month<=$monthend;$month++) {
        // loop through all records
        for($i = 0; $i<$nrec; $i++) {
            echo("$i:");
            $recw = $recweek[$i];
            if($recw > 0) {
                // a specific week
                $mmdd = gendate($month, $recw, $recdow[$i]); // generate the date
                insertintoce($mmdd, $recshhmm[$i], $recehhmm[$i], $rectype[$i], $rectherest[$i] );
            } else {
                // every week
                $mmdd = gendate($month, 1, $recdow[$i]); // generate the date
                insertintoce($mmdd, $recshhmm[$i], $recehhmm[$i], $rectype[$i], $rectherest[$i] );
                $mmdd = gendate($month, 2, $recdow[$i]); // generate the date
                insertintoce($mmdd, $recshhmm[$i], $recehhmm[$i], $rectype[$i], $rectherest[$i] );
                $mmdd = gendate($month, 3, $recdow[$i]); // generate the date
                insertintoce($mmdd, $recshhmm[$i], $recehhmm[$i], $rectype[$i], $rectherest[$i] );
                $mmdd = gendate($month, 4, $recdow[$i]); // generate the date
                if($mmdd > 0) insertintoce($mmdd, $recshhmm[$i], $recehhmm[$i], $rectype[$i], $rectherest[$i] );
                $mmdd = gendate($month, 5, $recdow[$i]); // generate the date
                if($mmdd > 0) insertintoce($mmdd, $recshhmm[$i], $recehhmm[$i], $rectype[$i], $rectherest[$i] );
            }
        }
    }
}

/////////////////////////////////////////////////////////////////////////////
//  insertintoce inserts into the actxxxx arrays in the proper date&time sequence
//  entry   mmdd, start hhmm, endhhmm, type, the rest of the string
//  exit    item added to the actxxxx arrays, $nact bumped
function insertintoce($mmdd, $shhmm, $ehhmm, $t, $therest) {
    global $actmmdd,$actshhmm,$actehhmm,$acttype,$acttherest,$nact;
    for($i=0; $i<$nact; $i++) {
        //echo("insert for loop: $mmdd, i=$i, actmmdd=" . $actmmdd[$i] . ",nact=$nact<br/>");
        if($mmdd > $actmmdd[$i]) continue;
        //echo("    after the continue");
        if($mmdd == $actmmdd[$i] && $shhmm > $actshhmm[$i]) continue;
        if($mmdd == $actmmdd[$i] && $shhmm == $actshhmm[$i] && $ehhmm<$actehhmm[$i]) continue;
        if($mmdd == $actmmdd[$i] && $shhmm == $actshhmm[$i] && $ehhmm==$actehhmm[$i] &&
            $t==$acttype[$i] && $therest==$acttherest[$i]) return;  // if a duplicate, skip it
        //echo("$mmdd/$actmmdd[$i];$shhmm/$actshhmm[$i];$ehhmm/$actehhmm[$i];$t/$acttype[$i];$therest/$acttherest[$i]<br/>");
        // move the rest up. At this point, the $i value is pointing to the 1st entry to move up.
        for($j = $nact; $j>=$i; $j--) {
            $actmmdd[$j+1] = $actmmdd[$j];
            $actshhmm[$j+1] = $actshhmm[$j];
            $actehhmm[$j+1] = $actehhmm[$j];
            $acttype[$j+1] = $acttype[$j];
            $acttherest[$j+1] = $acttherest[$j];
        }
        break;
    }
    // insert it at the i value
    //if($i==$nact && $i!= 0) $i--;  // back up one if at the end
    $actmmdd[$i] = $mmdd;
    $actshhmm[$i] = $shhmm;
    $actehhmm[$i] = $ehhmm;
    $acttype[$i] = $t;
    $acttherest[$i] = $therest;
    $nact = $nact + 1;
    echo("Inserted at $i (nact=$nact): $mmdd;$shhmm;$ehhmm;$t;$therest<br/>");

}

/////////////////////////////////////////////////////////////////////////
//  writefceout write out the file
//  writes all entries in the actxxxx arrays to fceout
//  entry   $fceout = name of output coming events
//          $filefceout = handle of output coming events.  Events have already been copied to it.
function writefceout($fceout, $filefceout){
    global $actmmdd,$actshhmm,$actehhmm,$acttype,$acttherest,$nact;
    for($i=0; $i<$nact; $i++) {
        $s = $actmmdd[$i] . ";" . $actshhmm[$i] .";" .  $actehhmm[$i] .";" .  $acttype[$i] .";" .  $acttherest[$i];
        if($actmmdd[$i]<1000) $s = "0" . $s; // leading 0 if needed
        fputs($filefceout, $s);
    }
    fclose($filefceout);
    echo("wrote $nact lines to $fceout<br/>");
}

////////////////////////////////////////////////////////////////////////
 //gendate - generate the date gendate(month, week (1-5), dow (day of week))
 // entry: month = 1 - 12
 //     week of the month = 1 - 5
 //     dow = day of week, as 0 (sun) - 6
 // exit: date as mmdd, 0 if invalid
function gendate($month, $week, $dow) {
    global $year;
    $dom=array(31,28,31,30,31,30,31,31,30,31,30,31); // days in month, indexed by month

    $jd = cal_to_jd(CAL_GREGORIAN,$month,1,$year); // julian day of year
    $jdow = jddayofweek($jd,0); // day of week
    $mmdd =  1 + ($week-1)*7; // if month begins on sunday
    if($dow >= $jdow) $mmdd = $mmdd + ($dow - $jdow);
    else  $mmdd = $mmdd + $dow + (7-$jdow);
    if($mmdd > $dom[$month-1]) return 0;  // too many days
    //echo("Gendate m=$month,w=$week,dow=$dow returns jd=$jd,jdow=$jdow,mmdd=$mmdd <br/>");
    return $month * 100 + $mmdd;
}

?>