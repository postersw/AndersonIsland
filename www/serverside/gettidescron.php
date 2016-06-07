<?php
/////////////////////////////////////////////////////////////
//  gettidecron - gets the tides json structure from aerisapi.com
//  web site and writes it to tides.txt.
//  this file is picked up by the the app directly.
//  note that 'dailycache.txt' has TIDESDATALINK set to tidedata.txt to get this file.
//  Called by cron every 6 hours (4 times/day).  This gets around the 750 hits/day limit on the free account.
//  rfb. 6/4/16. 6/6/16.
//
    $link = "http://api.aerisapi.com/tides/9446705?client_id=pSIYiKH6lq4YzlsNY54y0&client_secret=vMb1vxvyo7Z96DSn7niwxVymzOxPN6qiEEdBk7vS&from=-15hours&to=+96hours";
    $file = "tidedata.txt";
    chdir("/home/postersw/public_html");  // move to web root
    $str = file_get_contents($link);
    if($str == "") file_get_contents($link);  // try is 2nd time if 1st one fails
    if($str == "") {  // if no data
        echo("tide cron run: NO DATA !!!");
        return 0;
    }
    // check for success
    $j = strpos($str, '"success":true');
    if($j < 0) {  // if not success
        echo("tide cron run: ERROR !!!\n $str");
        return 0;
    }
    // write to data file
    file_put_contents($file, $str);  // save the data
    echo("tide cron run successful:\n $str");
    return 0;
?>