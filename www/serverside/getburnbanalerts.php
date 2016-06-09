<?php
/////////////////////////////////////////////////////////////
//  getburnban - gets the burnban status from pscleanair
//  web site and writes it to burnban.txt.
//  this file is picked up by the getalerts.php script and sent to the app.
//  format is:
//      Peninsula\n                            <input name="ctl00$MainContent$Repeater1$ctl05$TextBox" type="text" value="No Ban"
//              6/4/16.
//
    $burnbanlink = "http://wc.pscleanair.org/burnban411/";
    $burnbanfile = "burnban.txt";
    chdir("/home/postersw/public_html");  // move to web root
    $str = file_get_contents($burnbanlink);
    // $j = strpos($str, 'Peninsula\n                            <input name="ctl00$MainContent$Repeater1$ctl05$TextBox" type="text" value="No Ban"');

     $j = preg_match('/Peninsula[^<]*<input [^<]* value="No Ban"/', $str); // look for this string
     //echo("preg_match j=$j ");

    if($j === false) {  // if string not found
        $old = file_get_contents($burnbanfile, $r);
        if($old == "") return 0;
        echo("Peninsula 'No Ban' not found");
        unlink($burnbanfile);
        return 0;
    }

    // write it
    $r = "Peninsula: NO Burn Ban";
    $old = file_get_contents($burnbanfile, $r);
    if($r == $old) return 0;
    echo $r;
    file_put_contents($burnbanfile, $r);
    return 0;
?>