<?php
/////////////////////////////////////////////////////////////
//  gettanneroutage - gets the outage status from the tanner
//  web site and writes it to tanneroutage.txt.
//  this file is picked up by the getalerts.php script and sent to the app.
//  format is:
//      <h1>Anderson Island Outages.......................
//      <h6>outage information TO BE EXTRACTED </h6>
//              PRETTY SHAKEY ...
//  We will not put this into produciton until we verify the h6 stuff.
//
    $tanneroutagelink = "http://www.tannerelectric.coop/andersonisland";
    $tanneroutagefile = "tanneroutage.txt";
    chdir("/home/postersw/public_html");  // move to web root
    $str = file_get_contents($tanneroutagelink);
    $i = strpos($str, "<h1>Outages for Anderson Island");
    if($i === false) {  // if string not found
        echo("<h1> not found");
        unlink($tanneroutagefile);
        return 0;
    }
    $j = strpos($str, "<h6>", $i);
    if($j === false) {  // if string not found
            echo("<h6> not found");
        unlink($tanneroutagefile);
        return 0;
    }

    // string found. extract the first 80 characters
    $len = strlen($str); // lenght of string 

    $k = strpos($str, "</h6>", $j + 4);  // find the 2nd<h6> tag
    if($k === false) {  // if string not found
            echo("</h6> not found");
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