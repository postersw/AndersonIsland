<?php
/////////////////////////////////////////////////////////////
//  getburnban - gets the burnban status from pscleanair
//  web site and writes it to burnban.txt.
//  Also gets the burnban status from PFFD27 (AI Fire department) and writes it to burnban.txt.
//  this file is picked up by the getalerts.php script and sent to the app.
//  Air Quality format is:   Peninsula ... <input ... value="xxxxxx"
//  Fire department: looks for string "NO BURN BAN".
//              6/4/16.   
//  Fire department added 6/12/16.   rfb.
//
    $burnbanlink = "http://wc.pscleanair.org/burnban411/";
    //$pcfdlink = "http://www.pcfd27.com/";
    $pcfdlink = "http://www.co.pierce.wa.us/index.aspx?NID=982";  // moved to pierce county 8/17/16
    $burnbanfile = "burnban.txt";
    chdir("/home/postersw/public_html");  // move to web root
    $str = file_get_contents($burnbanlink);
    if($str == "") file_get_contents($burnbanlink); // 1 retry
    // $j = strpos($str, 'Peninsula\n                            <input name="ctl00$MainContent$Repeater1$ctl05$TextBox" type="text" value="No Ban"');
    $j = strpos($str, 'Peninsula'); // j = position of Peninsula
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

    // write it
    $airqual = "Peninsula air quality: " . $bb;

    // fire department. removed 8/19/16. 
    //$fire = "?";
    //$str = file_get_contents($pcfdlink);
    //if($str == "") file_get_contents($pcfdlink); // 1 retry
    //if($str != "") {
    //    $j = strpos($str, 'NO BURN BAN'); // j = position of 'NO BURN BAN'
    //    if($j == false) $fire == "<strong>Burn Ban</strong>";
    //    else $fire = "No Ban";
    //}
    // County burn bans 8/19/16.
    // Extract the BURN BAN status after 'Current fire safety burn ban status
    $fire = "?";
    $lookfor = "Current fire safety burn ban status in Pierce County";
    $str = file_get_contents($pcfdlink);
    if($str == "") file_get_contents($pcfdlink); // 1 retry
    if($str != "") {
        $j = stripos($str, $lookfor); // j = start of burn ban status
        if($j == false) break; // we couldn't find it
        $j = $j + strlen($lookfor);
        $bb = stripos($str, "BURN BAN", $j);  // find BURN BAN
        $bbstart = strrpos($str, ">", $bb); // find > before BURN BAN
        $bbend = strpos($str, "<", $bb); // find > after BURN BAN
        $fire = substr($str, $bbstart+1, $bbend-1); // entire phrase
        if(stripos($fire, "NO ") == false) $fire = '<a href="http://www.co.pierce.wa.us/index.aspx?NID=982" style="color:red;font-weight:bold">' . $fire . "</a>";
        else $fire = '<a href="http://www.co.pierce.wa.us/index.aspx?NID=982" style="color:green;">' . $fire . "</a>";
    }

    // write it
    $msg = $airqual . "<br/>Fire Safety: " . $fire;
    $old = file_get_contents($burnbanfile);
    if($msg == $old) return 0;
    echo $msg;
    file_put_contents($burnbanfile, $msg);
    return 0;

    // $j = preg_match('/Peninsula[^<]*<input [^<]* value="No Ban"/', $str); // look for this string
    // //echo("preg_match j=$j ");

    //if($j === false) {  // if string not found
    //    $old = file_get_contents($burnbanfile, $r);
    //    if($old == "") return 0;
    //    echo("Peninsula 'No Ban' not found");
    //    unlink($burnbanfile);
    //    return 0;
    //}

    //// write it
    //$r = "Peninsula: NO Burn Ban";
    //$old = file_get_contents($burnbanfile, $r);
    //if($r == $old) return 0;
    //echo $r;
    //file_put_contents($burnbanfile, $r);
    //return 0;

    ////////////////////////////////////////////////////////////
    // Bailout - send error message and delete file and exit
    function Bailout($s) {
        echo "Error: " . $s;
        unlink($burnbanfile);
        exit;
    }
?>