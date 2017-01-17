<?php
/////////////////////////////////////////////////////////////
//  getburnban - gets the clean air burnban status from pscleanair
//  web site and writes it to burnban.txt.
//  Also gets the fire safety burnban status www.co.pierce.wa.us and writes it to burnban.txt.
//  this file is picked up by the getalerts.php script and sent to the app.
//  Air Quality format is:   Peninsula ... <input ... value="xxxxxx"
//      Rev 6/4/16.
//  Fire department: looks for string "COUNTY WIDE BURN BAN", and then "Lifted" or "Effective".
//      Rev 9/28/16.
//
    $burnbanlink = "http://wc.pscleanair.org/burnban411/";
    $firebblink = "http://www.co.pierce.wa.us/index.aspx?NID=982";
    $burnbanfile = "burnban.txt";
    chdir("/home/postersw/public_html");  // move to web root
    $str = file_get_contents($burnbanlink);
    if($str == "") file_get_contents($burnbanlink); // 1 retry
    // $j = strpos($str, 'Peninsula\n                            <input name="ctl00$MainContent$Repeater1$ctl05$TextBox" type="text" value="No Ban"');
    $j = stripos($str, 'Peninsula'); // j = position of Peninsula
    if($j==false) Bailout("Peninsula not found");
    //echo " len=" . strlen($str); echo (" j=$j " . substr($str, $j, 10));
    $k = strpos($str, "<input ", $j);  //k=position of <input
    if($k==false) Bailout("<input not found");
    //echo "  k=$k "; echo substr($str, $k+1, 10);
    $v = strpos($str, 'value="', $k);  //v=position of value="xxxx"
    if($v==false) Bailout ("value not found");
    //echo ("  v=$v " . substr($str, $v, 10));
    // extract the value
    $q = strpos($str, '"', $v+7);  // q = position of closing quote
    if($v==false) Bailout("no closing quote");
    //echo ("  q=$q " . substr($str, $q, 10));  echo (" l = " . ($q-$v-7));
    $bb = substr($str, $v+7, $q-$v-7); // bb = burn ban value
    if($bb == "") Bailout("bb=null");
    // make it green if no burnban; else red with the text value
    if($bb == "No Ban") $bb = '<span style="color:green">No Ban</span>';
    else $bb = '<span style="color:red;font-weight:bold">' . $bb . '</span>';

    // write it
    $airqual = "Peninsula air quality: " . $bb;

    // fire department. read pierce county page and find COUNTY-WIDE BURN BAN.
    //      then find Lifted or Effective.  Otherwise issue an error.

    $fire = file_get_contents($firebblink); //'<a href="http://www.co.pierce.wa.us/index.aspx?NID=982" style="color:red;font-weight:bold">County-wide Outdoor Burn Ban</a>'; // rfb 8/19
    if($fire == "") $fire = file_get_contents($firebblink); //1 retry
    //echo("length of fire=" . strlen($fire) . "<br/>"); DEBUG
    //$fire = strip_tags($firew);  // remove the tags
    //echo $fire; echo "<br/>";
    $cwbb = "COUNTY-WIDE BURN BAN";
    $i = stripos($fire, $cwbb);
    if($i == 0) Bailout("could not find $cwbb");
    $lifted = stripos($fire, "Lifted", $i);
    $effective = stripos($fire, "Effective", $i);
    if($lifted > 0) $firebb = '<a href="http://www.co.pierce.wa.us/index.aspx?NID=982" style="color:green;">No Outdoor Burn Ban</a>';
    elseif($effective> 0) $firebb =  '<a href="http://www.co.pierce.wa.us/index.aspx?NID=982"style="color:red;font-weight:bold">County-wide Outdoor Burn Ban</a>';
    else Bailout("County Burn Ban not = Lifted or Effective. Revise the script.");

    // write to file
    $msg = $airqual . "<br/>Fire Safety: " . $firebb;
    $old = file_get_contents($burnbanfile);
    if($msg == $old) return 0;
    echo $msg;
    file_put_contents($burnbanfile, $msg);
    return 0;



    ////////////////////////////////////////////////////////////
    // Bailout - send error message and delete file and exit
    function Bailout($s) {
        echo "Error: " . $s;
        unlink($burnbanfile);
        exit;
    }
?>