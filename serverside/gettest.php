<?php
/////////////////////////////////////////////////////////////
//  gettest - test framework
//
    $link = "https://secure.pscleanair.org/AirQuality/BurnBan";
    $link = "https://www.piercecountywa.gov/RSSFeed.aspx?ModID=1&CID=All-newsflash.xml";
    
    chdir("/home/postersw/public_html");  // move to web root

    date_default_timezone_set("UTC"); // set UTC
    $utc = time();
    echo " utc=" . date("m/d/y h:i:s", $utc);
    date_default_timezone_set("America/Los_Angeles"); // set UTC
    echo " pst" . date("m/d/y h:i:s", $utc);

    $pt = time();
    echo " utc=" . date("m/d/y h:m:s");
    $delta = ($pt-$utc) / 3600;
    echo "utc=$utc, pt=$pt, delta=$delta";

    ComputeFerryPerformance();
    return;

// AIR QUALITY: read the Air Quality page and extract the data for peninsula

    $str = file_get_contents($link);
    if($str===false) echo("no return from file get contents $link");
    echo(" returned characters=" . strlen($str));
    echo "--------------------------------------------------------";
    echo "str=$str";
    echo "--------------------------------------------------------";
    
    $str2 = getUrlContent($link);
    if($str2===false) echo("no return from file get contents $link");
    echo(" returned characters=" . strlen($str2));   
    echo "str2=$str2";
    echo "==========================================================";
    
    if($str == $str2) echo(" the 2 strings match");
    else echo (" the 2 strings are different");

    ////////////////////////////////////////////////////////////
    // Bailout - send error message and delete file and exit
    function Bailout($s) {
        echo "Error: " . $s;
        //unlink($burnbanfile);
        exit;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //  getUrlContent - impersonate a brower to read a web page.
    //  Not Used.
    function getUrlContent($url) {
        fopen("cookies.txt", "w");
        $parts = parse_url($url);
        $host = $parts['host'];
        $ch = curl_init();
        $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, $agent);
curl_setopt($ch, CURLOPT_URL,$url);
       // $header = array('GET /1575051 HTTP/1.1',
       //     "Host: {$host}",
       //     'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
       //     'Accept-Language:en-US,en;q=0.8',
       //     'Cache-Control:max-age=0',
       //     'Connection:keep-alive',
       //     'Host:adfoc.us',
       //     'User-Agent:Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)',
       // );
    //          'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36',
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        // curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    
        // curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
        // curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

//////////////////////////////////////////////////////////////////////////////
// ComputeFerryPerformance - reads the ferryrunlog.txt and computes the ontime performance
// called after a ferry run is logged.
//  Entry: reads ferryrunlog.txt: unixtimestamp,date,A/S,ONTIME/LATE,delaytime in min, next run time
//  Exit: writes the answer to ferryperformance.txt.
function ComputeFerryPerformance() {
    define("SecInWeek", 7*24*3600);
    define("SecInMonth", 30*24*3600);
    define("SecInYear",365*24*3600);
    echo SecInWeek; echo "<br>";
    echo SecInMonth; echo "<br>";
    $t = time();  // unix timestamp in seconds
    $D7Ontime = 0; $D7runs=0; // 7 day ontime
    $D30Ontime=0; $D30runs=0; // 30 day ontime
    $D365Ontime=0; $D365runs=0; //365 days

    $handle = fopen("ferryrunlog.txt", "r"); // open the file for reading
    if ($handle) {
        while (($line = fgets($handle)) !== false) { // read a line
            // process the line
            $A = explode(",", $line); // split into unixtimestamp,date,A/S,ONTIME/LATE,delaytime in min, next run time
            if(count($A)==6) {
                $dt = $t - (int)($A[0]);  // elapsed time in sec
                echo "dt=$dt<br>";
                if($dt < SecInYear) {  // year
                    $ontime=((int)$A[4] < 10);  // true if ontime
                    $D365runs++;
                    if($ontime) $D365Ontime++; // if delay < 10
                    echo "D365=$D365runs<br>";
                    if($dt < SecInMonth) {  // month
                        $D30runs++;
                        if($ontime) $D30Ontime++; // if delay < 10
                        echo "D30runs=$D30runs<br>";
                        if($dt < SecInWeek) {  // week
                            $D7runs++;
                            if($ontime) $D7Ontime++; // if delay < 10
                            echo "D7runs=$D7runs, D7Ontime=$D7Ontime<br>";
                        }
                    } 
                }
            }
        }
        fclose($handle); // close the file
    }
    // compute percent and write to ferryperformance.txt.
    if($D7runs>0) {
        $D7Ontime--;
        $m = "Ferry OnTime: Last 7 Days " . intval($D7Ontime*100/$D7runs) . "%, Last 30 days " . intval($D30Ontime*100/$D30runs) . "%\n";
        file_put_contents("ferryperformance.txt", $m);
        echo "D7Ontime-$D7Ontime, D7runs=$D7runs, D30Ontime=$D30Ontime,D30runs=$D30runs,M=$m"; // debug
    }
}
?>