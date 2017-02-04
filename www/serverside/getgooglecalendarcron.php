<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  getgooglecalendarcron - gets the coming events/activities from the google calendar
//          for the next 2 months.
//          called by cron every day at 11:50pm.
//  Google calendar event format:
//      Event name or summary:  [E|M|S|A|C|G|O];name   MUST USE ;
//          where x=E(event)|M(meeting)|S(show) for Event, or A(activity)|C(craft)|G(game)|O(other) for activity
//      Location: the place where it happens (don't use ;)
//      Description:  sponsor and misc information  (don't use ;)
//  reads: google AndersonIsland calendar from robertbedoll@gmail.com
//      "id": "orp3n7fmurrrdumicok4cbn5ds@group.calendar.google.com",
//  writes: comingevents.txt in the coming events format:
//           mmdd;starthhmm;endhhmm;x;event;location;sponsor
// file format is:
//      0101;0000;0000;E;year ....
//      ... events ...
//      ACTIVITIES
//      0101;0000;0000;E;year ....
//      ... activities ...
// this file is copied by getdailycache.php into dailycache.txt as:
//      COMINGEVENTS
//       ... events ...
//      ACTIVITIES
//       ... activities...
//      ENDCOMINGEVENTS
//
// access google calendar:
//  https://www.googleapis.com/calendar/v3/calendars/orp3n7fmurrrdumicok4cbn5ds%40group.calendar.google.com/events?singleEvents=True&key={YOUR_API_KEY}
//  Set 'singleEvents' = true (to expand recurring events)
//  calendar key =    "kind": "calendar#calendarListEntry",
//   "etag": "\"1466657625875000\"",
//   "id": "orp3n7fmurrrdumicok4cbn5ds@group.calendar.google.com",
//   "summary": "AndersonIslandAssistant",
//   "description": "Backing calendar for Anderson Island Assistant"
// uses the api key for the service account aiacalendar@andersonislandassistant.iam.gserviceaccount.com
//  see https://console.developers.google.com/apis/dashboard?project=andersonislandassistant
//
//  01/2017 - initial
//  02/4/2017 - revised to force ; as 2nd char of event
//
chdir("/home/postersw/public_html");  // move to web root
$y = date("Y"); // year, e.g. 2017
$m = date ("m"); // month with leading zero
$d = date("d"); // day with leading zero
$mds = $y . $m . $d; // mmdd
$timemin = $y . "-" . $m . "-" . $d . "T00:00:00-07:00"; //2016-08-01T00:00:00-07:00
$ye = $y; // year end
$me = $m + 2;
if($me > 12) {  // if year rollover
    $me = $m2 - 12;
    $ye = strval($y++);
}
$timemax = $ye . "-" . sprintf("%02d", $me) . "-" .  $d . "T00:00:00-07:00"; //2016-08-01T00:00:00-07:00
echo "Events from $timemin to $timemax <br/>";

$http = "https://www.googleapis.com/calendar/v3/calendars/orp3n7fmurrrdumicok4cbn5ds@group.calendar.google.com/events?singleEvents=True&key=AIzaSyDJWvozAfe-Evy-3ZR4d3Jspd0Ue5T53E0" .
    "&orderBy=startTime&timeMin=$timemin&timeMax=$timemax";
$reply = file_get_contents($http);  // read the reply
$jreply = json_decode($reply);  // decode the json reply
//var_dump($jreply);
echo count($jreply->items) . " items. <br/>";
if (count($jreply->items) == 0) die( "No upcoming events found");
print "Upcoming events:<br/>";
$fce = fopen("comingevents.txt", "w");
$n=0;

// pick up all Events
fwrite($fce, "0101;0000;0000;E;$y Happy New Year\r\n");  // write year
$n = fcopy("xMES", $y);
echo $n . " Events.<br/>";

// pick up all Activities (AGO) from the same calendar
fwrite($fce, "ACTIVITIES\r\n");
fwrite($fce, "0101;0000;0000;E;$y Happy New Year\r\n");  // write year
echo "ACTIVITIES<br/>";
$n = fcopy("xACGO", $y);
echo $n . " Activities.<br/>";
fclose($fce);

exit;

/////////////////////////////////////////////////////////////////////////////////////////
// fcopy - copy the calendar events from the jreply structure to a file
// entry: etype = allowable calendar event types (1st letter of event)
//        ys = year e.g. '2017'
// globals: jreply = the calendar list as a json object
//          fce = the file to write to
// 
function fcopy($etype, $ys) {
    global $jreply, $fce;
    $n = 0;
    foreach ($jreply->items as $event) {  // loop through each calendar item
        $k = substr($event->summary, 0, 1); // k=the key letter of the event
        if(strpos($etype, $k) > 0) {  // if desired event
            //2016-06-28T18:30:00-07:00;2016-06-28T19:30:00-07:00;
            if(substr($event->start->dateTime,0,4) != $ys) {  // if year rollover, write the new year
                $ys = substr($event->start->dateTime,0,4);
                fwrite($fce, "0101;0000;0000;E;$ys Happy New Year\r\n");  // write year
            }
            $r = substr($event->start->dateTime,5,2) . substr($event->start->dateTime,8,2) . ";" . substr($event->start->dateTime,11,2) . substr($event->start->dateTime,14,2) . ";" .
                substr($event->end->dateTime,11,2) . substr($event->end->dateTime,14,2)  . ";" .
                $k . ";" . substr($event->summary, 2) . ";" . $event->location . ";" . $event->description ;
            $n++;
            // now write it to the file
            echo $r . "<br/>\r\n";
            fwrite($fce, $r . "\r\n");
        }
    }
    return $n;
}
?>