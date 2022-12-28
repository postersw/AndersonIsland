<?php
////////////////////////////////////////////////////////////////////////////////////////
//  getweathercron - gets weather alerts and post them to weatherwarninginclude.txt.
//  reads weather from openweathermap.org and scans for weather alerts.
//      values that exceed trigger values cause alerts to be written to weatherwarninginclude.txt.
//      this file is ready by getdailycache.php.
//
//  rfb. 12/27/22. Initial version.
//       12/28/22. Add weather conditions to alerts.

    $link = 'https://api.openweathermap.org/data/2.5/forecast?id=5812092&units=imperial&APPID=f0047017839b75ed3d166440bef52bb0'; 
    chdir("/home/postersw/public_html");  // move to web root
    date_default_timezone_set('America/Los_Angeles');

    $str = file_get_contents($link);
    if($str===false) echo("no return from file get contents $link");
    $warning = CreateWeatherWarnings($str);
    if($warning != "") $warning = "<span style='color:orange'>$warning</span>";  // make orange
    file_put_contents("weatherwarninginclude.txt", $warning);
    exit();


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// CreateWeatherWarnings Read json reply data and generate weather warnings based on wind, temp, or weather id
// This is the ONLY code that parses the json forecast returned from OpenWeatherMap.
//
//  Entry   jsonforecastdata = json forecast data from OpenWeatherMap
//  Exit    Returns string of weather warnings
//
// forecast data from OpenWeatherMap:
    // {"cod":"200","message":0,"cnt":40,"list":
    //     [{	"dt":1672174800,
    //         "main":{"temp":51.89,"feels_like":50.31,"temp_min":48.31,"temp_max":51.89,"pressure":980,"sea_level":980,"grnd_level":980,"humidity":75,"temp_kf":1.99},
    //         "weather":[{"id":500,"main":"Rain","description":"lightrain","icon":"10d"}],
    //         "clouds":{"all":75},
    //         "wind":{"speed":14.41,"deg":181,"gust":31.16},
    //         "visibility":10000,
    //         "pop":1,
    //         "rain":{"3h":1.4},
    //         "sys":{"pod":"d"},
    //         "dt_txt":"2022-12-27 21:00:00"},  {...}]
    //
// weather->id  description
//200	Thunderstorm	thunderstorm with light rain	 11d
// 201	Thunderstorm	thunderstorm with rain	 11d
// 202	Thunderstorm	thunderstorm with heavy rain	 11d
// 210	Thunderstorm	light thunderstorm	 11d
// 211	Thunderstorm	thunderstorm	 11d
// 212	Thunderstorm	heavy thunderstorm	 11d
// 221	Thunderstorm	ragged thunderstorm	 11d
// 230	Thunderstorm	thunderstorm with light drizzle	 11d
// 231	Thunderstorm	thunderstorm with drizzle	 11d
// 232	Thunderstorm	thunderstorm with heavy drizzle	 11d
// 500	Rain	light rain	 10d
// 501	Rain	moderate rain	 10d
// 502	Rain	heavy intensity rain	 10d
// 503	Rain	very heavy rain	 10d
// 504	Rain	extreme rain	 10d
// 511	Rain	freezing rain	 13d
// 520	Rain	light intensity shower rain	 09d
// 521	Rain	shower rain	 09d
// 522	Rain	heavy intensity shower rain	 09d
// 531	Rain	ragged shower rain	 09d
// 600	Snow	light snow	 13d
// 601	Snow	Snow	 13d
// 602	Snow	Heavy snow	 13d
// 611	Snow	Sleet	 13d
// 612	Snow	Light shower sleet	 13d
// 613	Snow	Shower sleet	 13d
// 615	Snow	Light rain and snow	 13d
// 616	Snow	Rain and snow	 13d
// 620	Snow	Light shower snow	 13d
// 621	Snow	Shower snow	 13d
// 622	Snow	Heavy shower snow	 13d
// 762	Ash	volcanic ash	 50d
// 771	Squall	squalls	 50d
// 781	Tornado	tornado	 50d

function CreateWeatherWarnings($jsonforecastdata) {
    if ($jsonforecastdata == null) return;
    $forecast = json_decode($jsonforecastdata);
    if ($forecast === FALSE) return;
    $utcadjustment = 8*3600; // convert to local time
    $windtrigger = 20;
    $lowtemptrigger = 25;
    $hightemptrigger = 90;
    $windalert = "";
    $tempalert = "";
    $rainalert = "";
    // alertable trigger ids - see table above.
    $alertids = [200,201,202,210,211,212,221,230,231,232,502,503,504,522,522,531,600,601,602,622,612,613,615,620,621,622,762,771,781]; // weather id codes for alerts
    //var_dump($forecast);

    $resp = $forecast->list;  // pick out the list of forecast periods

    //  check each weather period for the next 24 hours
    for ($i = 0; $i < 7; $i++) {
        $r = $resp[$i];  // get 1 weather period
        $timef = $r->dt;// unix gmt time in sec since 1970
        $dt = date("D g a", $timef);  // rely on PHP to adjust time zone. converts utc to current default_timezone
        //echo ("dateUCT=$dt <br>");
        
        // wind alerts
        $wind = intval($r->wind->speed);
        if($wind>$windtrigger && $windalert == "") {
            $windalert = "<b>Wind alert for $dt: winds $wind mph, gusts " . intval($r->wind->gust) . "</b><br>";
            echo $windalert;
        }
        $winddir = $r->wind->deg;
        
        // temperature alerts
        $lowtemp = intval($r->main->temp_min);
        $hightemp = intval($r->main->temp_max);
        if($lowtemp < $lowtemptrigger && $tempalert == "") {
            $tempalert = "<b>Low temp alert for $dt: $lowtemp degrees</b><br>";
            echo $tempalert;
        }
        if($hightemp > $hightemptrigger && $tempalert == "") {
            $tempalert = "<b>High temp alert for $dt: $hightemp degrees</b><br>";
            echo $tempalert;
        }
        // weather alerts
        if($rainalert=="") {  // if no alert
            if(array_search($r->weather[0]->id, $alertids)!==false) {  // if id code found in alertids array
                $rainalert = "<b>Weather alert for $dt: " . $r->weather[0]->description . "</b><br>";
            }
        }
        //echo ("timef=$timef,$dt  wind=$wind from $winddir, lowtemp=$lowtemp, hightemp=$hightemp, rain=$rainalert<br>");
    }
    return  $windalert . $tempalert . $rainalert;
}

   
?>