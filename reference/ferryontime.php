<?php
//////////////////////////////////////////////////////////////////////////////
// getferrypositioncron.php - re
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, height=device-height, target-densitydpi=medium-dpi" /> 
    <meta http-equiv="Cache-Control" content="max-age=600" />
    <title>Ferry OnTime statistics</title>
     <link rel="stylesheet" href="lib/w3.css">
     <link rel="stylesheet" type="text/css" href="css/index.css" />
        <style>
       h2 {border-style: solid; border-width: thin;border-color: black; padding:6px;background-color:deepskyblue ;font-weight:bold}

        h4 {border-style: none; border-width: thin;border-color: black; padding:6px;background-color:lightblue }
        .ic {font-size: 24px;vertical-align: text-bottom;}
    </style>
</head>

<body>
 <div class="w3-container">
    <h1>AI Ferry OnTime Statistics</h1>
    The Ferry OnTime statistics are calculated automatically by the Anderson Island Assistant.<br>
    A ferry run is considered LATE if it leaves <b>ten minutes</b> or more passed its scheduled departure time, based on position information
    reported to MarineTraffic.com.<br>
    When two ferries are running every 30 minutes, late departures are not tracked.<br>
    A ferry run is considered CANCELLED if it has not left within 50 minutes of its scheduled departure time.
    (i.e. a run can be up to 49 minutes late. After that it is considered CANCELLED and the next scheduled run is tracked)
    <br>
    <br>
    Statistics are accumulated for the last 7 days, the last 30 days, and the last year. Statistic tracking began on 1/7/2024.

<?php
ComputeFerryPerformance();
echo "<br><br><a href='https://www.anderson-island.org/ferryrunlog.txt'>List all ferry runs</a>";
echo " </div></body>";
exit();

//////////////////////////////////////////////////////////////////////////////
// ComputeFerryPerformance - reads the ferryrunlog.txt and computes the ontime performance
// called after a ferry run is logged.
//  Entry: reads ferryrunlog.txt: unixtimestamp,date,A/S,ONTIME/LATE,delaytime in min, next run time
//  Exit: writes the answer to ferryperformance.txt.
function ComputeFerryPerformance() {
    define("SecInWeek", 7*24*3600);
    define("SecInMonth", 30*24*3600);
    define("SecInYear",365*24*3600);
    define("SecInDay",24*3600); 
    $t = time();  // unix timestamp in seconds
    $D7Ontime = 0; $D7runs=0; $D7=0; $D7late=0; $D7cancelled=0;// 7 day ontime
    $D30Ontime=0; $D30runs=0; $D30=0; $D30late=0; $D30cancelled=0;// 30 day ontime
    $D365Ontime=0; $D365runs=0; $D365=0; $D365late=0; $D365cancelled=0;//365 days


    $handle = fopen("ferryrunlog.txt", "r"); // open the file for reading
    if ($handle) {
        while (($line = fgets($handle)) !== false) { // read a line
            // process the line
            $A = explode(",", $line); // split into 0unixtimestamp,1date,2A/S,3ONTIME/LATE/CANCELLED,4delaytime in min, 5next run time
            if(count($A)==6) {
                $dt = $t - (int)($A[0]);  // elapsed time in sec
                if($dt < SecInYear) {  // year
                    if($D365==0) $D365= (int)($dt/SecInDay)+1; // elapsed days
                    $D365runs++;
                    if($A[3]=="CANCELLED") $D365cancelled++;
                    elseif($A[3]=="LATE") $D365late++;

                    if($dt < SecInMonth) {  // month
                        if($D30==0) $D30= (int)(($dt/SecInDay)+1); // elapsed days
                        $D30runs++;
                        if($A[3]=="CANCELLED") $D30cancelled++;
                        elseif($A[3]=="LATE") $D30late++;

                        if($dt < SecInWeek) {  // week
                            if($D7==0) $D7= (int)(($dt/SecInDay)+1); // elapsed days
                            $D7runs++;
                            if($A[3]=="CANCELLED") $D7cancelled++;
                            elseif($A[3]=="LATE") $D7late++;
                        }
                    } 
                }
            }
        }
        fclose($handle); // close the file
    }
    // compute percent and write to ferryperformance.txt.

    $D7Ontime = $D7runs-($D7late+$D7cancelled);
    $D30Ontime = $D30runs-($D30late+$D30cancelled);
    $D365Ontime = $D365runs-($D365late+$D365cancelled);
    echo "<h2>Week: Last $D7 Days</h2>";
    $otp = (int)($D7Ontime*100/$D7runs);
    echo "Runs: $D7runs, OnTime: $D7Ontime ($otp %), Late>10 minutes: $D7late, Cancelled: $D7cancelled <br>";
    echo "<h2>Month: Last $D30 Days</h2>";
    $otp = (int)($D30Ontime*100/$D30runs);
    echo "Runs: $D30runs, OnTime: $D30Ontime ($otp %), Late>10 minutes: $D30late, Cancelled: $D30cancelled <br>";
    echo "<h2>Year: Last $D365 Days</h2>";
    $otp = (int)($D365Ontime*100/$D365runs);
    echo "Runs: $D365runs, OnTime: $D365Ontime ($otp %), Late>10 minutes: $D365late, Cancelled: $D365cancelled <br>";

}