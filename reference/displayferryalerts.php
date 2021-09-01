<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  displayferryalelrts - displays the ferry alert log in reverse order
//  called by user from the Anderson Island Assistant: displayferryalerts.php.  (no parameters)
//
//  The alerts are saved in alertlog.txt by the getferryalerts.php script.
//
//  Bob Bedoll. 8/31/21.
//


chdir("/home/postersw/public_html");
$Log = array();
//chdir("C:\A");////////////////// DEBUG for local PC //////////////////////////
date_default_timezone_set("America/Los_Angeles"); // set PDT
echo "<html><head>";
echo '<meta name="viewport" content="initial-scale=1, width=device-width,height=device-height,target-densitydpi=medium-dpi,user-scalable=no" />';
echo "<style>body {font-family:'HelveticaNeue-Light','HelveticaNeue',Helvetica,Arial,sans-serif;} table,td {border:1px solid black;border-collapse:collapse;font-size: larger} A {text-decoration: none;} </style></head>";
echo "<h1>Ferry Alert History</h1>Most recent message first.<p/>";
$n = ReadFerryLog();
DisplayFerryLog($n);

exit();


/////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  ReadFerryLog
//  
//  Entry
//  Exit    log loaded into Log[] array
//          returns highest index of message in log array
function ReadFerryLog() {
    $i = 0;
    global $Log;
    $ferrylog = "alertlog.txt";
    $file = fopen($ferrylog, "r");
    while(! feof($file)) {
        $s = fgets($file);
        if(strpos($s, "Cleared alert ")) continue;  // skip all cleared alert messages
        if(substr($s, 0, 2) == "20") {
            $Log[$i] = $s;
            $i++;
        } else $Log[$i-1] .=  $s;
    }
    fclose($file);
    //echo "Read $i messages <br/>";
    return $i-1;
}

/////////////////////////////////////////////////////////////////
//  DisplayFerryLog
//
//  Entry n = highest index
//        $Log = array of strings
//
//  Exit    messages written to output in reverse order
function  DisplayFerryLog($n) {
    global $Log;
    $limit = $n - 70; //number of messages in history to DisplayFerryLog
    //echo "n = $n, limit=$limit<br/>";
    //echo "debug: " . substr($Log[$n-1], 20) . "<br/>";
    for($i = $n; $i>=$limit; $i--) {
        echo "<strong>" . substr($Log[$i], 20, 14) . "</strong>" . substr($Log[$i], 34) . "<br/><br/>";
        if($i == 0) break;
    }
}

?>