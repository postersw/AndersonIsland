<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  getgooglecalendarcron - gets the coming events/activities from the AndersonIslandAssistant Google calendar
//          for the next 6 months and writes them to the comingevents.txt file
//          called by cron every day at 11:50pm.
//  To set immediate refresh flag for the app:
//      getgooglecalendarcron?refresh=true
//
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
// this file is copied by getdailycache.php to the app as:
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
//  03/31/2017 - fix to prevent illegal end dates like 0631
//  10/02/2017 - fix for year rollover.  Also limit to 100 events and 100 activities.
//  05/17/2018 - 6 month lookahead. Change event limit to 999 but leave activity limit at 100.
//  02/13/2019 - set or clear "refresh.txt" file.
//  09/14/2019 - ignore \n in event description 
//  12/1/2022 - fixed $me at line 60 in next year calculation
//  1/21/2023 - allow for no description in event object
//  4/7/2024 - add the word "More..." to the location of the 2nd event so it is displayed on the main screen for Events.
//  4/28/2024   Test for $location before using it.
//
chdir("/home/postersw/public_html");  // move to web root
$y = date("Y"); // year, e.g. 2017
$m = date ("m"); // month with leading zero
$d = date("d"); // day with leading zero
$mds = $y . $m . $d; // mmdd
$timemin = $y . "-" . $m . "-" . $d . "T00:00:00-07:00"; //2016-08-01T00:00:00-07:00
$ye = $y; // year end
$me = $m + 6; // changed 5/18/18 from 3 months to 6 months
$de = $d;
if($me > 12) {  // if year rollover
    $me = $me - 12;
    $ye = strval($y+1);
}
if($me == 2 && $de > 28) $de = 28;  // prevent illegal feb  date
if(($me == 9 || $me == 4 || $me == 6 || $me = 11) && ($de > 30)) $de = 30; // prevent illegal end date
$timemax = $ye . "-" . sprintf("%02d", $me) . "-" .  $de . "T00:00:00-07:00"; //2016-08-01T00:00:00-07:00
echo "$m/$d/$y<br/>";
echo "Events from $timemin to $timemax <br/>";

$http = "https://www.googleapis.com/calendar/v3/calendars/orp3n7fmurrrdumicok4cbn5ds@group.calendar.google.com/events?singleEvents=True&key=AIzaSyDJWvozAfe-Evy-3ZR4d3Jspd0Ue5T53E0" .
    "&maxResults=2000&orderBy=startTime&timeMin=$timemin&timeMax=$timemax";  // max results set to 2000 on 5/18/18
$reply = file_get_contents($http);  // read the reply
$jreply = json_decode($reply);  // decode the json reply
//var_dump($jreply);
echo count($jreply->items) . " items. <br/>";

// if an error
if (count($jreply->items) == 0) {
    echo "ERROR - no upcoming events returned. \n";
    echo $reply; // print the error
    die( "No upcoming events found");
}

print "Upcoming events:<br/>";
$fce = fopen("comingevents.txt", "w");
$n=0;

// pick up all Events

fwrite($fce, "0101;0000;0000;E;$y Happy New Year\r\n");  // write year
echo "0101;0000;0000;E;$y Happy New Year<br/>";
$n = fcopyMore("xMES", $y);
echo $n . " Events.<br/>";


// pick up all Activities (AGO) from the same calendar
fwrite($fce, "ACTIVITIES\r\n");
fwrite($fce, "0101;0000;0000;E;$y Happy New Year\r\n");  // write year
echo "ACTIVITIES<br/>";
echo "0101;0000;0000;E;$y Happy New Year<br/>";
$n = fcopy("xACGO", $y); // NOTE: if you change "xACGO", see line 110.
echo $n . " Activities.<br/>";
fclose($fce);

