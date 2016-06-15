<?php
/////////////////////////////////////////////////////////////
//  gettanneroutage - gets the outage status from the tanner
//  web site and writes it to tanneroutage.txt.
//  this file is picked up by the getalerts.php script and sent to the app.
//  format is:
//      <h1>Anderson Island Outages.......................
//      <h6>outage information TO BE EXTRACTED </h6>
//      OR
//      <h1>Anderson Island Outages...
//          text
//      </div
//
    $tanneroutagelink = "http://www.tannerelectric.coop/andersonisland";
    $tanneroutagefile = "tanneroutage.txt";
    chdir("/home/postersw/public_html");  // move to web root
    $str = file_get_contents($tanneroutagelink);
    $i = strpos($str, "<h1>Outages for Anderson Island");
    if($i === false) {  // if string not found
        echo("h1 not found");
        unlink($tanneroutagefile);
        return 0;
    }
    $j = strpos($str, "<h6>", $i);
    if($j === false) {  // if string not found
           
        //unlink($tanneroutagefile);
        $result = "";
        $str = substr($str, $i+31); // string after Outages for Anderson Island
        $A = explode("<", $str);  // break the string up into: < s1 > s2
        //echo("<br/>count of A = " . count($A) . "<br/>" );
        // loop through the strings, each of which begins with <
        for($i = 0; $i < count($A); $i++) {
            $s = $A[$i];
            $j = strpos($s, ">");  // j = closing >
            if($j == 0) $s1 = "";
            else $s1 = substr($s, 0, $j);  // s1 is the string between < and >
            $s2 = substr($s, $j + 1);  // s2 is the string After the >.  This should be text.
            // debug
            //$s1 = str_replace ("<", "&lt", $s1);$s1 = str_replace (">", "&gt", $s1);
            //$s2d = str_replace ("<", "&lt", $s2);
            //$s2d = str_replace (">", "&gt", $s2d);
            //echo ("i = $i:' $s1 '<br/>");
            //echo (" s2:' $s2d '<br/>");
            $result = $result . $s2;
            if($s1 == "/div") break;  // quit at the first /div
        }
        //echo ("<br/><br/>RESULT= $result <br/><br/>");
        $r = str_replace("  ", "", $result);
        $r = str_replace("\n", "", $r);
        $r = substr($r, 0, 137) . "...";
        //echo $r;

        // write it to the file
        $old = file_get_contents($tanneroutagefile);
        if($r == $old) return 0;
        echo("h6 not found");
        echo ("<br/><br/>RESULT= $result <br/><br/>");
        echo $r;
        file_put_contents($tanneroutagefile, $r);
        return 0;
    }

    // h6 string found. extract the first 80 characters
    $len = strlen($str); // lenght of string 

    $k = strpos($str, "</h6>", $j + 4);  // find the 2nd<h6> tag
    if($k === false) {  // if string not found
        echo("/h6 not found");
        unlink($tanneroutagefile);
        return 0;
    }
    $len = $k;   
    if($j+80 < $len) $len = $j+80;  // ensure max 80
    $r = substr($str, $j+4, $len-$j-4); // extract the string to the end of <h6>

    // write it
    $old = file_get_contents($tanneroutagefile, $r);
    if($r == $old) return 0;
    echo $r;
    file_put_contents($tanneroutagefile, $r);
    return 0;
?>