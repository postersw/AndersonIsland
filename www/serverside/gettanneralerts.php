<?php
/////////////////////////////////////////////////////////////
//  gettanneroutage - gets the outage status from the tanner
//  web site and writes it to tanneroutage.txt.
//  this file is picked up by the getalerts.php script and sent to the app.
//  format is:
//      <h1>Anderson Island Outages.......................
//         <blah> <blah> <blah>
//          All text outside of <> and before the closing </div is picked up
//      <blah>
//      </div
//
    $tanneroutagelink = "http://www.tannerelectric.coop/andersonisland";
    $tanneroutagefile = "tanneroutage.txt";
    chdir("/home/postersw/public_html");  // move to web root
    $str = file_get_contents($tanneroutagelink);
    if($str == "") $str = file_get_contents($tanneroutagelink);  // try again if no result
    if($str == "") $str = file_get_contents($tanneroutagelink);
    $i = strpos($str, "<h1>Outages for Anderson Island");
    if($i === false) {  // if string not found
        echo("h1 not found");
        unlink($tanneroutagefile);
        return 0;
    }

    // Now extract the TEXT that occurs after the <h1> structure.  Stop at </div
    //  TEXT is everything between <...> and <...>

    $result = "";
    $str = substr($str, $i+31); // string after Outages for Anderson Island
    $A = explode("<", $str);  // break the string up into: < s1 > s2

    // loop through the $A strings, each of which is: < s1 > s2
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
        if($s1 == "p") $result = $result . "<p>";
        $result = $result . $s2;
        if($s1 == "/div") break;  // quit at the first /div
    }

    // eliminate newline and multiple blanks
    $r = preg_replace('/\s+/', ' ', $result); // remove all duplicate blanks and whitespace characters
    // if an update, find the last one.
    //$i = strripos ($r, "Update ");
    //if($i > 0) {
    //    $r = substr($r, $i);  // get the update
    //}
    // break $r into paragraphs
    $P = explode("<p>", $r); 
    // use the last paragraph
    $r = $P[count($P)-1];
    if (strlen($r) > 137) $r = substr($r, 0, 300) . "...";

    // write it to the file
    $old = file_get_contents($tanneroutagefile);
    if($r == $old) return 0;
    echo ("<br/><br/>RESULT= $result <br/><br/>");
    echo $r;
    file_put_contents($tanneroutagefile, $r);
    return 0;

?>