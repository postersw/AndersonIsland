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
//      Rev 10/17/21. look for RSS feed for bun ban.  This fails as of 10/15/21, locked out by cloudflair security of <piercecountywa class="gov">
//                   Currently the only way to set the fire safety burn ban is to set it in file fireburnbanstatus.txt.
//      12/18/21:  Messages improved.  No data from Outdoor bun ban will not about the script. No data from Air Quailty WILL abort the script.
//      6/19/22:  Removed fire burn ban automated check.
//      6/6/23:  Turn on burn ban.l

    $burnbanlink = "https://secure.pscleanair.org/AirQuality/BurnBan";
    $firebblink = "https://www.piercecountywa.gov/982/Outdoor-Burning?PREVIEW=YES";//  
    $fireburnbanRSS = "https://www.piercecountywa.gov/RSSFeed.aspx?ModID=1&CID=All-newsflash.xml";  //rss feed
    $burnbanfile = "burnban.txt";
    //$firebb = "<a href=\"$firebblink\" style=\"color:green;\">No Outdoor Burn Ban (lifted 10/24/22)</a>";
    $firebb = "<a href=\"$firebblink\" style=\"color:red;font-weight:bold\">County-wide Outdoor Burn Ban (6/7/23)</a>"; // 8/4/22

    chdir("/home/postersw/public_html");  // move to web root

    $bb = getAirQuality($burnbanlink);  // air quallity

    //$firebb = getFireSafetyRSS($fireburnbanRSS);  // fire safety  DOESNT WORK. removed 6/18/22.


    // write to file if it changed, and issue to email
    $airqual = "Air quality: " . $bb;
    $msg = $airqual . "<br/>Fire Safety: " . $firebb;
    $old = file_get_contents($burnbanfile);
    if($msg == $old) return 0;  // if no change
    echo $msg;
    file_put_contents($burnbanfile, $msg);
    return 0;


    /////////////////////////////////////////////////////////////////////////////////////////////////////
    //  getAirQuality - get the air quality and return it for app
    //        AIR QUALITY: read the Air Quality page and extract the data for peninsula
    //  Entry   $burnbanlink = address of air quality web page.
    //  Exit    returns text for web page or app.
    //
    function getAirQuality($burnbanlink) {
        $str = file_get_contents($burnbanlink);
        if($str == "") file_get_contents($burnbanlink); // 1 retry
        if($str===false) Bailout("No air quality data read from $burnbanlink");
        //    <div class="area-name sub-area">Peninsula</div>
        //    <div class="status-text no-ban">No Ban</div>
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
        return $bb;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  getFireSafetyRSS FIRE SAFETY BURN BAN USING RSS FEED;   Locked out by Cloudflare as of 10/14/21.
    //  Entry   $fireburnbanRSS = link for RSS feed with may contain a burn ban notice]
    //          fileburnbanstatus.txt = previous status. saved in this file.
    //  Exit    returns burn ban status for web page or app.
    // 
    function getFireSafetyRSS($fireburnbanRSS) {
        global $firebblink;
        $fileburnbanstatus = "fireburnbanstatus.txt"; // saves burn band status for fire only
        $fire = file_get_contents($fireburnbanRSS); //'<a href="http://www.co.pierce.wa.us/index.aspx?NID=982" style="color:red;font-weight:bold">County-wide Outdoor Burn Ban</a>'; // rfb 8/19
        if($fire===false) echo("No outdoor burn ban data read from $fireburnbanRSS");
        echo "    FIRE =$fire|";
        if(strlen($fire)<100) echo("No reply to $fireburnbanRSS");
        //echo("length of fire=" . strlen($fire) . "<br/>"); DEBUG
        $lifted = 0; // >0 if no burn ban
        $effective = 0; // >0 if there is a burn ban
        $lifted = stripos($fire, "burn ban lifted");
        if($lifted===false) $lifted = 0;
        $effective = stripos($fire, "burn ban effective");
        if($effective===false) $effective = 0;

        // check the effective & lifted switches and create the message. if no burn ban notice, use the saved status in the file.
        if($lifteed>0 && (($effective==0)||($lifted<$effective))) {
            $firebb = "<a href=\"$firebblink\" style=\"color:green;\">No Outdoor Burn Ban</a>";
        } elseif($effective > 0) {
            $firebb = "<a href=\"$firebblink\" style=\"color:red;font-weight:bold\">County-wide Outdoor Burn Ban</a>";
        } else $firebb = file_get_contents($fileburnbanstatus);
        file_put_contents($fileburnbanstatus, $firebb);  // save status for next 
        echo (";  lifted=$lifted, effective=$effective, firebb=$firebb;  ");
        return $firebb;
    }


    ////////////////////////////////////////////////////////////
    // Bailout - send error message and delete file and exit
    function Bailout($s) {
        echo "ABORT-BAILOUT ERROR: " . $s;
        //unlink($burnbanfile);
        exit;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //  getUrlContent - impersonate a brower to read a web page.
    //  Not Used because it will not pass Cloudflair security.
    function getUrlContent($url) {
        fopen("cookies.txt", "w");
        $parts = parse_url($url);
        $host = $parts['host'];
        $ch = curl_init();
        $header = array('GET /1575051 HTTP/1.1',
            "Host: {$host}",
            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language:en-US,en;q=0.8',
            'Cache-Control:max-age=0',
            'Connection:keep-alive',
            'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36',
        );
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // OBSOLETE - getFireBunBanWebStatus - reads the firebblink web page and tries to extract the status
    //  As of 10/41/21 I am blocked from reading the web page. It returns nothing. 
    //  Both file_get_contents and getUrlContent return an empty string.
    // function getFireBurnBanWebStatus() {
    //     global $firebblink;
    //     $fire = file_get_contents($firebblink); //'<a href="http://www.co.pierce.wa.us/index.aspx?NID=982" style="color:red;font-weight:bold">County-wide Outdoor Burn Ban</a>'; // rfb 8/19
    //     //if($fire == "") $fire = file_get_contents($firebblink); //1 retry
    //     //$file = getUrlContent($firebblink);
    //     echo "FIRE =$fire|";
    //     if(strlen($fire)<100) Bailout("No reply to $firebblink");
    //     //echo("length of fire=" . strlen($fire) . "<br/>"); DEBUG
    //     //$fire = strip_tags($firew);  // remove the tags
    
    //     // find starting point for burn ban
    //     $cwbb = "Fire Safety Burn Ban Status";
    //     $i = stripos($fire, $cwbb);
    //     if($i == 0) {
    //         $cwbb = "Burn Ban Status";
    //         $i = stripos($fire, $cwbb);
    //     }
    //     if($i == 0) Bailout("Could not find \"$cwbb\"");
    
    //     $lifted = 0; // >0 if no burn ban
    //     $effective = 0; // >0 if there is a burn ban
    
    //     // 3/26/19: Find the line after Current Fire Safety Burn Ban:
    //     $lifted = stripos($fire, "Current Fire Safety Burn Ban: NO BURN BAN", $i);
    
    //     // now find alt image tags (bad solution) to get the actual status
    //     if($lifted===false) $lifted = stripos($fire,'alt="FS No Burn Ban"', $i);
    //     if($lifted===false) $lifted = stripos($fire,'BURN BAN LIFTED', $i);
    //     if($lifted===false) $lifted = stripos($fire, "FIRE SAFETY - NO BURN BAN", $i); // these are alt image tags, which will change.
    //     if($lifted===false) $lifted = stripos($fire, "NO FIRE SAFETY BURN BAN", $i);
    //     $effective = stripos($fire, '"FIRE SAFETY - BURN BAN"', $i);
    //     if($effective===false) $effective = stripos($fire, '"BURN BAN IN EFFECT"', $i);
    //     if($effective===false) $effective = stripos($fire, '"FIRE SAFETY BURN BAN"', $i);
    //     if($effective===false) $effective = stripos($fire, '"Burn Ban Announcement"', $i);  // added 7/29/2020
    //     return;
    // }
?>