SetRefresh(); // sets or clears the refresh.txt file
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
    if(strpos($etype, "A") > 0) {  // if Activities
        $nlimit = 100; // limit to 100 activities
    } else {
        $nlimit = 999; // no limit on events
    }
    foreach ($jreply->items as $event) {  // loop through each calendar item
        $k = substr($event->summary, 0, 1); // k=the key letter of the event
        if(strpos($etype, $k) > 0) {  // if desired event
            //2016-06-28T18:30:00-07:00;2016-06-28T19:30:00-07:00;
            if(substr($event->start->dateTime,0,4) != $ys) {  // if year rollover, write the new year
                $ys = substr($event->start->dateTime,0,4);
                fwrite($fce, "0101;0000;0000;E;$ys Happy New Year\r\n");  // write year
                echo "0101;0000;0000;E;$ys Happy New Year generated for event year change<br/>";
            }
            if(property_exists($event, "description")) $desc = str_replace("\n", " ", $event->description); // remove line feeds
            else $desc = "";
            $r = substr($event->start->dateTime,5,2) . substr($event->start->dateTime,8,2) . ";" . substr($event->start->dateTime,11,2) . substr($event->start->dateTime,14,2) . ";" .
                substr($event->end->dateTime,11,2) . substr($event->end->dateTime,14,2)  . ";" .
                $k . ";" . substr($event->summary, 2) . ";" . $event->location . ";" . $desc ;
            $n++;
            if($n > $nlimit) break;  // limit to 100 activities to prevent too much data on phone.  (5/18/18).
            // now write it to the file
            echo $r . "<br/>\r\n";
            fwrite($fce, $r . "\r\n");
        }
    }
    return $n;
}

/////////////////////////////////////////////////////////////////////////////////////////
// fcopyMore - copy the calendar events from the jreply structure to a file
//              Add "More..." to the location of the 2nd day
// entry: etype = allowable calendar event types (1st letter of event)
//        ys = year e.g. '2017'
// globals: jreply = the calendar list as a json object
//          fce = the file to write to
//
function fcopyMore($etype, $ys) {
    global $jreply, $fce;
    $n = 0;
    if(strpos($etype, "A") > 0) {  // if Activities
        $nlimit = 100; // limit to 100 activities
    } else {
        $nlimit = 999; // no limit on events
    }
    $r1 = ""; $r2 = ""; $lastdate = "";
    $ndate = 0;
    foreach ($jreply->items as $event) {  // loop through each calendar item
        $k = substr($event->summary, 0, 1); // k=the key letter of the event
        if(strpos($etype, $k) > 0) {  // if desired event
            //2016-06-28T18:30:00-07:00;2016-06-28T19:30:00-07:00;

            // now write previous line  to the file.  
            // We do this because we want to append the word "More" to the location of the LAST event in day 2.
            if($r1!="") {
                // if a new day, then for day 2 add "More..." to the location.
                if(substr($event->start->dateTime,0,10) != $lastdate) {
                    $ndate = $ndate + 1;
                    if($ndate == 2) $r1 = $r1 . "<br><b>More ...</b>";  // add More
                    $lastdate = substr($event->start->dateTime,0,10);
                }
                echo $r1 . $r2 . "<br/>\r\n";
                fwrite($fce, $r1 . $r2 . "\r\n");
            }

            if(substr($event->start->dateTime,0,4) != $ys) {  // if year rollover, write the new year
                $ys = substr($event->start->dateTime,0,4);
                fwrite($fce, "0101;0000;0000;E;$ys Happy New Year\r\n");  // write year
                echo "0101;0000;0000;E;$ys Happy New Year generated for event year change<br/>";
            }
            if(property_exists($event, "description")) $desc = str_replace("\n", " ", $event->description); // remove line feeds
            else $desc = "";
            if(property_exists($event, "location")) $loc = $event->location;
            else $loc = "";
            // build the line to print on the next iteration;
            $r1 = substr($event->start->dateTime,5,2) . substr($event->start->dateTime,8,2) . ";" . substr($event->start->dateTime,11,2) . substr($event->start->dateTime,14,2) . ";" .
                substr($event->end->dateTime,11,2) . substr($event->end->dateTime,14,2)  . ";" .
                $k . ";" . substr($event->summary, 2) . ";" . $loc;
            $r2 =  ";" . $desc ;
            $n++;
            if($n > $nlimit) break;  // limit to 100 activities to prevent too much data on phone.  (5/18/18).

        }
    }
    return $n;
}

/////////////////////////////////////////////////////////////////////////
//  SetRefresh - sets or clears the refresh file
//  if refresh=true in the GET header, writes a time stamp to the refresh.txt file
//  This requests the app to call GetDailyCache immediately, which refreshes the calendar.
//      The app remembers the refresh value and will not refresh until the value changes, or until the date changes.
//  otherwise deletes the refresh.txt file.
function SetRefresh() {
    $refreshfile = "refresh.txt";
    $refresh = "";
    if(array_key_exists("refresh", $_GET)) {
        $refresh = date("m/d/Y h:i:s");
        file_put_contents($refreshfile,$refresh);
        echo "<br/>Wrote $refreshfile: $refresh <br/>";
    } else {
        if(file_exists($refreshfile)) unlink($refreshfile);
        echo "<br/>Removed $refreshfile.<br/>";
    }
}
?>