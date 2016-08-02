<?php
//////////////////////////////////////////////////////////////////////////////////
//  gencomingevents.php Generate Coming Events using Recurring Events
//  8/1/2016.   rfb.
//

// globals
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
$recmdd = array(0);
$recshhmm =array(0);
$recehhmm = array(0);
$rectype = array("");
$rectherest = array("");
$nrecur = 0;
// settings
$month = 8; // month to use
$monthstart = 8;
$monthend = 9;

// doit
readfcein(); // read it in
readfrecur(); // read recurring
dorecur();
writefecout();
exit(0);

//////////////////////////////////////////////////////
// readfcein - read the coming events file
function readfcein(){
    //  read it in
    $activities = false;
    $file = fopen($fcein, "r") or die("cant open $fcein");
    while(!feof($file)) {
        $s = fgets($file);
        if(substr($s, 0, 1) == "/") continue; // skip comment
        if(!$activities) {
            if($s != "ACTIVITIES") continue;
            $activities = true;
            continue;
        }
        $a = explode(";", $s, 5);  // split the string and save in an  array
        $actmmdd[$nact] = $a[0];
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
function readfrecur(){
    //  read it in
    $file = fopen($frecur, "r") or die("cant open $fcein");
    while(!feof($file)) {
        $s = fgets($file);
        if(substr($s, 0, 1) == "/") continue; // skip comment
        $a = explode(";", $s, 6);  // split the string and save in an  array
        $recweek[$nrec] = $a[0];
        $recdow[$nrec] = $a[1];
        $recshhmm[$nrec] = $a[2];
        $recehhmm[$nrec] = $a[3];
        $rectype[$nrec] = $a[4];
        $rectherest[$nrec] = $a[5];
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
    for($i = 0; $i<$nrec; $i++) {
        if($recweek($i) > 0) {
            // a specific week
            $mmdd = gendate($month, $recweek($i), $recdow[$i]); // generate the date
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
            insertintoce($mmdd, $recshhmm[$i], $recehhmm[$i], $rectype[$i], $rectherest[$i] );
            $mmdd = gendate($month, 5, $recdow[$i]); // generate the date
            if($mmdd > 0) insertintoce($mmdd, $recshhmm[$i], $recehhmm[$i], $rectype[$i], $rectherest[$i] );
        }
    }
}

/////////////////////////////////////////////////////////////////////////////
//  insertintoce inserts into the ce array
//  entry   mmdd, start hhmm, endhhmm, type, the rest of the string
function insertintoce($mmdd, $shhmm, $ehhmm, $t, $therest) {
    for($i=0; $i<$nce; $i++) {
        if($mmdd < $actmmdd[$i]) continue;
        if($mmdd == $actmmdd[$i] && $shhmm < $actshhmm[$i] && $ehhmm<$actehhmm) continue;
        if($mmdd == $actmmdd[$i] && $shhmm == $actshhmm[$i] && $ehhmm<$actehhmm) continue;
        if($mmdd == $actmmdd[$i] && $shhmm == $actshhmm[$i] && $ehhmm==$actehhmm &&
            $type==$actype[$i] && $therest==$acttherest[$i]) return;  // if a duplicate, skip it
        // move the rest up
        for($j = $nact; $j<$i; $j--) {
            $actmmdd[$j+1] = $actmmdd[$j];
            $actshhmm[$j+1] = $actshhmm[$j];
            $actehhmm[$j+1] = $actehhmm[$j];
            $acttype[$j+1] = $acttype[$j];
            $acttherest[$j+1] = $acttherest[$j];
        }
        // insert it
        $nact = $nact + 1;
        $actmmdd[$i] = $mmdd;
        $actshhmm[$i] = $shhmm;
        $actehhmm[$i] = $ehhmm;
        $acttype[$i] = $t;
        $acttherest[$i] = $therest;
        echo("Inserted at $i : $mmdd;$shhmm;$ehhmm;$t;$therest<br/>");
    }
}
/////////////////////////////////////////////////////////////////////////
//  writefceout write out the file
//  writes all entries in the actxxxx arrays
function fwritefceout(){
    $file = fopen($fceout, "w") or die("cant open $fceout for write");
    for($i=0; $i<$actn; $i++) {
        $s = $actmmdd[$i] . $actshhmm[$i] . $actehhmm[$i] . $acttype[$i] . $acttherest[$i] . "\n";
        fputs($file, $s);
    }
    fclose($file);
    echo("wrote $actn lines to $fceout<br/>");
}
//////////////////////////////////////////////////////////////////////////
// gendate - generate the date gendate(month, week (1-5), dow (day of week))
// month = 1 - 12
//  week in month = 1 - 5
//  dow = day of week, as 0 (sun) - 6
//  exit: date as mmdd, 0 if invalid
function gendate($month, $week, $dow) {
    $dom={31,28,31,30,31,30,31,31,30,31,30,31};
    // first brute force
    $d = 1;
    $jd = cal_to_jd(CAL_GREGORIAN,$month,1,$year); // julian day of year
    $jdow = jddayofweek($jd,1); // day of week
    $mmdd =  1 + ($week-1)*7); // if month begins on sunday
    if($dow >= $jdow) $mmdd = $mmdd + ($dow - $jdow);
    else  $mmdd = $mmdd + $dow + (7-$jdow);
    if($mmdd > $dom[$month-1]) return 0;  // too many days
    return $month * 100 + $mmdd;
}

?>