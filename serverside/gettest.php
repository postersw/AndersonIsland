<?php
/////////////////////////////////////////////////////////////
//  gettest - test framework
//
    $link = "https://secure.pscleanair.org/AirQuality/BurnBan";
    $link = "https://www.piercecountywa.gov/RSSFeed.aspx?ModID=1&CID=All-newsflash.xml";
    
    chdir("/home/postersw/public_html");  // move to web root

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


?>