<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  getgooglecalendarcron - gets the coming events/activities from the google calendar
//                  called by cron every day at 11:50pm.
//  reads: google AndersonIsland calendar from robertbedoll@gmail.com
//  writes: comingevents.txt in the coming events format:
//           mmdd,starthhmm,endhhmm,x,event,location,sponsor
// file format is:
//      ... events ...
//      ACTIVITIES
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
// follow the instructions to get the google php library. it is needed for authorization.
?>