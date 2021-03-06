<?php
/////////////////////////////////////////////////////////////
//  getburnban - gets the clean air burnban status from pscleanair
//  web site and writes it to burnban.txt.
//  Also gets the fire safety burnban status www.co.pierce.wa.us and writes it to burnban.txt.
//  this file is picked up by the getalerts.php script and sent to the app.
//
//  Air Quality format is:   Peninsula ... <input ... value="xxxxxx"
//      Rev 6/4/16.
//  Fire department: looks for string "COUNTY WIDE BURN BAN", and then "Lifted" or "Effective".
//      Rev 9/28/16.
//  Fire department: look for "Current Fire Safety Burn Ban Status". Then for "NO BURN BAN".
//      Rev 2/16/18.
//      Rev 2/5/18. Look for NO FIRE SAFETY BURN BAN
//      Rev 3/26/19: Look for: Current Fire Safety Burn Ban Status:</p><p>No Burn Ban
//      Rev 4/4/19: Look for alt="FS No Burn Ban"
//      Rev 11/25/19: Air Quality alert address change.
//      Rev 01/17/20: Burn Ban address change
//      Rev 7/29/20: Look for Burn Ban Announcement.
        //<div class="area-row">        //    <div class="area-name sub-area">Peninsula</div>
        //        <div class="status-text no-ban">No Ban</div>
        //</div>                                 12345678
//
    $burnbanlink = "https://secure.pscleanair.org/AirQuality/BurnBan";
    $firebblink = "http://www.co.pierce.wa.us/982/Outdoor-Burning";//  1/17/20
    $burnbanfile = "burnban.txt";
    chdir("/home/postersw/public_html");  // move to web root

// AIR QUALITY: read the Air Quality page and extract the data for peninsula

    $str = file_get_contents($burnbanlink);
    if($str == "") file_get_contents($burnbanlink); // 1 retry
       //    <div class="area-name sub-area">Peninsula</div>
   //        <div class="status-text no-ban">No Ban</div>

    $j = stripos($str, 'Peninsula'); // j = position of Peninsula
    if($j==false) Bailout("Peninsula not found");
    // now find <div.
    $k = strpos($str, "<div", $j); // position of <div
    if($k==false) Bailout("<div not found");
    // now find closing >
    $v = strpos($str, ">", $k);// closing >
    if($k==false) Bailout("> not found");
    // now find </div
    $q = strpos($str, "</div", $v);   // position of <div
    if($k==false) Bailout("</div not found");
    $bb = substr($str, $v+1, $q-$v-1); // bb = burn ban value
    if($bb == "") Bailout("bb=null");
    // make it green if no burnban; else red with the text value
    if($bb == "No Ban") $bb = '<span style="color:green">No Ban</span>';
    elseif($bb == "Stage 1") $bb = '<span style="color:darkorange">Stage 1 Burn Ban</span>';
    else $bb = '<span style="color:red;font-weight:bold">' . $bb . ' Burn Ban</span>';

    // write it
    $airqual = "Air quality: " . $bb;
    //echo $airqual;   debug

// FIRE SAFETY. read pierce county page and find "Fire Safety Burn Ban Status" or Burn Ban Status.
// then find the alt image tages: FIRE SAFETY - NO BURN BAN or FIRE SAFETY - BURN BAN. Not the best solution. 6/6/18.

    $fire = file_get_contents($firebblink); //'<a href="http://www.co.pierce.wa.us/index.aspx?NID=982" style="color:red;font-weight:bold">County-wide Outdoor Burn Ban</a>'; // rfb 8/19
    if($fire == "") $fire = file_get_contents($firebblink); //1 retry
    //echo("length of fire=" . strlen($fire) . "<br/>"); DEBUG
    //$fire = strip_tags($firew);  // remove the tags

    // find starting point for burn ban
    $cwbb = "Fire Safety Burn Ban Status";
    $i = stripos($fire, $cwbb);
    if($i == 0) {
        $cwbb = "Burn Ban Status";
        $i = stripos($fire, $cwbb);
    }
    if($i == 0) Bailout("Could not find \"$cwbb\"");

    $lifted = 0; // >0 if no burn ban
    $effective = 0; // >0 if there is a burn ban

    // 3/26/19: Find the line after Current Fire Safety Burn Ban:
    $lifted = stripos($fire, "Current Fire Safety Burn Ban: NO BURN BAN", $i);

    // now find alt image tags (bad solution) to get the actual status
    if($lifted===false) $lifted = stripos($fire,'alt="FS No Burn Ban"', $i);
    if($lifted===false) $lifted = stripos($fire, "FIRE SAFETY - NO BURN BAN", $i); // these are alt image tags, which will change.
    if($lifted===false) $lifted = stripos($fire, "NO FIRE SAFETY BURN BAN", $i);
    $effective = stripos($fire, '"FIRE SAFETY - BURN BAN"', $i);
    if($effective===false) $effective = stripos($fire, '"BURN BAN IN EFFECT"', $i);
    if($effective===false) $effective = stripos($fire, '"FIRE SAFETY BURN BAN"', $i);
    if($effective===false) $effective = stripos($fire, '"Burn Ban Announcement"', $i);  // added 7/29/2020
    if($lifted > 0) $firebb = "<a href=\"$firebblink\" style=\"color:green;\">No Outdoor Burn Ban</a>";
    elseif($effective> 0) $firebb = "<a href=\"$firebblink\" style=\"color:red;font-weight:bold\">County-wide Outdoor Burn Ban</a>";
    else {
        $firebb = "<a href=\"$firebblink\" >Unknown</a>";
        echo "Could not find burn ban status on $firebblink. Revise getburnbanalerts.php.";
    }

    // write to file if it changed, and issue to email
    $msg = $airqual . "<br/>Fire Safety: " . $firebb;
    $old = file_get_contents($burnbanfile);
    if($msg == $old) return 0;  // if no change
    echo $msg;
    file_put_contents($burnbanfile, $msg);
    return 0;



    ////////////////////////////////////////////////////////////
    // Bailout - send error message and delete file and exit
    function Bailout($s) {
        echo "Error: " . $s;
        //unlink($burnbanfile);
        exit;
    }

    ///old extract
        ////echo " len=" . strlen($str); echo (" j=$j " . substr($str, $j, 10));
    //$k = strpos($str, "<input ", $j);  //k=position of <input
    //if($k==false) Bailout("<input not found");
    ////echo "  k=$k "; echo substr($str, $k+1, 10);
    //$v = strpos($str, 'value="', $k);  //v=position of value="xxxx"
    //if($v==false) Bailout ("value not found");
    ////echo ("  v=$v " . substr($str, $v, 10));
    //// extract the value
    //$q = strpos($str, '"', $v+7);  // q = position of closing quote
    //if($v==false) Bailout("no closing quote");
    ////echo ("  q=$q " . substr($str, $q, 10));  echo (" l = " . ($q-$v-7));
    //$bb = substr($str, $v+7, $q-$v-7); // bb = burn ban value
?>