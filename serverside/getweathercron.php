<?php
////////////////////////////////////////////////////////////////////////////////////////
//  getweathercron - gets weather alerts and post them to weatherwarninginclude.txt.
//
//  rfb. 12/27/22. Initial version.

    $link = 'https://api.openweathermap.org/data/2.5/forecast?id=5812092&units=imperial&APPID=f0047017839b75ed3d166440bef52bb0'; 
    chdir("/home/postersw/public_html");  // move to web root
    date_default_timezone_set('America/Los_Angeles');

    $str = file_get_contents($link);
    if($str===false) echo("no return from file get contents $link");
    $warning = CreateWeatherWarnings($str);
    file_put_contents("weatherwarninginclude.txt", $warning);
    exit();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// CreateWeatherWarnings Read json reply data and alwaysbuild the gWeatherPeriods array of WeatherPeriod objects
// This is the ONLY code that parses the json forecast returned from OpenWeatherMap.
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
function CreateWeatherWarnings($jsonforecastdata) {
    if ($jsonforecastdata == null) return;
    $forecast = json_decode($jsonforecastdata);
    if ($forecast === FALSE) return;
    $utcadjustment = 8*3600; // convert to local time
    $windtrigger = 22;
    $lowtemptrigger = 20;
    $hightemptrigger = 90;
    $windalert = "";
    $tempalert = "";
    $rainalert = "";
    //var_dump($forecast);

    $resp = $forecast->list;  // pick out the list of forecast periods
    date_default_timezone_set("UTC"); // set UTC
    $tnow = time();  // lcurrent time in UTC seconds

    //  check each weather period for the next 24 hours
    for ($i = 0; $i < 7; $i++) {
        $r = $resp[$i];  // get 1 weather period
        $timef = $r->dt;// unix gmt time in sec since 1970
        $timeage = ($timef-$tnow)/3600; // age in hours
        $dt = date("D g a", $timef-$utcadjustment);  // rely on PHP to adjust time zone
        echo ("dateUCT=$dt <br>");
        $wind = intval($r->wind->speed);
        if($wind>$windtrigger && $windalert == "") {
            $windalert = "Wind alert for $dt: winds $wind mph, gusts " . intval($r->wind->gust) . "<br>";
            echo $windalert;
        }
        $winddir = $r->wind->deg;
        //$rain = $r->rain["3h"];
        $rain = "?";
        $lowtemp = intval($r->main->temp_min);
        $hightemp = intval($r->main->temp_max);
        if($lowtemp < $lowtemptrigger && $tempalert == "") {
            $tempalert = "Low temp alert for $dt: $lowtemp degrees<br>";
            echo $tempalert;
        }
        if($hightemp > $hightemptrigger && $tempalert == "") {
            $tempalert = "High temp alert for $dt: $hightemp degrees<br>";
            echo $tempalert;
        }
        echo ("timef=$timef,age=$timeage,  wind=$wind from $winddir, lowtemp=$lowtemp, hightemp=$hightemp, rain=$rain<br>");
        //if (typeof r.rain == 'undefined' || typeof r.rain["3h"] == 'undefined') rain = 0;
        //else rain = (Number(r.rain["3h"]) / 25.4);
        //gWeatherPeriods[i] = new NewWeatherPeriod(timef, Number(r.main.temp_max), r.weather[0].description, icon, rain, Number(r.wind.deg), Number(r.wind.speed))
    }
    return  $windalert . $tempalert . $rainalert;
}

   
?>