/*****************************************************************************
 * index.js - ALL JAVASCRIPT FOR AIA
 * Javascript for AIA consolidated into this single file
     ver 1.5.0509: add OpenHours object (multiple date ranges and time ranges).  Fix weather month.
            0515: Sunrise/Sunset in tide graph, add other days. Color code activities. Local version of jquery. 
                  Onclick support for events and add-to-google-calendar.
                  My own dialog box div. Formatting improvements.
                  Menu. (moved about, update, to menu)
            0524: Full page for each business on click.
            0527: call getalerts.php for ferry, burnban, tanner alers.
            0529: jquery removed.  remove it from git.
            0530: moved all javascript into index.js.
            0603.2300: fixed top bar.
            0604.1600: add TIDEDATALINK to dailycache.txt. 
        1.6 0606.1600: move dailycache.txt, comingevents.txt, tidedata.txt to getdailycache.php.
        1.6.0607.1601: Android release.  tracks pages used.
        1.6.0610.1700: IOS release on 6/10. IOS release approved 6/13.
        1.6.0614.2100: reactivate android message. 
        1.7.0702.2300: Created branch Ver17 for dev. Branch 16Prod captures the prod 1.6.0614 branch.
                       Force alert reload on every start. Set alert timeout=8 min.
            0705.2100: Web. Fishing link. Add parameters to ShowLinksPage
            0706.2100: Web. Consolidate GetForecast to one routine to fix weather forecast update bug.
            0710.2300: Ferry schedule: handle next day correctly on main page.
            0823.2300: Use FERRYTA/S/K with embedded rules. No hardcoded rules.
            0918.1000: Pubhbots: Call Pushbots only every 3 days to cut down on API calls.
                       Version check: Add version check for ANDRIODVER and IOSVER and display message.
            0929     : Coming Events: Automatically add year to all calendar dates and hande year rollover correctly.
            1007     : Ferry Schedule Grid: move headers to each day. Color am backgound blue.
            1010     : Android ver 2220 to Google Play
            10.14    : ClearCacheandExit button; extra null protections.
        1.8 0307 (2017): Add Ferry Location link and Ferry Schedule link to dailycache.
        1.9 039817  : Add TICKETS link that actually starts the ticket app on the phone.
        1.10 031417: Make ferry ticket row narrower.  Fix for IOS.
        1.11 032017: Remove alert from IOS when the ticket app is not there.
             040117: Display 'DELAYED' in ferry time if alert message contains 'DELAY:'
             040617: Switch Ticket launcher to hutchind.cordova.plugins.launcher that works for the iphone. 
             040817: Get Alerts every minute.
             041017: Change sunrise/sunset to hh:mm. 
             041117: remove splash screen for android and hide mainpage during startup.
        1.13 052100: Ferry times on main page: 3/row. time till run. Highlight by location.
        1.14 0614:   Fix Android launch icon. Released to Google play store. NOT released to IOS.
        1.15 0623:   IOS Version. Show selected options on the menu screen.
        1.16 010518. Fix thanksgiving date calc.  0124. Make current time green on events. Add MAINTABLEROWS.
             020218. Change tide display on main page to a table.
             041518. Add arrows to tide display. 
        1.17.042518. Upgrade config.xml to cli-7.1.0. to pick up fix for Android 8 and pushbots. No code changes. ANDROID play store only.
        1.18 051318. Error handling for data errors. Custom Tide request: wait message. call NOAA directly. WEB only.
        1.19 052318. Replace Pushbots by OneSignal because its free. Android only.
        1.20 070118. Horiz scroll of tide graph.  Display full day of events.  Fix ferry grid for one-way runs. clearOneSignalNotifications. pierceferryticketlink.
        1.21 072618. Icons on main page.  Released to web.
             081818. Moon phases added to weather.
        1.22 101318. Text to Speech for ferry.
 * 
 *  copyright 2016-2018, Robert Bedoll, Poster Software, LLC
 * All Javascript removed from index.html
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
const gVer = "1.22.111818.1";  // VERSION MUST be n.nn. ...  e.g. 1.07 for version comparison to work.
var gMyVer; // 1st 4 char of gVer
const cr = "copyright 2016-2018 Robert Bedoll, Poster Software LLC";

const gNotification = 2;  // 0=no notification. 1=pushbots. 2=OneSignal

var app = {
    // Application Constructor
    initialize: function () {
        this.bindEvents();
    },
    // Bind Event Listeners
    //
    // Bind any events that are required on startup. Common events are:
    // 'load', 'deviceready', 'offline', and 'online'.
    bindEvents: function () {
        document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    // deviceready Event Handler
    //
    // The scope of 'this' is the event. In order to call the 'receivedEvent'
    // function, we must explicitly call 'app.receivedEvent(...);'
    onDeviceReady: function () {
        //navigator.splashscreen.hide();
        if (localStorage.getItem("notifyoff") == null) { // if notify isn't off
            switch(gNotification) {
                case 1: // pushbots
                    //window.plugins.PushbotsPlugin.initialize("570ab8464a9efaf47a8b4568", { "android": { "sender_id": "577784876912" } });
                    //window.plugins.PushbotsPlugin.resetBadge();  // clear ios counter
                    break;
                case 2:  // OneSignal v1.19 5/23/18 .  App id=a0619723-d045-48d3-880c-6028f8cc6006
                    window.plugins.OneSignal
                        .startInit("a0619723-d045-48d3-880c-6028f8cc6006")
                        .endInit();
                    window.plugins.OneSignal.clearOneSignalNotifications();  // clear all notifications from the shade
                    break;
            }
            localStorage.setItem("pushbotstime", gTimeStampms.toFixed(0));
        }
        app.receivedEvent('deviceready');

    },
    // Update DOM on a Received Event. commented out on 2/16/16 because we don't need it.
    receivedEvent: function (id) {
        //var parentElement = document.getElementById(id);
        //var listeningElement = parentElement.querySelector('.listening');
        //var receivedElement = parentElement.querySelector('.received');

        //listeningElement.setAttribute('style', 'display:none;');
        //receivedElement.setAttribute('style', 'display:block;');

        //console.log('Received Event: ' + id);
    }

};

/////////////////////////////////////////////////////////////////////////////////////////////////////
// JavaScript source code specifically for Anderson Island Assistant
//  RFB 2/2/2016
"use strict";
/////////////////////////  DATE ///////////////////////////////////////////////////////////////////////
// global date variables
var table; // the schedule table as a DOM object
var Gd; // date object
var gTimeStampms; // unix ms since 1970
var gDayofWeek;  // day of week in 0-6
var gWeekofMonth; // week of the month
var gLetterofWeek; // letter for day of week
var gTimehhmm;  // hhmm in 24 hour format
var gTimehh; // time in hours
var gTimemm; // time in seconds
var gYear; // year 
var gMonth;  // month 1-12. note starts with 1
var gDayofMonth; // day of month 1-31
var gMonthDay; // mmdd
var gYYmmdd; // yymmdd
var laborday = 0; // first monday in sept.  we need to compute this dyanmically
var memorialday;  // last monday in may
var thanksgiving;
var holiday;  // true if  holiday

var gDayofWeekName = ["SUNDAY", "MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY"];
var gDayofWeekNameL = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
var gDayofWeekShort = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
var scheduledate = ["5/1/2014"];

// global variables (gXxxxxx). Since this entire app is one page and one program, these variables hold for the entire execution.


var gisPhoneGap; // true if phonegap
var gisAndroid; // true if android
var gisMobile; // true if mobile (even if a browser)

var openHoursLastUpdate; // time of last update
var gAlertCounter = 0;
var gAlertTime = 0; // saved value in sec
var gFocusCounter = 0;
var gFocusTime = 0; // saved value in sec
var gResumeCounter = 0;
var gResumeTime = 0;// saved value in sec

// Location set by gelocation for phone only
gLatitude = 0.0; // NS
gLongitude = 0.0; // EW
gLocationOnAI = 99; // 99 = don't highlight (unknown). 0=Steilacoom, 1=Anderson Island
gLocationTime = 0; // time of last location update


// ferry time switches default
var gFerryShowIn = 1; // 1 to show (in nnm) on 1st time. Set from "gferryshowin". Defaults to 1.
var gFerryShow3 = 0; // show 3 times. Set from "gferryshow3"
var gFerryHighlight = 0; // highlight ferry AI or Steilacoom depending on user location. Set from "ferryhighlight"

// tides
var nextTides; // string of next tides for the main page
var tidesLastUpdate; // time of last update

// weather counters
var gWeatherForecastCount = 0; // number of weather forcast requests to debug API key exceeding 60 rpm
var gWeatherCurrentCount = 0; // number of weather current requests to debug API key exceeding 60 rpm

// icons
var gIconSwitch = 1; // icon switch, : 1=icon+lc,2=icon+uc,3=icon,4=uc,5=lc. Only 1 and 4 are used.
var gEventIcons = true;

//  TXTX - Text to Speech Object.
var TXTS = {
    OnOff: 0, // 0 = off, 1 = on, 2 = large text (instead of speech)
    FirstTime: false, // true to issue first time message
    FerryTime: "", // global ferry time text-to-speech string
    TideData: "", // global tide data text-to-speech string
    WeatherCurrent: "", // global weather current text-to-speech string
    WeatherForecast: "", // global weather forecast text-to-speech string
    Next: "", // event string
    OpenHours: "", // open store hours
    BurnBanStatus: "",
    TannerOutageStatus: ""
};

//  BigText -  BigText object. 
var BIGTX = {
    OnOff: 0, // 0 = off, 1 = on, 
    FerryTime: "", // global ferry time text-to-speech string
    TideData: "", // global tide data text-to-speech string
    WeatherCurrent: "", // global weather current text-to-speech string
    WeatherForecast: "", // global weather forecast text-to-speech string
    Next: "", // event string
    OpenHours: "", // open store hours
    BurnBanStatus: "",
    TannerOutageStatus: ""
};



///////////////////////////////////////////////////////////////////////////////////////
// return true if a holiday for the ferry schedule. input = month*100+day
function IsHoliday(md) {
    if (md == 1231 || md == 1232 || md == 101) return true;
    if (md == memorialday || md == 703 || md == 704 || md == laborday || md == thanksgiving || md == 1224 || md == 1225) return true;
    return false;
}
/////////////////////////////////////////////////////////////////////////////////////////
// initilize all date variables.  
// entry: dateincr = 0 for today, 1 to go to last date + 1 (i.e. increment date by 1 and reset time).
//          mm/dd/yyyy for an arbitrary date
// sets the date globals above
function InitializeDates(dateincr) {
    if (dateincr == 0) Gd = new Date();
    else if (dateincr == 1) {
        Gd.setDate(Gd.getDate() + 1); // bump by 1
        Gd.setHours(0); Gd.setMinutes(0); Gd.setSeconds(0);
    } else {
        Gd = new Date(dateincr);
    }
    gTimeStampms = Gd.getTime(); // milisec since 1970
    gDayofWeek = Gd.getDay();  // day of week in 0-6
    gLetterofWeek = "0123456".charAt(gDayofWeek); // letter for day of week
    gTimehh = Gd.getHours();
    gTimemm = Gd.getMinutes();
    gTimehhmm = gTimehh * 100 + gTimemm;  // hhmm in 24 hour format
    gMonth = Gd.getMonth() + 1;  // month 1-12. IMPORTANT: note starts with 1
    gDayofMonth = Gd.getDate(); // day of month 1-31
    gMonthDay = gMonth * 100 + gDayofMonth;
    gYear = Gd.getFullYear();
    gYYmmdd = gMonthDay + (gYear-2000)*10000; // yymmdd
    gWeekofMonth = Math.floor((gDayofMonth - 1) / 7) + 1;  // nth occurance of day within month: 1,2,3,4,5
    // build holidays once only
    if (dateincr == 0 && laborday == 0) BuildHoliday(gYear);
    // compute holidays
    holiday = IsHoliday(gMonthDay);
}

/////////////////////////////////////////////////////////////////////////////////////
//  IncrementDate - returns date in YYmmdd format, incremented by dateincr
//  entry   dateincr = increment in days against current gdate values
//  exit    returns yymmdd  e.g. 180828
function IncrementDate(dateincr) {
    var dd = gDayofMonth + dateincr;
    var mm = gMonth;
    var yy = gYear;
    if (dd > gDaysInMonth[gMonth]) {  // if month overflow
        mm = mm + 1;
        dd = dd - gDaysInMonth[gMonth];
        if (mm == 13) {  // if next year
            mm = 1; yy = yy + 1;
        }
    }
    return (mm*100) + dd + (yy - 2000) * 10000; // yymmdd
}

//////////////////////////////////////////////////////////////////////////////////////
//  BuildHolidays - calculate laborday, memorial day, thanksgiving.  rev 1/5/18.
//  entry: year = year.   
//  exit: sets laborday, memorialday, thansgiving to mmdd.
function BuildHoliday(year) {
    // laborday // first monday in sept.  we need to compute this dyanmically
    var dlabordate, dlabor;
    dlabordate = new Date(year, 8, 1); // earlies possible date
    dlabor = dlabordate.getDay();
    if (dlabor > 1) laborday = 909 - dlabor;  // monday = 1... Sat=6
    else if (dlabor == 0) laborday = 902;  // monday = 1... Sat=6
    else laborday = 901;
    // memorial day last monday in may
    var dmemdate, memdate;
    dmemdate = new Date(year, 4, 25); // earliest possible date memorial day
    memday = dmemdate.getDay();
    if (memday > 1) memorialday = 525 + 8 - memday;  // monday = 1... Sat=6
    else if (memday == 0) memorialday = 526;  // monday = 1... Sat=6
    else memorialday = 525;
    // thanksgiving calculation.  Fixed on 01/05/18.
    // thanksgiving = 11/22 – 11/28
    var dthanksdate, dthanks;
    dthanksdate = new Date(year, 10, 1);// 1st day of nov
    dowthanks = dthanksdate.getDay(); // 0 – 6, thur=4
    if (dowthanks <= 4) thanksgiving = 1126 - dowthanks;//  4 = 22,3=23,2=24,1=25,0=26
    else thanksgiving = 1133 - dowthanks;;//  5 ->1128,  6->11/27
}

//////////////////////////////////////////////////////////////////////////////////
// tests for correct calculation of holidays.  Only used for debugging.
function BuildHolidayTest() {
    var testy = [2018, 2019, 2020, 2021, 2022, 2023,2024];
    var testm = [528, 527,525,531,530,529,527];
    var testl = [903, 902,907,906,905,904,902];
    var testt = [1122, 1128, 1126, 1125,1124,1123,1128];
    var i;
    for (i = 0; i < 4; i++) {
        BuildHoliday(testy[i]);
        if (memorialday != testm[i]) alert(String(testy[i]) + String(memorialday));
        if (laborday != testl[i]) alert(String(testy[i])+ String(laborday));
        if (thanksgiving != testt[i]) alert(String(testy[i])+ String(thanksgiving));
    }
    alert("test done");
}

///////////////////////////////////////////////////////////////////////////////////////
// GetDayofWeek - returns 0-6 for an arbitrary date in mmdd or yymmdd format. 
//  entry   mmdd = mmdd (assumes gYear) or yymmdd
function GetDayofWeek(mmdd) {
    var mmdd = Number(mmdd);
    var yyyy = gYear;
    if (mmdd > 9999) {
        yyyy = Math.floor(mmdd / 10000) + 2000; // extract year
        mmdd = mmdd % 10000;
    }
    var d = new Date(yyyy, Math.floor(mmdd / 100) - 1, mmdd % 100);
    return d.getDay();
}
///////////////////////////////////////////////////////////////////////////////////////
// GetWeekofYear - returns week of year  
//  entry   mmdd = mmdd (assumes gYear) or yymmdd (180122)
//  exit    week as 0 - 51;
function GetWeekofYear(mmdd) {
    var mmdd = Number(mmdd);
    var yyyy = gYear;
    if (mmdd > 9999) {
        yyyy = Math.floor(mmdd / 10000) + 2000; // extract year
        mmdd = mmdd % 10000;
    }
    var januaryFirst = new Date(yyyy, 0, 1,0,0,0,0);
    var thedate = new Date(yyyy, Math.floor(mmdd / 100) - 1, mmdd % 100,0,0,0,0);
    return Math.floor((Math.ceil((thedate - januaryFirst) / 86400000) + januaryFirst.getDay()) / 7);
}

////////////////////////////////////////////////////////////////////////////////////////////
// DateDiff - return difference in days between 2 dates in our funky mmdd format (0101 - 1231)
//  DateDiff(newer, older)  e.g. DateDiff(0122, 0102) = 20.  Handles rollover for a single year only.
//  Entry   mmdd1 = Newer month/day (Bigger), mmdd2 = Older month/day (Smaller)
var gDayspermonth = [0, 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365]; // cumulative days in year
function DateDiff(mmdd1, mmdd2) {
    if (mmdd1 == mmdd2) return 0;
    var m1 = Math.floor(mmdd1 / 100);
    var m2 = Math.floor(mmdd2 / 100);
    var d1 = mmdd1 % 100;
    var d2 = mmdd2 % 100;
    var r = gDayspermonth[m1] + d1 - gDayspermonth[m2] - d2;
    if (r < 0) r += 365;
    return r;
}

/////////////////////////////////////////////////////////////////////////////////////////
// GetTimeMS - get time stamp in MS since 1970
function GetTimeMS() {
    var d = new Date();
    return d.getTime(); // milisec since 1970
}



/////////////////////////////////////////////////////////////////////////////////////////
//  InList check for the argument in the list
//  entry   a = value
//          a1, a2, ... = values to test for
//  returns true if a = a1 or a2 or a3, ...; e.g. InList(3,0,1,2,3,4) returns true because 3 is in the list
function InList(a) {
    var i;
    for (i=1; i < arguments.length; i++) { if (arguments[0] == arguments[i]) return true; }
    return false;
}

/////////////////////////////////////////////////////////////////////////////////////////
// format ferry time for display. Formats time as: 
//      hh: mm am  OR hh: mm pm
//  ft = time in hhmm 24 hour form. 
//  returns string of time in 12 hour form.
function FormatTime(ft) {
    var ampm;
    if (ft < 1199) ampm = " am";
    else ampm = " pm";
    if (ft < 100) return "12:" + +Leading0(ft % 100) + ampm;
    else if (ft < 1299) return Leading0(Math.floor(ft / 100)) + ":" + Leading0(ft % 100) + ampm;
    else return Leading0(Math.floor(ft / 100) - 12) + ":" + Leading0(ft % 100) + ampm;
}
////////////////////////////////////////////////////////////////////////////////////////////
//  ShortTime - formats time as 
//      hh:mma OR hh:mmp OR hh:mm if noampm is specified.
//  Entry   ft = time as hhmm (integer)
//          noampm = omit parameter to return ampm suffix. 
//                   specify = 1 to omit ampm suffix.value does not matter
function ShortTime(ft, noampm) {
    var ampm = "";
    if (arguments.length == 1) {
        if (ft < 1199) ampm = "a";
        else ampm = "p";
    }
    if (ft < 100) return "12:" + Leading0(ft % 100) + ampm;
    else if (ft < 1299) return (Math.floor(ft / 100)) + ":" + Leading0(ft % 100) + ampm;
    else return (Math.floor(ft / 100) - 12) + ":" + Leading0(ft % 100) + ampm;
}
////////////////////////////////////////////////////////////////////////////////////////////
//  VeryShortTime - shortest possible time. Does not display minutes if minutes=0.
//      Formats time as ha  or  hp  if no minutes.  else h:mma  or h:mmp
//  like ShortTime but does not return minutes if not needed
//  so it returns 1p, where ShortTime returns 1:00p;
function VeryShortTime(ft) {
    //if (ft == 1200) return "noon";
    if ((ft % 100) == 0) {
        if (ft == 0) return "12a";
        var h = (Math.floor(ft / 100));
        if (ft < 1199) return h + "a";
        else if (ft < 1299) return h + "p";
        else return (h - 12) + "p";
    }
    else return ShortTime(ft);
}

///////////////////////////////////////////////////////////////////////////////////////////
// timediff - returns formatted time difference between 2 times 
// time1 is assumed to be now and time2 in the future.  So the diff is time2 - time1. 
//  hhmm1 = hours*100 + min; hhmm2 = hours*100 + min
//  returns string: hh:mm which is hhmm2 = hhmm1
function timeDiff(hhmm1, hhmm2) {
    var diffm;
    diffm = RawTimeDiff(hhmm1, hhmm2);
    return Math.floor(diffm / 60) + ":" + Leading0(diffm % 60);
}
////////////////////////////////////////////////////////////////////////////////////
// timeDiffhm - returns formatted time difference as nnh nnm, e.g. 1h 3m
// time1 is assumed to be now and time2 in the future.  So the diff is time2 - time1. 
//  hhmm1 = hours*100 + min; hhmm2 = hours*100 + min
//  returns string: nnhnnm
function timeDiffhm(hhmm1, hhmm2) {
    var diffm;
    diffm = RawTimeDiff(hhmm1, hhmm2);
    if (diffm < 60) return diffm + "m";
    if ((diffm % 60) == 0) return Math.floor(diffm / 60) + "h";
    return Math.floor(diffm / 60) + "h" + (diffm % 60) + "m";
}
////////////////////////////////////////////////////////////////////////////////////
// RawTimeDiff returns the time difference in minutes; hhmm2 - hhmm1
function RawTimeDiff(hhmm1, hhmm2) {
    var tm, ftm;
    tm = (Math.floor(hhmm1 / 100) * 60) + (hhmm1 % 100); // time in min
    ftm = (Math.floor(hhmm2 / 100) * 60) + (hhmm2 % 100);
    if (ftm < tm) ftm = ftm + 24 * 60;
    return ftm - tm; // diff in minutes
}
////////////////////////////////////////////////////////////////////////////////////
// timeDiffTTS returns the time difference for speech as 'nn hours nn minutes' or 'hh hours' or 'nn minutes'; 
//  Entry: rtd = time difference in minutes
//  Exit: returns text string ready for speech
function timeDiffTTS(diffm) {
    if (diffm < 60) return diffm + " minutes ";
    if ((diffm % 60) == 0) return Math.floor(diffm / 60) + " hours ";
    return Math.floor(diffm / 60) + " hours " + (diffm % 60) + " minutes";
}

//////////////////////////////////////   UTILITY ////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////
// clear table. Deletes all rows but the first.
function clearTable(table) {
    // clear table
    while (table.rows.length > 1) {
        table.deleteRow(-1);
    }
}


//////////////////////////////////////////////////////////////////////////////////////
// Pad with leading zero
function Leading0(num) {
    if (num >= 10) return num;
    else return "0" + Number(num);
}

////////////////////////////////////// Geo Location ////////////////////////////////////
//  GeoLocation
function getGeoLocation() {
    if ((gTimeStampms - gLocationTime) < 30000) return; // update every 30 sec at most
    gLocationTime = gTimeStampms;
    navigator.geolocation.getCurrentPosition
    (onGeoSuccess, onGeoError, { enableHighAccuracy: true, timeout: 5000});
}
// Success callback.
//  Exit: sets gLatitude, gLongitude, 
//        gLocationOnAI = 0 for Steilacoom, 1 for AI.  Saved as storage
//        Redraws ferry times if location has changed
var onGeoSuccess = function (position) {
    gLatitude = position.coords.latitude;  // NS
    gLongitude = position.coords.longitude; // EW
    edgeW = -122.7464; edgeE = -122.6613; // Anderson Island Bounding Box from Google map
    edgeN = 47.1899; edgeS = 47.1228;
    var locationOnAI = 0; // steilacoom
    if ((gLongitude > edgeW) && (gLongitude < edgeE) && (gLatitude < edgeN) && (gLatitude > edgeS)) locationOnAI = 1;
    UpdateLocation(locationOnAI);
}

// Error callback. Sets gLocationOnAI = 99.
var onGeoError = function (error) {
    gLatitude = 0.0;
    gLongitude = 0.0;
    UpdateLocation(99);
}

//  UpdateLocation - updates gLocationOnAI and rewrites page 1 ferry times
function UpdateLocation(locationOnAI) {
    if (isPhoneGap() && (locationOnAI != gLocationOnAI)) { // if location changed
        gLocationOnAI = locationOnAI;
        localStorage.setItem("glocationonai", gLocationOnAI.toFixed(0));
        WriteNextFerryTimes(); // redraw schedule to highlight proper row
    }
}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// global variables (gXxxxxx). Since this entire app is one page and one program, these variables hold for the entire execution.
var gUpdateCounter = 0; // counter of timer updates
var gLastUpdatems = 0; // last update time in MS
var gForceCacheReload; // true to force a cache reload
var gForceTideReload;  // true to force tide reload
var gMyTimer; // timer number
var gAppStartedTime; // time app started in sec
var gAppStartedDate;
var gDisplayPage; // name of page being displayed
var gTableToClear; // name of table to clear
var gMenuOpen = false; // true if menu is open

var gDateSunrise; // sunrise date object
var gDateSunset; // sunset date object
var gDaysInMonth = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

// Ferry run times and flags. 
//  These values are valid as of 9/2016 - but are immediately overridden by data in dailycache.txt:
//  FERRYTS,FERRYTA,FERRYTK,FERRYTS2,FERRYTA2,FERRYTK2   (read in by ParseDailyCache)Heirarchy is:
//  1. '*' overrides everything and means always.
//  2. 0-6 = day of week that run is valid on.
//  3. Starts with a '(': it is the special case rules run with 'eval'

var ferrytimeS = [445,"((gDayofWeek>0)&&(gDayofWeek<6)&&!InList(gMonthDay,1225,101,704,thanksgiving))",545,"123456",645,"*",800,"*",900,"*",1000,"((gDayofWeek!=3)||!InList(gWeekofMonth,1,3))",1200,"*",1420,"*",1520,"*",1620,"*",1730,"*",1840,"*",2040,"*",2200,"( (gDayofWeek==6)|| ((gDayofWeek==5)&&!((gMonthDay>=701)&&(gMonthDay<=laborday))) ||InList(gMonthDay,1231,101,memorialday,703,704,laborday,thanksgiving,1224,1225))",2300,"((gDayofWeek==5)&&(gMonthDay>=701)&&(gMonthDay<=laborday))"];
var ferrytimeA = [515,"((gDayofWeek>0)&&(gDayofWeek<6)&&!InList(gMonthDay,1225,101,704,thanksgiving))",615,"123456",730,"*",830,"*",930,"*",1030,"((gDayofWeek!=3)||!InList(gWeekofMonth,1,3))",1230,"*",1450,"*",1550,"*",1650,"*",1800,"*",1910,"*",2110,"*",2230,"( (gDayofWeek==6)|| ((gDayofWeek==5)&&!((gMonthDay>=701)&&(gMonthDay<=laborday))) ||InList(gMonthDay,1231,101,memorialday,703,704,laborday,thanksgiving,1224,1225))",2330,"((gDayofWeek==5)&&(gMonthDay>=701)&&(gMonthDay<=laborday))"];
var ferrytimeK = [0,"",0,"",655,"*",0,"",0,"",1010,"((gDayofWeek==2)&&InList(gWeekofMonth,1,3))",1255,"*",0,"",0,"",0,"",0,"",1935,"*",0,"",2250,"( (gDayofWeek==6)|| ((gDayofWeek==5)&&!((gMonthDay>=701)&&(gMonthDay<=laborday))) ||InList(gMonthDay,1231,101,memorialday,703,704,laborday,thanksgiving,1224,1225))",2350,"((gDayofWeek==5)&&(gMonthDay>=701)&&(gMonthDay<=laborday))"];
var ferrytimeS2 = [445,"((gDayofWeek>0)&&(gDayofWeek<6)&&!InList(gMonthDay,1225,101,704,thanksgiving))",545,"123456",645,"*",800,"*",900,"*",1000,"((gDayofWeek!=3)||!InList(gWeekofMonth,1,3))",1200,"*",1230,50,1350,50,1420,"*",1450,50,1520,"*",1550,50,1620,"*",1650,50,1730,"*",1800,50,1840,"*",2040,"*",2200,"( (gDayofWeek==6)|| ((gDayofWeek==5)&&!((gMonthDay>=701)&&(gMonthDay<=laborday))) ||InList(gMonthDay,1231,101,memorialday,703,704,laborday,thanksgiving,1224,1225))",2300,"((gDayofWeek==5)&&(gMonthDay>=701)&&(gMonthDay<=laborday))"];
var ferrytimeA2 = [515,"((gDayofWeek>0)&&(gDayofWeek<6)&&!InList(gMonthDay,1225,101,704,thanksgiving))",615,"123456",730,"*",830,"*",930,"*",1030,"((gDayofWeek!=3)||!InList(gWeekofMonth,1,3))",1230,"*",1300,50,1420,50,1450,"*",1520,50,1550,"*",1620,50,1650,"*",1730,50,1800,"*",1840,50,1910,"*",2110,"*",2230,"( (gDayofWeek==6)|| ((gDayofWeek==5)&&!((gMonthDay>=701)&&(gMonthDay<=laborday))) ||InList(gMonthDay,1231,101,memorialday,703,704,laborday,thanksgiving,1224,1225))",2330,"((gDayofWeek==5)&&(gMonthDay>=701)&&(gMonthDay<=laborday))"];
var ferrytimeK2 = [0,"",0,"",655,"*",0,"",0,"",1010,"((gDayofWeek==2)&&InList(gWeekofMonth,1,3))",1255,"*",0,"",0,"",0,"",0,"",0,"",0,"",0,"",0,"",0,"",0,"",1935,"*",0,"",2250,"( (gDayofWeek==6)|| ((gDayofWeek==5)&&!((gMonthDay>=701)&&(gMonthDay<=laborday))) ||InList(gMonthDay,1231,101,memorialday,703,704,laborday,thanksgiving,1224,1225))",2350,"((gDayofWeek==5)&&(gMonthDay>=701)&&(gMonthDay<=laborday))"];

var gFerryDate2 = 0;  // cutover time to ferrytimex2

// OpenHours Array object. Array of openhours for the store hours.  This array is loaded by a JSON.parse
//      of the OpenHoursJSON string in 'dailycache'
//  Properties: Name, Phone, Desc, Href: http..., Addr, Map: http for google maps, 
//      SC[ {From: mmdd, To: mmdd, H[sun open hhmm, sun close hhmm, mo, mc, to, tc, wo, wc, to, tc, fo, fc, so, sc], H2[same as h1] }... ],
//      Closed [mmdd, ...]
//      H and H2 are arrays of open and close times as hhmm, indexed by day of week*2.  H2 is optional.
//      
var gShowAllOpenHours = false; // true to show all open hours
var OpenHours = [];
OpenHours = [{  // single preload for testing and if there is no connectivity
    Name: 'Store', Phone: '884-4001', Desc: 'Groceries, Deli, Hardware, Gas',
    Href: 'https://www.co.pierce.wa.us/index.aspx?NID=1541',
    Addr: '10202 Eckenstam Johnson Rd',
    Map: 'https://www.google.com/maps?q=Anderson+Island+General+Store,+10202+Eckenstam+Johnson+Rd,+Anderson+Island,+WA',
    Sc: [{ From: 101, To: 1231, H: [1000, 1800, 700, 2000, 700, 2000, 700, 2000, 700, 2000, 700, 2100, 800, 2100] }],
    Closed: [1225]
}];

/////////////////////////////////////////////////////////////////////////////////////////
// load web pages
function ShowFerry() {
    MarkPage("x");
    var link = GetLink("ferrypagelink", "http://www.co.pierce.wa.us/index.aspx?NID=1793");
    window.open("http://www.co.pierce.wa.us/index.aspx?NID=1793", "_system");
}

function ShowMap() {
    MarkPage("g");
    var link = GetLink("googlemaplink", "https://www.google.com/maps/place/Anderson+Island,+Washington+98303/@47.1559337,-122.7429194,13z/data=!3m1!4b1!4m2!3m1!1s0x5491a7e3857e1e6f:0x9800502f110113b4");
    window.open(link, "_blank");
}

function ShowBurnBan() {
    MarkPage("u");
    var link = GetLink("burnbanlink", "http://wc.pscleanair.org/burnban411/");  // default
    window.open(link, "_blank");
}

function ShowTannerOutage() {
    MarkPage("r");
    var link = GetLink("tanneroutagelink", "http://www.tannerelectric.coop/andersonisland");  // default
    window.open(link, "_blank");
}

function ShowParks() {
    MarkPage("p");
    var link = GetLink("parkslink", 'http://www.anderson-island.org/parks/parks.html');
    //window.open(link, '_blank', 'EnableViewPortScale=yes');
    window.open(link, '_blank'); // no viewport scaling

}

function ShowNews() {
    MarkPage("n");
    var link = GetLink("newslink", 'http://www.anderson-island.org/news.html');
    window.open(link, "_blank");
}

// open the correct web page for an upgrade. If its web, force a page reload.
function UpdateApp() {
    MarkPage("y");
    if (isPhoneGap()) {
        if (isAndroid()) {
            var link = GetLink("googleplaylink", 'https://play.google.com/store/apps/details?id=org.anderson_island.andersonislandassistant');
            window.open(link, '_system');
        } else {
            var link = GetLink("applestorelink", 'https://itunes.apple.com/us/app/anderson-island-assistant/id1092687892?ls=1&mt=8');
            window.open(link, '_system');
        }
    } else {
        window.location.reload(true);
        //window.open('http://www.anderson-island.org/?' + Date.now(), '_parent');
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////
// Notify.  Normal situation is notification is on, and the 'NotifyOff' flag is not there.
//          If notification is off, then 'NotifyOff' is present in local storage.
//  Entry:  notifytog.checked = true or false
function NotifyToggle() {
    if (isPhoneGap()) {
        var n = MenuIfChecked("notifytog");//1 = checked, 0 = unchecked
        if (n == 0) {  // if Off
            localStorage.setItem('notifyoff', 'OFF');
            window.plugins.OneSignal.setSubscription(false);
            break;
        } else {  // if On
            localStorage.removeItem("notifyoff"); // remove the notify off flag
            window.plugins.OneSignal.setSubscription(true);
            break;
        }
    }
}



//////////////////////////////////////////////////////////////////////////////////////////////
//  Ferry Schedule Main Page Settings

//  FerryShowCDToggle Countdown InOn/Off set the gFerryShowIn switch to control the countdown to arrival time
//  Entry: document.getElementById("ferrycdtog").checked
//  Exit: Sets gFerryShowIn (1 or 0) and local storage "ferryshowin" 
//
function FerryShowCDToggle() {
    gFerryShowIn = MenuIfChecked("ferrycdtog");
    localStorage.setItem("ferryshowin", gFerryShowIn.toFixed(0));
    WriteNextFerryTimes();
}


//  FerryShow3On/Off set the gFerry3 switch to control the countdown to arrival time
//  Entry: ferry3ttog.checked = true of false
//  Exit: sets gFerryShow3 (1 or 0) and local storage "ferryshow3 ("1" or "0")
//
function FerryShow3Toggle() {
    gFerryShow3 = MenuIfChecked("ferry3ttog");
    localStorage.setItem("ferryshow3", gFerryShow3.toFixed(0));
    WriteNextFerryTimes();
}

//  FerryHighlightOn/Off set the gFerryHighlight switch to control the highlighting of the shedule rows based on location (AI or Steilacoom)
//  Entry: ferryhltog.checked = true or false
//  Exit: sets gFerryHighlight (1 or 0) and local storage "ferryhighlight ("1" or "0")
//
function FerryHighlightToggle() {
    gFerryHighlight = MenuIfChecked("ferryhltog");
    localStorage.setItem("ferryhighlight", gFerryHighlight.toFixed(0));
    if (gFerryHighlight == 1) {
        gLocationTime = 0;
        if (isPhoneGap()) getGeoLocation();
    }
    WriteNextFerryTimes();
}

////////////////////////////////////////////////////////////////////////
// MenuSetup - setup the initial menu settings based on g values. 
//  Assumes all menu items start out as OFF
function MenuSetup() {
    // ferry countdown
    if (gFerryShowIn == 1) document.getElementById("ferrycdtog").checked = true;
    // ferry highlight
    if (gFerryHighlight == 1) document.getElementById("ferryhltog").checked = true;
    // 2/3 times/row
    if (gFerryShow3 == 1) document.getElementById("ferry3ttog").checked = true;
    // notify.   set the notify switch. hide it for web.
    if (isPhoneGap()) {
        if (LSget('notifyoff') != "OFF") document.getElementById("notifytog").checked = true;
    } else {
        Hide("menunotify");
        Hide("menuspeech");
    }
    // icons
    if (gIconSwitch == 1) document.getElementById("showiconstog").checked = true;
    if (TXTS.OnOff == 1) document.getElementById("ttstog").checked = true; // speech
    if (BIGTX.OnOff == 1) document.getElementById("bigtog").checked = true; // big text

}

function MenuCheck(id, truefalse) {
    document.getElementById(id).checked = truefalse;
}
//  MenuIfChecked (id) returns 1 if id is checked, 0 if id if not checked
function MenuIfChecked(id) {
    if (document.getElementById(id).checked == true) return 1;
    return 0;
}

//////////////////////////////////////////////////////////////////////////////////////////////
// Determine whether the file loaded from PhoneGap or the web
//  exit    true if phonegap, otherwise undefined. So test for true only.
function isPhoneGap() {
    //var test = /^file:\/{3}[^\/]/i.test(window.location.href)
    //&& /ios|iphone|ipod|ipad|android/i.test(navigator.userAgent);
    return gisPhoneGap;
}
function isAndroid() {
    //return ((navigator.userAgent.toLowerCase().indexOf('chrome') > -1) ||
    //(navigator.userAgent.toLowerCase().indexOf('android') > -1));
    return gisAndroid;
}
//isMobile - returns true if a Mobile browser (even if not PhoneGap), else false.  INDEPENDENT OF PHONEGAP.
function isMobile() {
    return gisMobile;
    //return /ios|iphone|ipod|ipad|android/i.test(navigator.userAgent);
}

//isMobile - Initialize the switches gisMobile, gisPhoneGap, gisAndroid
function initializeMobile(){
    gisMobile = /ios|iphone|ipod|ipad|android/i.test(navigator.userAgent);
    gisPhoneGap = /^file:\/{3}[^\/]/i.test(window.location.href)
    && /ios|iphone|ipod|ipad|android/i.test(navigator.userAgent);
    gisAndroid = ((navigator.userAgent.toLowerCase().indexOf('chrome') > -1) ||
    (navigator.userAgent.toLowerCase().indexOf('android') > -1));
}


//////////////////////////////////////////////////////////////////////////////////
//  DeviceInfo - returns device info as a string: 
//  PG=phonegap, MW=mobile web, DW=desktop web, And=Android, IOS=IOS
function DeviceInfo() {
    var kind;
    if (isPhoneGap()) kind = "PG-";
    else if (isMobile()) kind = "MW-";
    else kind = "DW-";
    if (navigator.userAgent.toLowerCase().indexOf('edge') > -1) kind += "MSEdge";
    else if (navigator.userAgent.toLowerCase().indexOf('.net') > -1) kind += "MSIE";
    else if (isAndroid()) kind += "And";
    else kind += "IOS";
    return kind;
}
/////////////////////////////////////////////////////////////////////////////////////////////
// FixURL = make URL fully qualified if a phonegap app and add "?<seconds>" to turn off cache
//  entry   url = the url
//  eixt    returns url
function FixURL(url) {
    if (url.indexOf("?") < 0) url += "?" + Date.now(); // turn off cache if no '?'
    if (isPhoneGap() == false) return url;
    if (url.indexOf("//") > -1) return url;  // if it already is qualified
    return 'http://anderson-island.org/' + url;
}

//////////////////////////////////////////////////////////////////////////////////////////
//  IsEmpty - returns true if the string is empty or null or undefined
//  Entry   string
//  Exit    returns true if empty or null, else false
function IsEmpty(string) {
    if (string == null) return true;
    if (string == "") return true;
    return false;
}

/////////////////////////////////////////////////////////////////////////////////////////////
// GetLink - returns the contents of the storage item, or the default if it is null
//  Entry   localstoragename = name of link in local storage
//          defaultlink = link to return if localstoragename doesn't exist
//  Exit    returns the contents of the storage item, or the default if it is null
function GetLink(localstoragename, defaultlink) {
    var link = localStorage.getItem(localstoragename);
    if (link == null || link == "") return defaultlink;
    return link;
}

///////////////////////////////////////////////////////////////////////
//  FixiPhoneHeader() - fix the header for the iphone by making it higher and adding a br
function FixiPhoneHeader() {
    if (isAndroid()) return;
    if (!isPhoneGap()) return;
    document.getElementById("ipfix1").style.display = "block";
    document.getElementById("ipfix2").style.display = "block"; // extra row to allow for ios status line
}

/////////////////////////////////////////////////////////////////////////////////////////////
// UpdateAvailable - set message if update is available
// NOTE: version MUST be 4 chars in the form n.nn  e.g. 1.07;  Version comparison is a string comparison.
function UpdateAvailable() {
    if (!isPhoneGap()) return;
    if (isAndroid()) UpdateCheck("androidver", "androidapp");
    else UpdateCheck("iosver", "iphoneapp");
    return;
}
function UpdateCheck(ver, id) {
    var rver = LSget(ver);
    if (rver == "") return;
    if (rver > gVer.substr(0, 4)) {
        document.getElementById(id).innerHTML = "Update available. Tap here to upgrade.";
        Show(id);
        document.getElementById("topline").innerHTML = "";
    }
}


/////////////////////////////////////////////////////////////////////////////////////////////
// InstallAvailable -    point user to google play if a mobile browser that is NOT PhoneGap
function InstallAvailable() {
    if (!isPhoneGap() && isMobile()) {  // if not phonegap
        if (isAndroid()) { // if chrome for android
            Show("androidapp");
            document.getElementById("topline").innerHTML = "";
        } else {
            Show("iphoneapp");
            document.getElementById("topline").innerHTML = "";
        }
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
// DisplayAlertDetail() - show the alert detail
//  if user clicks CANCEL, the alert will be hidden until it changes
//  Exit:  alerthide set if user clicks cancel
function DisplayAlertDetail() {
    var r = confirm(localStorage.getItem('alerttext') + "\n" + localStorage.getItem('alertdetail')
        + "\n\nClick CANCEL to hide this Alert");
    if (r == false) {
        localStorage.setItem("alerthide", "t");
        DisplayAlertInfo();  // this will hide the alert
    }

}

//////////////////////////////////////////////////////////////////////////////////////
// CountPage - bump page counter in localStorage.
function CountPage(page) {
    var id = "C" + page;
    var c = localStorage.getItem(id);
    if (c == null) localStorage.setItem(id, "1");
    else localStorage.setItem(id, (Number(c) + 1).toFixed(0));
}

// MarkPage - add the page 1st letter to the "pagehits" string. 
//  limit m to 1 call.
function MarkPage(page) {
    var s = localStorage.getItem("pagehits");
    if (s == null) {
        localStorage.setItem("pagehits", page);
        return s;
    }
    if (s.length > 30) return;
    if (page == "m") {  // dont create a string of mmmmm
        if (s.substr(s.length - 1) == "m") return;
    }
    localStorage.setItem("pagehits", s + page);
}

/////////////////////////////////////////////////////////////////////////////
// Show(divid)  Hide(Divid)  Show or hide a div by setting the display style.
//  divid = id of div to show or hide
function Show(divid) {
    document.getElementById(divid).style.display = "block";
}
function Hide(divid) {
    document.getElementById(divid).style.display = "none";
}

///////////////////////////////////////////////////////////////////////////////
//  MarkOffline - mark the app offline
//      offline = true if offline, false if online
function MarkOffline(offline) {
    var ofl = "Offline. ";
    var tle = document.getElementById("topline");
    var topline = tle.innerHTML;
    if (offline) {
        Show("offlinemsg"); 
        if (topline.substr(0, 9) == ofl) return;
        tle.innerHTML = ofl + topline; // set it offline
    } else {  // not offline
        Hide("offlinemsg");
        if (topline.substr(0, 9) != ofl) return;
        tle.innerHTML = topline.substr(9); // remove offline prefix
    }
}

///////////////////////////////////////////////////////////////////////////
//  LSget - local storage get always returns string or the default. 
//      if no default passed, returns "". never returns null.
//  Entry: id = local storage item id
//         default = optional value to return if id is not present. If omitted, returns ""
function LSget(id, def) {
    var s = localStorage.getItem(id);
    if (s != null) return s;
    if (arguments.length == 2) return def;
    return "";
}

function LSappend(id, s) {
    localStorage.setItem(id, LSget(id) + s)
}

////////////////////////////////////////////////////////////////////////////////////////
//  Removetags - remove html tags. Replaces <br/> with a period.
//  Entry: str = string to fix.
//  Exit: returns the string
function RemoveTags(str) {
    str = str.replace("<br/>", ". ");
    return str.replace(/<\/?.+?>/ig, '');
}

////////////////////////////////////////////////////////////////////////////////////////////////
// DisplayAlertInfo()  - sets the alerttext and sets the alertdiv = display:block if there is an alert
//  NOTE: if 'alerthide' exists, the alert will NOT be displayed.
//  Also processes the burnban and tanner alerts.
function DisplayAlertInfo() {
    if (IsEmpty(localStorage.getItem("alerttext")) || localStorage.getItem("alerthide") != null) {
        Hide("alertdiv");
        //document.getElementById("alertdiv").setAttribute('style', 'display:none;');
    } else {
        document.getElementById("alerttext").innerHTML = localStorage.getItem("alerttext");
        Show("alertdiv");
        //document.getElementById("alertdiv").setAttribute('style', 'display:bock;');
    }

    // burnban status or alert
    var s = localStorage.getItem("burnbanalert");
    if (IsEmpty(s)) s = "Tap for burn ban status.";
    document.getElementById("burnbanalert").innerHTML = s;

    // tanner status or alert
    var s = localStorage.getItem("tanneroutagealert");
    if (IsEmpty(s)) s = "Tap for outage status.";
    document.getElementById("tanneroutagealert").innerHTML = s;
    var tt = document.getElementById("tannertitle").innerHTML; // tanner title
    if (s.indexOf("No Outages") > 0) document.getElementById("tannertitle").innerHTML = tt.replace("flash_off", "flash_on");  // if no outage
    else document.getElementById("tannertitle").innerHTML = tt.replace("flash_on", "flash_off"); // if an outage
}



/////////////////////////////////////////////////////////////////////////////////////////////////
// getAlertInfo - without jQuery- gets the alert info from the server every minute via php and save it in 
//      alerttext and alertdetail and ...
//  Entry   'alerthide' = true to hide the alert in 'alerttext'
//  Exit    'alerttext', 'alertdetail' set.  'alerthide' cleared if the alert has changed.
//          'burnbanalert' = burn ban alert info
//          'tanneralert' = tanner alert info
function getAlertInfo() {
    //var alerttimeout = 480; // alert timeout in sec 8 minutes
    var alerttimeout = 60000; // alert timeout in ms. 1 min  as of 4/8/17, v1.11.
    //var timestamp = Date.now() / 1000; // time in sec
    if ((Date.now() - gAlertTime) < alerttimeout) return; // gets alert async every min.
    var myurl = FixURL('getalerts.php');
    // ajax request without jquery
    MarkOffline(false);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) HandleAlertReply(xhttp.responseText);
        if (xhttp.readyState == 4 && xhttp.status == 0) MarkOffline(true); // this one works when net is disconnected
    }
    try{
        xhttp.open("GET", myurl, true);
        xhttp.timeout = 12000;  // 12 second timeout; this doesn't seem to work
        xhttp.ontimeout = function () { MarkOffline(true); }  // after 12 seconds, show the offline msg
        xhttp.send();
    }
    catch (e) {
        MarkOffline(true);
    }
}

function HandleAlertReply(r) {
    MarkOffline(false);
    gAlertTime = Date.now(); // time in ms
    //localStorage.setItem("alerttime", timestamp); // save the cache time so we don't keep asking forever
    gAlertCounter++; // count the alert reply
    var s = parseCacheOptional(r, "", "FERRY", "FERRYEND");
    SaveFerryAlert(s);
    parseCacheRemove(r, 'burnbanalert', "BURNBAN", "BURNBANEND");
    parseCacheRemove(r, 'tanneroutagealert', "TANNER", "TANNEREND");
    DisplayAlertInfo();
    WriteNextFerryTimes(); // display 'DELAYED' in ferry times if necessary.
}

///////////////////////////////////////////////////////////////////////////////////
//  SaveFerryAlert 
//  entry r = the alert text, "" if none
function SaveFerryAlert(r) {
    if (r == "") {  // if the alert is gone, clear it
        localStorage.setItem("alerttext", "");
        localStorage.setItem("alertdetail", "");
        localStorage.removeItem("alerthide");  // turn off hide
    } else {  // if there is an alert, save it
        r = r + "\n";
        var i = r.indexOf("\n");
        var atext = r.substr(0, i);
        if (atext != localStorage.getItem("alerttext")) {  // if alert changed
            localStorage.setItem("alerttext", atext);
            localStorage.setItem("alertdetail", r.substr(i));
            localStorage.removeItem("alerthide");  // turn off hide because alert changed
        }
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////
//  side menu
/* Set the width of the side navigation to 150px */
function OpenMenu() {
    // if we are not on main page, the click is really a 'BACK' click
    if (gMenuOpen) {
        CloseMenu();
        return;
    }
    if (gDisplayPage != "mainpage") {
        ShowMainPage();
        return;
    }
    document.getElementById("sidemenu").style.width = "85%";
    SetPageHeader("Settings");
    //document.getElementByID("mainpage").onclick = function () { CloseMenu(); }; /////////////
    gMenuOpen = true;
}

/* Set the width of the side navigation to 0 */
function CloseMenu() {
    document.getElementById("sidemenu").style.width = "0";
    SetPageHeader(" Anderson Island Assistant");
    document.getElementById("h1menu").innerHTML = "<i class='material-icons'>menu</i>&nbsp";  // menu symbol in header
    gMenuOpen = false;
}
// Open Ferry Menu
function OpenFerryMenu() {
    ShowMainPage();
    document.getElementById("sidemenu").style.width = "85%";
    gMenuOpen = true;
}


////////////////////////////////////////////////////////////////////////////////////////////////////
// StripDecimal - strips out the decimal part of a numeric text string
function StripDecimal(n) {
    var ns = String(n);
    var i = ns.indexOf(".");
    if (i < 0) return ns;
    if (i == 0) return "";
    ns = ns.substring(0, i); // strip it
    return ns;
}
////////////////////////////////////////////////////////////////////////////////////////////////////
//  DegToCompassPoints - converts degrees to compass points
function DegToCompassPoints(d) {
    var cp = ["N", "NE", "E", "SE", "S", "SW", "W", "NW", "N"];
    if (d == undefined) return "";
    return cp[Math.floor((Number(d) + 22.5) / 45)];
}
////////////////////////////////////////////////////////////////////////////////////////////////////
//  DegToCompassPointsTTS - converts degrees to talking compass points
function DegToCompassPointsTTS(d) {
    var cp = ["north", "north east", "east", "south east", "south", "south west", "west", "north west", "north"];
    if (d == undefined) return "";
    return cp[Math.floor((Number(d) + 22.5) / 45)];
}







///////////////////////////////////////////////////////////////////////////////////////////////
//  GetDailyCache - retrieves the daily cache into the local storage objects 
//  Load from server using ajax async request.
//  FERRYTIMESS,FERRYTIMESA,OPENHOURS,OPENHOURSEND,EMERGENCY,EMERGENCYEND
//function GetDailyCache() {
//     ajax async request
//    var myurl = FixURL("dailycache.txt"); 
//    $.ajax({
//        url: myurl, // for phone use fully qualified url http://anderson-island.org/
//        success: function (data) {
//}
function GetDailyCache() {
    // ajax async request
    ////var myurl = FixURL("dailycache.txt");

    var myurl = FixURL("getdailycache.php?VER=" + gVer + "&KIND=" + DeviceInfo() + "&N=" + localStorage.getItem("Cmain") + 
        "&P=" + LSget("pagehits").substr(0, 30));
    //var myurl = FixURL("getdailycachetest.php?VER=" + gVer + "&KIND=" + DeviceInfo() + "&N=" + localStorage.getItem("Cmain") +
    //   "&P=" + LSget("pagehits").substr(0, 30));

    // ajax request without jquery
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) HandleDailyCacheReply(xhttp.responseText);
    }
    xhttp.open("GET", myurl, true);
    xhttp.send();
}
function HandleDailyCacheReply(data) {
    InitializeDates(0);
    localStorage.setItem("Cmain", "0");  // clear page count
    localStorage.setItem("pagehits", "");
    ParseDailyCache(data);
    localStorage.setItem("dailycacheloaded", gMonthDay); // save event loaded date/time
    localStorage.setItem("dailycacheloadedtime", gTimehhmm); // save event loaded date/time
    localStorage.setItem("myver", gMyVer);  // save the app version
    // now update stuff on mainpage that uses daily cache data
    ShowOpenHours();
    WriteNextFerryTimes();
    DisplayLoadTimes();
}

////////////////////////////////////////////////////////////////////////////////////////
// ParseDailyCache - parses the daily cache into its parts in local storage
// data is demarkated by 
//  KEYWORD\n data   \n      OR
//  KEYWORD\n  data with embedded newlines \n KEYWORDEND

function ParseDailyCache(data) {
    var s;

    data = data.replace(/\r/g, ""); // remove all crs regular expression with global flag
    //var rows = data.split("\n"); //split into lines
    if (data.substring(0, 10) != "DAILYCACHE") {
        //alert("could not load updated values for ferry times and open hours");
        document.getElementById("reloadreason").innerHTML = "Could not load dailycache data.";
        return;
    }

    localStorage.setItem("dailycacheloaded", gMonthDay); // remember date time

    parseCache(data, "ferrytimess", "FERRYTS", "\n");
    parseCache(data, "ferrytimesa", "FERRYTA", "\n");
    parseCache(data, "ferrytimesk", "FERRYTK", "\n");

    parseCache(data, "emergency", "EMERGENCY", "EMERGENCYEND");
    parseCache(data, "links", "LINKS", "LINKSEND");
    parseCache(data, "openhoursjson", "OPENHOURSJSON", "OPENHOURSJSONEND");
    ParseOpenHours();
    //if (s != "") OpenHours = JSON.parse(s);  // parse it
    // new ferry schedule 
    parseCache(data, "ferrytimess2", "FERRYTS2", "\n");
    parseCache(data, "ferrytimesa2", "FERRYTA2", "\n");
    parseCache(data, "ferrytimesk2", "FERRYTK2", "\n");
    ParseFerryTimes();

    parseCache(data, "ferrydate2", "FERRYD2", "\n"); // cutover date to ferrytimes2 as 'mm/dd/yyyy'
    parseCacheRemove(data, "ferrymessage", "FERRYMESSAGE", "FERRYMESSAGEEND");
    s = parseCacheRemove(data, "message", "MOTD", "\n");  // message
    if (!IsEmpty(s)) document.getElementById("topline").innerHTML = s;
    parseCache(data, "androidver", "ANDROIDVER", "\n");
    parseCache(data, "iosver", "IOSVER", "\n");
    parseCache(data, "locations", "LOCATIONS", "LOCATIONSEND"); // locations for coming events

    // links for things that could change, like the ferry pictures, burnban, tanner
    parseCacheRemove(data, "ferrycams", "FERRYCAMS", "\n");   // ferry camera link steilacoom
    parseCacheRemove(data, "ferrycama", "FERRYCAMA", "\n");   // ferry camera link anderson
    parseCacheRemove(data, "burnbanlink", "BURNBANLINK", "\n");   // burn ban link 
    parseCacheRemove(data, "tanneroutagelink", "TANNEROUTAGELINK", "\n");   // tanner outage link
    parseCacheRemove(data, "tidedatalink", "TIDEDATALINK", "\n"); // tide data
    parseCacheRemove(data, "currentweatherlink", "CURRENTWEATHERLINK", "\n"); // weather data
    parseCacheRemove(data, "weatherforecastlink", "WEATHERFORECASTLINK", "\n"); // forecast data
    parseCacheRemove(data, "ferryschedulelink", "FERRYSCHEDULELINK", "\n"); // ferry schedule
    parseCacheRemove(data, "ferrylocationlink", "FERRYLOCATIONLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "androidpackageticketlink", "ANDROIDPAKAGETICKETLINK", "\n"); // ferry ticket android package
    parseCacheRemove(data, "iosinternalticketlink", "IOSINTERNALTICKETLINK", "\n"); // ferry ticket ios internal URI
    parseCacheRemove(data, "pierceferryticketlink", "PIERCEFERRYTICKETLINK", "\n"); // ferry ticket ios internal URI
    parseCacheRemove(data, "googleplayticketlink", "GOOGLEPLAYTICKETLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "googleplaylink", "GOOGLEPLAYLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "iosticketlink", "IOSTICKETLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "ferrypagelink", "FERRYPAGELINK", "\n"); // ferry schedule
    parseCacheRemove(data, "googlemaplink", "GOOGLEMAPLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "applestorelink", "APPLESTORELINK", "\n"); // ferry schedule
    parseCacheRemove(data, "parkslink", "PARKSLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "newslink", "NEWSLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "customtidelink", "CUSTOMTIDELINK", "\n"); // ferry schedule
    parseCacheRemove(data, "noaalink", "NOAALINK", "\n"); // CUSTOM TIDES schedule
    //parseCacheRemove(data, "maintablerows", "MAINTABLEROWS", "MAINTABLEEND");  // extra rows for main table
    parseCacheOptional(data, "moon", "MOON", "MOONEND");  // moon - added 8/18/18

    // coming events (added 6/6/16). from the file comingevents.txt, pulled by getdailycache.php
    // format: COMINGEVENTS ...events...ACTIVITIES...activities...COMINGEVENTSEND
    parseCache(data, "comingevents", "COMINGEVENTS", "ACTIVITIES");
    parseCache(data, "comingactivities", "ACTIVITIES", "COMINGEVENTSEND");
    localStorage.setItem("comingeventsloaded", gMonthDay); // save event loaded date/time
    FixDates("comingevents");
    FixDates("comingactivities");
    document.getElementById("nextevent").innerHTML = DisplayNextEvents(localStorage.getItem("comingevents"));
    document.getElementById("nextactivity").innerHTML = DisplayNextEvents(localStorage.getItem("comingactivities"));

    // tides (added 6/6/16);
    s = parseCache(data, "", "TIDES", "TIDESEND");
    try{
        var json = JSON.parse(s);
    } catch (err) {
        alert("ERROR: Can't decode tide data: " + err.message + "\n" + s);
    }
    localStorage.setItem("jsontides", JSON.stringify(json.response.periods)); // store the full json reponse structure
    localStorage.setItem("tidesloadedmmdd", gMonthDay);
    ShowNextTides();

    if (gReloadCachedDataButtonInProgress) {
        gReloadCachedDataButtonInProgress = false;
        alert("Data successfully reloaded.");
    }
}

/////////////////////////////
// parseCache - returns the string from the daily cache
//  data is in the form:  <startstr>\n data <endstr>
//  localstoragename = name of local storage item, "" to not store it
//  startstr = starting string, endstr = ending string. can be "\n"
//  exit    returns the string. "" if no string.
// Modified 5/15/18 to always check for <startstr>\n and return error if not found.
function parseCache(data, localstoragename, startstr, endstr) {
    //var s = data.indexOf(startstr);
    var s = data.indexOf(startstr + "\n");  //This should work more reliably but i'm afraid of the \n on nonwindows
    //if (s < 0) return "";
    if (s < 0) {
        alert("ERROR in DailyCache startstr: Cant find " + startstr);
        return "";
    }
    var e = data.indexOf(endstr, s + startstr.length + 1);
    //if (e < 0) return "";
    if (e < 0) {
        alert("ERROR in DailyCache endstr: Cant find " + endstr);
        return "";
    }
    var str = data.substring(s + startstr.length + 1, e);
    if (localstoragename != "") localStorage.setItem(localstoragename, str);
    return str;
}

/////////////////////////////
// parseCacheOptional - returns the optional string from the daily cache
//  data is in the form:  <startstr>\n data <endstr>
//  localstoragename = name of local storage item, "" to not store it
//  startstr = starting string, endstr = ending string. can be "\n"
//  exit    returns the string. "" if no string.
function parseCacheOptional(data, localstoragename, startstr, endstr) {
    var s = data.indexOf(startstr);
    if (s < 0) return "";
    var e = data.indexOf(endstr, s + startstr.length + 1);
    if (e < 0) return "";
    var str = data.substring(s + startstr.length + 1, e);
    if (localstoragename != "") localStorage.setItem(localstoragename, str);
    return str;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  parseCacheRemove - identical to parseCache but removes the local storage item if it is not present in the data
function parseCacheRemove(data, localstoragename, startstr, endstr) {
    var s = parseCacheOptional(data, localstoragename, startstr, endstr);
    if (s == "") localStorage.removeItem(localstoragename);
    return s;
}

//////////////////////////////////////////////////////////////////////////////////
// FixDates - add year to coming events and activities. So 0122;1000;1100... becomes 160122;1000;1100....
//  This is kinda ugly.  maybe we should do it inline.  But this way we only do it once.
//  NOTE: there MUST be a new year event to change years: 0101;0000;0000;E;20yy
//  entry   itemname = name of storage item
//  exit    year added to all dates to become yymmdd
function FixDates(itemname) {
    var year = (gYear - 2000).toFixed(0) ; //yy
    var data = localStorage.getItem(itemname);
    var CE = data.split("\n");  // break it up into an array of rows
    // run through each row, add date
    for (var i = 0; i < CE.length; i++) {
        if (CE[i] == "") continue;
        if (CE[i].charAt(4) != ";") continue; // if not nnnn; skip because it will be a year
        if (CE[i].substr(0, 19) == "0101;0000;0000;E;20")  year = CE[i].substr(19, 2); // new year flag
        CE[i] = year + CE[i]; // insert year
    }
    CE = CE.join("\n");  // reassemble the string
    localStorage.setItem(itemname, CE); // replace it
}

//////////////////////////////////////////////////////////////////////////////////
//  ClearCacheandExit   a debug aid to simulate initial startup by removing all elements from cache
function ClearCacheandExit() {
    localStorage.clear();
    if(isPhoneGap()) navigator.app.exitApp();
}

/////////////////////////////////////////////////////////////////////////////////////
//  update all data on a regular basis. 
//  also redisplay the next ferry times & tides & open hours every minute
//  update current weather every 15 minutes. update forecast every 30 minutes.
//  Called every 60 secs and every time the main page is redisplayed.
function timerUp() {

    // everything you want to do every minute. These all go against localStorage strings, so no query
    InitializeDates(0);
    gLastUpdatems = Date.now();
    gUpdateCounter++;
    document.getElementById("updatetime").innerHTML = "Updated " + FormatTime(gTimehhmm);

    // special handling for other than the main page
    switch (gDisplayPage) {
        case "comingeventspage":
            return;
        case "emergencypage":
            return;
        case "ferryschedulepage":
            return;
        case "openhourspage":
            return;
        case "tidespage":
            if (gUserTideSelection) return;
            TidesDataPage();
            //var periods = JSON.parse(localStorage.getItem("jsontides"));
            //ShowTideDataPage(periods, true);
            return;
        case "weatherpage":
            return;
        case "mferrywebcampage":
            ShowFerryWebCam();
            return; // how to force redisplay
        case "aboutpage":
            DisplayLoadTimes();
            return;
        case "businesspage":
            return;

    }

    // main page update

    ShowCachedData();

    // reload daily stuff - ferry schedule, store hours, coming events, tides
    var dailycacheloaded = localStorage.getItem("dailycacheloaded");
    if ((dailycacheloaded == null) || (Number(dailycacheloaded) != gMonthDay)) {
        ReloadCachedData();
    }

    // check location for change every minute 
    if (gFerryHighlight && isPhoneGap()) getGeoLocation();

    // get tides once/day
    //getTideData();  moved to getDailyCache

    // current weather every 20 
    getCurrentWeather(); // gets weather async every 20 min. Timer is in routine.

    // forecast every 4 hours
    getForecast(); // gets weather async every 4 hour. Timer is in routine.

    // alerts every minute
    getAlertInfo(); // alerts every minute. Timer is in routine.

    //DisplayLoadTimes();
}

/////////////////////////////////////////////////////////////////////////////
// focus and blur events
// focus: if > 60 sec since last update, call timerUp();
function focusEvent() {
    gFocusCounter++;
    gFocusTime = Date.now();
    if (gMyTimer == null) {  // if timer is off
        gMyTimer = setInterval("timerUp()", 60000);  // restart timeout in milliseconds. currently 60 seconds
        timerUp(); // restart the timer if needed
    } else {
        //var d = new Date();
        //if ((d.getTime() - gLastUpdatems) > 60000) timerUp(); // if > 60 secs
        if ((gFocusTime - gLastUpdatems) > 60000) timerUp(); // if > 60 secs
    }
}

function blurEvent() {
    clearInterval(gMyTimer); // TURN OFF TIMER WHEN FOCUS IS LOST
    gMyTimer = null;
}

/////////////////////////////////////////////////////////////////////////////////////////
//  onResume
function onPause() {
}

//  resume: handle as a focus event.
function onResume() {
    gResumeCounter++;
    gResumeTime = Date.now();
    focusEvent();
}

//  backKeyDown: when back key is pressed, return to main page. If on main page (and not menu) exit.
function backKeyDown() {
    // Call my back key code here.
    if (gDisplayPage == 'mainpage' && isPhoneGap()  && !gMenuOpen) navigator.app.exitApp();
    ShowMainPage();
}

//////////////////////////////////////////////////////////////////////////////////
// show all cached data, i.e. all data in localStorage.
//  Shows: Ferry Times, Tides, Alerts, Open Hours, events, activities, forecast, current weather
// 
function ShowCachedData() {
    WriteNextFerryTimes();  // show cached ferry schedule
    ShowNextTides(); // show cached tide data
    DisplayAlertInfo();
    ShowOpenHours(); //  open hours
    document.getElementById("nextevent").innerHTML = DisplayNextEvents(localStorage.getItem("comingevents"));
    document.getElementById("nextactivity").innerHTML = DisplayNextEvents(localStorage.getItem("comingactivities"));
    var s = localStorage.getItem("message");
    if (!IsEmpty(s)) document.getElementById("topline").innerHTML = s;

    var s = localStorage.getItem("forecast");
    if (s != null) document.getElementById("forecast").innerHTML = s;

    s = localStorage.getItem("currentweather"); // cached current weather
    if (s != null) document.getElementById("weather").innerHTML = s;
    
    //s = localStorage.getItem("maintablerows"); // additional main page rows. Removed 9/1/18 because of issue with icons.
    //if (!IsEmpty(s)) document.getElementById("maintablerows").innerHTML = s;

}

////////////////////////////////////////////////////////////////////////////////////
// Reload cached data - calls all the ajax calls to get the data and recache it in localStorage.
//  Forces reload of: Daily cache
//                  Alerts, weather forecast, current weather
//
function
    ReloadCachedData() {
    //alert("reload cached data");
    InitializeDates(0);
    GetDailyCache();  // no limit
    //GetComingEvents();// merged into GetDailyCache on 6/6/16
    gAlertTime = 0; // force alert reload
    getAlertInfo();
    //localStorage.removeItem("tidesloadedmmdd"); // force tides
    //getTideData();// no limitmerged into GetDailyCache on 6/6/16
    localStorage.removeItem("forecasttime"); // force forecast reload at start of new day
    getForecast();// limited to every 120 min
    localStorage.removeItem("currentweathertime"); // force weather reload at start of new day
    getCurrentWeather();// limited to every 20 min
    //DisplayLoadTimes();
}

//  ReloadCachedDataButton - called only when user manually asks to reload the data
var gReloadCachedDataButtonInProgress = false;
function ReloadCachedDataButton() {
    if (gMenuOpen) CloseMenu();  // close the menu            if (gMenuOpen) CloseMenu();  // close the men
    gReloadCachedDataButtonInProgress = true;

    ReloadCachedData();

}

//////////////////////////////////////////////////////////////////////////////////////
// DisplayLoadTimes() displays time data loaded
function DisplayLoadTimes() {
    document.getElementById("reloadtime").innerHTML = "<br/>Stats:<br/>App started " + gAppStartedDate +
        ", Update " + DispElapsedSec(gLastUpdatems) + " #" + gUpdateCounter +
        ",<br/>Cached reloaded " + localStorage.getItem("dailycacheloaded") + " @" + localStorage.getItem("dailycacheloadedtime") +
        ", Tides loaded:" + localStorage.getItem("tidesloadedmmdd") +
        ", PBotsInit:" + (isPhoneGap()? (((gTimeStampms - Number(LSget("pushbotstime"))) / 3600000).toFixed(2) + " hr ago") : "none.") +
        "<br/>k=" + DeviceInfo() + " n=" + localStorage.getItem("Cmain") + " p=" + localStorage.getItem("pagehits") +
        "<br/>Forecast:" + DispElapsedMin("forecasttime") + " #" + gWeatherForecastCount.toFixed(0) +
        ", CurrentWeather:" + DispElapsedMin("currentweathertime") + " #" + gWeatherCurrentCount.toFixed(0) +
        "<br/>Alerts: " + DispElapsedSec(gAlertTime) + " #" + gAlertCounter.toFixed(0) +
        "<br/>Focus " + DispElapsedSec(gFocusTime) + " #" + gFocusCounter.toFixed(0) +
        ", Resume " + DispElapsedSec(gResumeTime) + " #" + gResumeCounter.toFixed(0) +
        "<br/>Long:" + gLongitude + ",Lat:" + gLatitude + ",OnAI:" + gLocationOnAI +
        "<br/>ScreenWidth:" + window.screen.width;

}

//  DispElapsedSec = calculate  & display elapsed time between now and the passed in time
//  oldtime = saved millisec (ms) value 
function DispElapsedSec(oldtime) {
    if (oldtime == 0) return "";
    return ((Date.now() - oldtime)/1000).toFixed(0) + " sec ago";
}
//  DispElapsedMin = calculate  & display elapsed time between now and the time stored in the tag
//  localstoragetag = local storage saved seconds value 
function DispElapsedMin(localstoragetag) {
    return ((Date.now()/1000 - Number(localStorage.getItem(localstoragetag))) / 60).toFixed(0) + " min ago";
}

////////////////////////////////////////////////////////////////////////////////////////
// show page (new page name). Turns off the page currently being displayed (gDisplayPage) and turns on newpage.
//  newpage = id of div for page, 'gDisplayPage' = name of currently displaying page, 
//  'gTableToClear' = name of table to clear in former page
//  exit    gDisplayPage = the new page
function ShowPage(newpage) {
    if (gMenuOpen) CloseMenu();  // close the menu
    if (gDisplayPage == newpage) return;
    if(newpage != "mainpage") MarkPage(newpage.substr(0, 1)); // ADD PAGE LETTER
    // clear out rows of table for former page
    if (gTableToClear != null) {
        var table = document.getElementById(gTableToClear);
        if (table != null) clearTable(table);
        gTableToClear = null;
    }
    // now switch to new page
    Hide(gDisplayPage);
    Show(newpage);
    //document.getElementById(gDisplayPage).setAttribute('style', 'display:none;');
    //document.getElementById(newpage).setAttribute('style', 'display:block;');
    gDisplayPage = newpage; // remember it
    window.scroll(0, 0);  // force scroll (1.7)
}

function SetPageHeader(header) {
    document.getElementById("h1title").innerHTML = header;
    document.getElementById("h1menu").innerHTML = "<i class='material-icons'>&#xe5c4;</i>&nbsp";  // back arrow
    //if (isPhoneGap() && !isAndroid()) document.getElementById("h1menu").innerHTML = "&larr;back";
    //else document.getElementById("h1menu").innerHTML = "&nbsp&larr;&nbsp";
}
///////////////////////////////////////////////////////////////////////////////////////////////////
// show main page
function ShowMainPage() {
    SetPageHeader(" Anderson Island Assistant");
    document.getElementById("h1menu").innerHTML = "<i class='material-icons'>&#xe5d2;</i>&nbsp";  // menu symbol in header
    //document.getElementById("h1menu").innerHTML = "&#9776&nbsp&nbsp";
    ShowPage("mainpage");
    timerUp();
}

/////////////////////////////////////////////////////////////////////////////////////////////////////
// Dialog - open a dialog
//  text = message with embedded html.  Heading is ignored for now.
function Dialog(text, heading) {
    var md = document.getElementById("modaldialog");
    md.style.display = "block";
    document.getElementById("modaltext").innerHTML = text;
    // When the user clicks anywhere outside of the dialog, close it
    window.onclick = function (event) {
        if (event.target == md) {
            ModalClose();
            //md.style.display = "none";
        }
    }
}

/////////////////////////////////////////////////////////////////////////////////
//  close the modal dialog and clear out its text.
function ModalClose() {
    document.getElementById("modaltext").innerHTML = ""; // clear the display
    Hide("modaldialog");
    //document.getElementById("modaldialog").style.display = "none";
}

//</script>


//  ******  ******  ******  ******  *     *
//  **      **      *    *  *    *   *   *
//  ******  ******  ******  ******     **
//  **      **      ****    ****       **
//  **      ******  **  **  **  **     **


//<!-- FERRY PAGE ----------------------------------------------------------------------------------------------------------->
//<script>
//==== FERRY  ALL CODE =======================================================================================

//////////////// FERRY MAIN PAGE /////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////
// WriteNextFerryTimes - Front Page. Finds the next ferry times and puts them into the Dom for the FRONT PAGE
//

function WriteNextFerryTimes() {
    // ferrytimes = time in 24 hours, S=Steilacoom, A=Anderson Island, 
    // ferrydays:  *=always, H=holiday, 0-6=days of week, AFXY=special rules H=(12/31,1/1,Mem day, 7/3,7/4,labor day,thanksgiving, 12/24,12/25),F=Fuel run 1,3 Wednesday, X=Friday Only labor day-6/30, Y=Fridays only 7/1=labor day
    //var ferrytimeS = [545, "H123456A", 645, "*", 800, "*", 900, "*", 1000, "F", 1200, "*", 1410, "*", 1510, "*", 1610, "*", 1710, "*", 1830, "*", 1930, "*", 2040, "4560H", 2200, "X6H", 2300, "Y"];
    //var ferrytimeA = [615, "H123456A", 730, "*", 830, "*", 930, "*", 1030, "F", 1230, "*", 1440, "*", 1540, "*", 1640, "*", 1740, "*", 1900, "*", 2000, "*", 2110, "4560H", 2230, "X6H", 2330, "Y"];
    // at this point, i = the next valid ferry run

    var str;
    var v = "";
    TXTS.FerryTime = "";
    BIGTX.FerryTime = "";
    // check for a DELAYED: or DELAYED nn MIN: and extract the string
    var s = localStorage.getItem('alerttext');
    if (!IsEmpty(s)) {
        var i = s.indexOf("DELAY");
        if (i > 0) {
            var j = s.indexOf(":", i);
            if (j > i) v = "<span style='font-weight:bold;color:red'>" + s.substring(i, j) + "</span><br/>";
            TXTS.FerryTime = s.substring(i, j).replace("MIN", "Minutes") + ". ";
        }
    }

    //if (holiday) v = v + "Hoilday<br/>"
    //v = v + "<span style='font-weight:bold'>Steilacoom: " + 
    //     FindNextFerryTime(UseFerryTime("S"), "", "S") + "</span>";
    //var a = "</br><span style='font-weight:bold;color:blue'>Anderson:&nbsp&nbsp&nbsp " + 
    //         FindNextFerryTime(UseFerryTime("A"), UseFerryTime("K"), "A") + "</span>";
    //document.getElementById("ferrytimes").innerHTML = v + a;
    var SteilHighlight = ""; var AIHighlight = "";
    if (gFerryHighlight == 1) {     // && gLatitude > 0) {
        if (gLocationOnAI == 1) AIHighlight = "background-color:#ffff80"; //#ffff00=yellow, #ffffE0=lightyellow
        else if (gLocationOnAI == 0) SteilHighlight = "background-color:#ffff80";
    }
    TXTS.FerryTime += "The next ferry departs steilacoom ";
    BIGTX.FerryTime = "Steilacoom: <br/> "
    v = v + "<table border-collapse: collapse; style='padding:0;margin:0;' ><tr style='font-weight:bold;" + SteilHighlight + "'><td style='padding:1px 0 1px 0;margin:0;'>Steilacoom: </td>" +
        FindNextFerryTime(UseFerryTime("S"), "", "S") + "</tr>";
    TXTS.FerryTime += ". The next ferry departs anderson island ";  // use commas for a pause
    BIGTX.FerryTime += "<br/>Anderson Is:<br/> "
    var a = "<tr style='font-weight:bold;color:blue;" + AIHighlight + "'><td style='padding:1px 0 1px 0;margin:0;'>Anderson: </td>" +
        FindNextFerryTime(UseFerryTime("A"), UseFerryTime("K"), "A") + "</tr></table>";
    document.getElementById("ferrytimes").innerHTML = v + a;
}


/////////////////////////////////////////////////////////////////////////////////////////
// return the next ferry time as a string. 
//      entry ferrytimes is the array of times and days (see ferrytimeA)
//            ferrytimeK = is the array of times and days for ketron
//            SA = S or A
//      exit  returns html string of ferry times
//            updates global TXTS.FerryTime text-to-speech string.
//
function FindNextFerryTime(ferrytimes, ferrytimeK, SA) {
    var ShowTimeDiff = false;
    InitializeDates(0);
    var i = 0;
    var ketron = false; //ketron run ;
    var nruns = 0;
    var ft = ""; var ketront = "";
    // roll through the ferry times, skipping runs that are not valid for today
    for (i = 0; i < ferrytimes.length; i = i + 2) {
        if (gTimehhmm >= ferrytimes[i]) continue;  // skip ferrys that have alreaedy run
        // now determine if the next run will run today.  If it is a valid run, break out of loop.
        if (ValidFerryRun(ferrytimes[i + 1], ferrytimes[i])) {
            ft = ft + "<td style='padding:1px 0 1px 0;margin:0;'>" + ShortTime(ferrytimes[i]);  // display in table
            if (nruns < 2) TXTS.FerryTime = TXTS.FerryTime + " at " + ShortTime(ferrytimes[i], 1) + ","; // text-to-speech. 2 runs only.
            BIGTX.FerryTime += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + ShortTime(ferrytimes[i]);

            // first run only: insert minutes till it sails
            if (nruns == 0 && gFerryShowIn) {
                var rtd = RawTimeDiff(gTimehhmm, ferrytimes[i]); // raw time diff
                var ftd = timeDiffhm(gTimehhmm, ferrytimes[i]);
                TXTS.FerryTime = TXTS.FerryTime + " in " + timeDiffTTS(rtd) + ", then ";  // text-to-speech time remaining
                BIGTX.FerryTime += " (" + ftd + ")";
                if (rtd <= 15) ft = ft + "<span style='font-weight:normal;color:red'> (" + ftd + ")</span>";  // if < 15 min, make time red
                else ft = ft + "<span style='font-weight:normal'> (" + ftd + ")</span>";
            }
            // Ketron special case 
            if (ferrytimeK != "") { // add ketron time for this run
                if ((ferrytimeK[i] != 0) && (ValidFerryRun(ferrytimeK[i + 1], ferrytimeK[i]))) {
                    ketron = true;
                    ketront = ketront + "<td style='padding:0;margin:0;'>" + ShortTime(ferrytimeK[i]) + "</td>";
                } else ketront = ketront + "<td style='padding:0;margin:0;'>------</td>";
            }
            ft = ft + "&nbsp&nbsp</td>";
            BIGTX.FerryTime += "<br/>";
            if (nruns == 1 && gFerryShow3 == 0) break;  // show 2 runs
            if (nruns == 2 && gFerryShow3 == 1) break;  // show 3 runs

            nruns++;
        };
    }
    // we ran out of the schedule today so give the 1st run for tomorrow
    if (i >= ferrytimes.length) ft = ft + FindNextFerryTimeTomorrow(SA, nruns);

    // ketron only if there is a ketron run, and it is valid. note iketron ponts to 1st run
    if ((ferrytimeK != null) && ketron) ft = ft + "</tr><tr style='font-weight:bold;color:gray'><td style='padding:0px;margin:0;'>Ketron:</td>" + ketront;
    return ft;
}

/////////////////////////////////////////////////////////////////////////////////////////
// return the single next ferry time as a string. 
//      entry ferrytimes is the array of times and days (eigher for steilacoom or ai))
//      exit  returns string of next single ferry time.
function FindNextSingleFerryTime(ferrytimes) {
    InitializeDates(0);
    var i = 0;
    // roll through the ferry times, skipping runs that are not valid for today
    for (i = 0; i < ferrytimes.length; i = i + 2) {
        if (gTimehhmm >= ferrytimes[i]) continue;  // skip ferrys that have alreaedy run
        // now determine if the next run will run today.  If it is a valid run, break out of loop.
        if (ValidFerryRun(ferrytimes[i + 1], ferrytimes[i])) {
            var rtd = RawTimeDiff(gTimehhmm, ferrytimes[i]);
            var ftd = timeDiffhm(gTimehhmm, ferrytimes[i]);
            if (rtd < 13) return ShortTime(ferrytimes[i]) + "<span style='color:red'> (in " + ftd + ")</span>";
            else return ShortTime(ferrytimes[i]) + " (in " + ftd + ")";
        }
    }
    // we ran out of the schedule today so give the 1st run for tomorrow
    return "tomorrow";
}

//////////////////////////////////////////////////////////////////////////////////////////////////
//  FindNextFerryTimeTomorrow - finds the 1st runs on the NEXT day
//  Entry   SA = S or A
//          nruns = 0 if table time column 1
//  Exit    returns string with 1st valid run for tomorrow
function FindNextFerryTimeTomorrow(SA, nruns) {
    var i = 0;
    var ft = "";
    var Timehhmm = gTimehhmm; // save current time
    InitializeDates(1);   // tomorrow
    var ferrytimes = UseFerryTime(SA); // get the ferry time for tomorrow
    for (i = 0; i < ferrytimes.length; i = i + 2) {
        if (ValidFerryRun(ferrytimes[i + 1], ferrytimes[i])) {
            ft = ft + "<td style='color:gray;font-weight:normal;padding:0;margin:0;'>" + ShortTime(ferrytimes[i]);
            // insert remaining time
            //if (nruns == 0 && gFerryShowIn) {
            //    ft = ft + " (" + timeDiffhm(Timehhmm, ferrytimes[i]) + ")";
            //}
            ft = ft + "&nbsp&nbsp</td>";
            if (nruns < 2) TXTS.FerryTime += " tomorrow morning at " + ShortTime(ferrytimes[i], 1);
            if (nruns == 1 && gFerryShow3 == 0) break;  // show 2 runs
            if (nruns == 2 && gFerryShow3 == 1) break;  // show 3 runs
            nruns++;
        }
    }

    InitializeDates(0); // reset to today
    if (i < ferrytimes.length) return ft
}


////////////////////////////////////////////////////////////////////////
//  ParseFerryTimes - convert the ferrytimesx strings into arrays ferrytimeX
//      which is a mixed array of runtime(number);rules(string); runtime; rules, ...
//      works on ver 1.7 data.  ignores comma delimited ver 1.5 data.
//  Entry   localStorage items ferrytimesxx are set
//  Exit    arrays ferrytimexx are set 
function ParseFerryTimes() {
    var s = localStorage.getItem("ferrytimess");
    if (IsEmpty(s)) return; // if no items, return and leave the defaults alone
    if (s.charAt(3) == ",") { gForceCacheReload = true; return; } // if ver 1.5 comma delimited time [445,] return and leave defaults
    ferrytimeS = FillFerryArray("ferrytimess");
    ferrytimeA = FillFerryArray("ferrytimesa");
    ferrytimeK = FillFerryArray("ferrytimesk");
    ferrytimeS2 = FillFerryArray("ferrytimess2");
    ferrytimeA2 = FillFerryArray("ferrytimesa2");
    ferrytimeK2 = FillFerryArray("ferrytimesk2");

    str = localStorage.getItem("ferrydate2");
    if (IsEmpty(str)) return;
    var d = new Date(str);  // convert ferry date 2 to ms
    gFerryDate2 = d.getTime(d); // ms till cutover
}

// Helper - convert ferry times from string to number, so "0800" becomes 800
//  remember that the ferry time is every other entry in each array
//  exit    returns new ferry time array
function FillFerryArray(itemname) {
    var str;
    var FA;
    str = localStorage.getItem(itemname);
    if (str == null) return null;
    FA = str.split(";"); // fill array with strings
    // convert ferry time to a number
    for (var i = 0; i < FA.length; i = i + 2) {
        if (isNaN(FA[i])) alert("Data error " + itemname + " " + i);
        FA[i] = Number(FA[i]);  // convert to number
    }

    return FA;
}

/////////////////////////////////////////////////////////////////////////
//  GetFerryTimeArray - select proper ferry time array (S or A) based on date
//  entry   SA = "S" for Steilacoom, A for Anderson, K for Ketron
//          gFerryDate2 = cutover time in ms
//          gTimeStampms = 'current' time for this function
//  exit    returns if SA=S: ferrytimeS , ferrytimeS2 if >=cutover date, 
//                  if SA=A: ferrytimeA , ferrytimeA2 if >=cutover date, 
function UseFerryTime(SA) {
    switch (SA) {
        case "S":
            if ((gFerryDate2 == 0) || (gTimeStampms < gFerryDate2)) return ferrytimeS;
            else return ferrytimeS2;
            break;
        case "A":
            if ((gFerryDate2 == 0) || (gTimeStampms < gFerryDate2)) return ferrytimeA;
            else return ferrytimeA2;
            break;
        case "K":
            if ((gFerryDate2 == 0) || (gTimeStampms < gFerryDate2)) return ferrytimeK;
            else return ferrytimeK2;
            break;
    }
}

/////////////////////////////////////////////////////////////////////////////////////////
// ValidFerryRun return true if a valid ferry time, else false.
//  alternate to having the rules special cased
// flag: *=always, 0-6=days of week, (xxxx) = eval rules in javascript
// ferrytime: ferry run time, used only for error message
//  eval rules are javascript, returning true for a valid run, else false
//    can use global variables gMonthDay, gDayofWeek, gWeekofMonth,...
//    e.g. ((gDayofWeek>0)&&(gDayofWeek<6)&&!InList(gMonthDay,1225,101,704,laborday,1123))

function ValidFerryRun(flag, ferrytime) {
    if (flag == undefined || flag == "") return false;
    if (flag.indexOf("*") > -1) return true; // good every day
    if (flag.substr(0, 1) != "(") {
        if (flag.indexOf(gLetterofWeek) > -1) return true;  // if day of week is encoded
        return false;
    }

    // (eval rules ).
    try {
        var t = eval(flag);
    } catch (err) {
        var msg = "Invalid Ferry Eval Rule for " + ferrytime + "\n" + flag + "\n" + err.message;
        alert(msg);
        t = false;
    }
    return t;
}


/////////////// FERRY DETAIL PAGE ////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////
// loads the ferry schedule at pierce web page
function ShowFerrySchedule() {
    var myurl = GetLink("ferryschedulelink", "http://www.co.pierce.wa.us/index.aspx?NID=2200");
    window.open(myurl, "_blank");
}
function ShowFerryLocation() {
    MarkPage("s");
    var myurl = GetLink("ferrylocationlink", "http://matterhorn11.co.pierce.wa.us/FerryStatus/");
    window.open(myurl, "_blank");
}


// Use the startApp plugin to directly start the pierce county ferry tickets app.  
function StartTicketApp() {
    if (isPhoneGap()) {
        if (isAndroid()) {
            // ANDROID: 
            // Default handlers "com.hutchind.cordova.plugins.launcher"
            var successCallback = function (data) {
            };
            var errorCallback = function (errMsg) {
                var link = GetLink("googleplayticketlink", 'https://play.google.com/store/apps/details?id=com.ttpapps.pcf');
                window.open(link, '_system');
            };
            var pkg = GetLink("androidpackageticketlink", "com.ttpapps.pcf"); // android ticket package
            window.plugins.launcher.launch({packageName: pkg }, successCallback, errorCallback);

            ////  com.lampa.startapp
            //var pkg = GetLink("androidpackageticketlink", "com.ttpapps.pcf"); // android ticket package
            //var sApp = startApp.set({ "package": pkg});
            //sApp.start(function () { /* success */
            //}, function (error) { /* fail */
            //    var link = GetLink("googleplayticketlink", 'https://play.google.com/store/apps/details?id=com.ttpapps.pcf');
            //    window.open(link, '_system');
            //});
        } else {
            // IOS:
            // Default handlers "com.hutchind.cordova.plugins.launcher"
            var successCallback = function (data) {
            };
            var errorCallback = function (errMsg) {
                var link = GetLink("iosticketlink", 'https://itunes.apple.com/us/app/pierce-county-ferry-tickets/id1107727955?mt=8');
                window.open(link, '_system');
            }
            var pkg = GetLink("iosinternalticketlink", "ttpapps.pcf://"); // IOS custom URL for ticket package
            window.plugins.launcher.launch({ uri: pkg }, successCallback, errorCallback);

            //  com.lampa.startapp
            //var pkg = GetLink("iosinternalticketlink", "ttpapps.pcf://"); // IOS custom URL for ticket package
            //var sApp = startApp.set(pkg);
            //sApp.start(function () { /* success */
            //}, function (error) { /* fail */
            //    var link = GetLink("iosticketlink", 'https://itunes.apple.com/us/app/pierce-county-ferry-tickets/id1107727955?mt=8');
            //    window.open(link, '_system');
            //});
        }
    } else {
        // WEB
        var link = GetLink("pierceferryticketlink", 'https://www.pierceferrytickets.com');
        window.open(link, '_system');
    }
}


/////////////////////////////////////////////////////////////////////////////////////////
// Loads the next ferry times into the global 'table' as a row for each run. 
// ferrytimesS, ferrytimesA is the array of times and days for Steelacoom and AI;
//  Entry: table = table to add to
//         ferrytimesS, A, K = array of ferry times for Steilacoom, AI, and Ketron
//         global gYYmmdd = date
//  Exit: builds table of run times. Note: cell id = YYmmddhhmmX where X=S/A/K
function BuildFerrySchedule(table, ferrytimesS, ferrytimesA, ferrytimesK) {
    var i;
    var ft;
    var amcolor = "#f0ffff";
    var extrat = 13; // extra time in display 12 minutes
    var boldS = false, boldA = false, boldK = false;
    var validS = false, validA = false, validK = false; // true if runs are valid
    // roll through the ferry times, skipping runs that are not valid for today
    for (i = 0; i < ferrytimesS.length; i = i + 2) {
        if ((gTimehhmm >= (ferrytimesS[i] + extrat)) && (gTimehhmm >= (ferrytimesA[i] + extrat)) && (gTimehhmm > Number(ferrytimesK[i]))) continue;  // skip ferrys that have alreaedy run
        //if ((gTimehhmm >= (Number(ferrytimesS[i]) + extrat)) && (gTimehhmm >= (Number(ferrytimesA[i]) + extrat)) && (gTimehhmm > Number(ferrytimesK[i]))) continue;  // skip ferrys that have alreaedy run
        // now determine if the next run will run today.  If it is a valid run, break out of loop.
        validS = (ferrytimesS[i] != 0) &&  ValidFerryRun(ferrytimesS[i + 1], ferrytimesS[i]);
        validA = (ferrytimesA[i] != 0) && ValidFerryRun(ferrytimesA[i + 1], ferrytimesA[i]);
        validK = (ferrytimesK[i] != 0) && ValidFerryRun(ferrytimesK[i + 1], ferrytimesK[i]);
        if (validS||validA||validK) {
            // Steelacoom
            var row1, row1col1, row1col2;
            row1 = table.insertRow(-1);
            row1col1 = row1.insertCell(0);
            row1col1.style.border.width = 1;
            row1col1.style.border = "thin solid black";
            if (validS) {
                row1col1.innerHTML = "&nbsp&nbsp" + FormatTime(ferrytimesS[i]);
                if (gTimehhmm > ferrytimesS[i]) row1col1.style.color = "lightgray";
                if (ferrytimesS[i] < 1200) row1col1.style.backgroundColor = amcolor;
                row1col1.id = gYYmmdd.toFixed(0) + formathhmm(ferrytimesS[i]) + "S"; // id = yymmddhhmmS
                row1col1.onclick = function () { ferryclick(this.id) };
            } else {
                row1col1.innerHTML = "&nbsp&nbsp"
            }

            // Anderson Island;
            row1col2 = row1.insertCell(1);
            row1col2.style.border = "thin solid black";
            if (validA) {
                row1col2.innerHTML = "&nbsp&nbsp" + FormatTime(ferrytimesA[i]);
                if (gTimehhmm > ferrytimesA[i]) row1col2.style.color = "lightgray";
                else row1col2.style.color = "darkblue";
                if (ferrytimesA[i] < 1200) row1col2.style.backgroundColor = amcolor;
                row1col2.id = gYYmmdd.toFixed(0) + formathhmm(ferrytimesA[i]) + "A"; // id = yymmddhhmmA
                row1col2.onclick = function () { ferryclick(this.id) };
            } else {
                row1col2.innerHTML = "&nbsp&nbsp";
            }

            // Ketron
            var row1col3 = row1.insertCell(2);
            row1col3.style.border = "thin solid black";
            if (validK) {
                row1col3.innerHTML = "&nbsp&nbsp" + FormatTime(ferrytimesK[i]);
                if (gTimehhmm > ferrytimesK[i]) row1col3.style.color = "lightgray";
                else row1col3.style.color = "brown";
                row1col3.style.border = "thin solid black";
                if (ferrytimesK[i] < 1200) row1col3.style.backgroundColor = amcolor;
                row1col3.id = gYYmmdd.toFixed(0) + formathhmm(ferrytimesK[i]) + "K"; // id = yymmddhhmmS
                row1col3.onclick = function () { ferryclick(this.id) };
            }
            // make the next run bold
            if (row1.rowIndex <= 3) { // row 3 or 4 (index=2 or 3) is the next run
                if (gTimehhmm <= ferrytimesS[i] && !boldS) {
                    row1col1.style.fontWeight = "bold";  // bold 
                    boldS = true;
                }
                if (gTimehhmm <= ferrytimesA[i] && !boldA){
                    row1col2.style.fontWeight = "bold";  // bold
                    boldA = true;
                }
                if (gTimehhmm <= ferrytimesK[i] && !boldK) {
                    row1col3.style.fontWeight = "bold";  // bold 
                    boldK = true;
                }
            }

        }
    }
    return;
}
// formathhmm - ensure 4 digit hhmm
function formathhmm(hhmm) {
    var s = hhmm.toFixed(0);
    if (s.length == 4) return s;
    else return "0" + s;
}
///////////////////////////////////////////
//  ferryclick - Add ferry time and date to calendar
//  tc = cell id: date (yymmdd) time (hhmm) S/A/K as a string. 
function ferryclick(tc) {
    if (!isPhoneGap()) return; // if not phone, return
    var y = Number(tc.substring(0, 2)) + 2000; // year
    var M = Number(tc.substring(2, 4)); // month
    var d = Number(tc.substring(4, 6)); // day
    var h = Number(tc.substring(6, 8)); // hr
    var m = Number(tc.substring(8, 10)); // min
    var el = ""; to = "";
    switch (tc.substr(10, 1)) {
        case "S": el = "Steilacoom"; to = "Anderson Island"; break;
        case "A": el = "Anderson Island"; to = "Steilacoom"; break;
        case "K": el = "Ketron"; to = "Steilacoom"; break;
    }
    if (confirm("Add ferry run from " + el + " at " + FormatTime(tc.substring(6,10)) + " on " + M + "/" + d + " to your calendar?\n(Your phone will remind you before departure)") != true) return;
    M = M - 1;
    var startDate = new Date(y, M, d, h, m, 0, 0); // beware: month 0 = january, 11 = december
    m = m + 30; // allow for 30 minute sailing
    if (m >= 60) {
        m = m - 60;
        h = h + 1;
    }
    var endDate = new Date(y, M, d, h, m, 0, 0);
    // add to calendar
    var success = function (message) { };
    var error = function (message) { alert("Unable to add to calendar."); };
    window.plugins.calendar.createEventInteractively("Ferry to " + to, el, "", startDate, endDate, success, error);
    //alert(as);
}

//////////////////////////////////////////////////////////////////////////////////
// ScheduleByDate - ask for date and then run schedule
function ScheduleByDate() {
    GetDateFromUser(ScheduleByDateCallback);
}
function ScheduleByDateCallback(userdate) {
    if (userdate == "") return;
    DisplayFerrySchedule(userdate);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Display the page
function DisplayFerrySchedulePage() {
    SetPageHeader("Ferry Schedule");
    ShowPage("ferryschedulepage");
    DisplayFerrySchedule("");
}

////////////////////////////////////////////////////////////////
// DisplayFerrySchedule build and displays the grid 'ferrytable'
//  entry userdate = "" for today, else mm/dd
function DisplayFerrySchedule(userdate) {
    var row1, row1col1;

    if (userdate == "") InitializeDates(0);
    else InitializeDates(userdate);
    document.getElementById("ferrymessage").innerHTML = localStorage.getItem("ferrymessage");

    table = document.getElementById("ferrytable");
    gTableToClear = "ferrytable";
    clearTable(table);
    if(table.rows.length>0) table.deleteRow(0);  // clear 1st row

    row1 = table.insertRow(-1);
    row1col1 = row1.insertCell(0);
    row1col1.style.backgroundColor = "blue";
    row1col1.style.color = "white";
    if (userdate == "") row1col1.innerHTML = 'TODAY';
    else row1col1.innerHTML = gDayofWeekName[gDayofWeek];
    row1col1 = row1.insertCell(1);
    row1col1.style.backgroundColor = "blue";
    row1col1.style.color = "white";
    row1col1.innerHTML = gMonth + "/" + gDayofMonth + (holiday ? " Holiday" : "") ;
    row1col1 = row1.insertCell(2);
    row1col1.style.backgroundColor = "blue";

    InsertStAI(table);
    BuildFerrySchedule(table, UseFerryTime("S"), UseFerryTime("A"), UseFerryTime("K"));
 
    gTimehhmm = 0;  // ignore current time
    var i;
    for (i = 0; i < 7; i++) {
        InitializeDates(1);  // tomorrow
        row1 = table.insertRow(-1);
        row1col1 = row1.insertCell(0);
        row1col1.colSpan = "3";
        row1col1.style.backgroundColor = "blue";
        row1col1.style.color = "white";
        row1col1.innerHTML = gDayofWeekName[gDayofWeek] + " " + gMonth + "/" + gDayofMonth + (holiday ? " Holiday" : "");
        InsertStAI(table);
        BuildFerrySchedule(table, UseFerryTime("S"), UseFerryTime("A"), UseFerryTime("K"));
    }

    InitializeDates(0);  // reset dates back
}

// build steilacoom/ai label
function InsertStAI(table) {
    row1 = table.insertRow(-1);
    row1.style.color = "darkblue";
    row1col1 = row1.insertCell(0);
    row1col1.style.border.width = 1;
    row1col1.style.border = "thin solid black";
    if (gFerryHighlight == 1 && gLocationOnAI == 0) row1col1.style.backgroundColor = "#ffff80";
    else row1col1.style.backgroundColor = "lightblue";
    row1col1.style.color = "black";
    row1col1.innerHTML = "&nbsp Steilacoom &nbsp";
    row1col1 = row1.insertCell(1);
    row1col1.style.border.width = 1;
    row1col1.style.border = "thin solid black";
    if (gFerryHighlight == 1 && gLocationOnAI == 1) row1col1.style.backgroundColor = "#ffff80";
    else row1col1.style.backgroundColor = "lightblue";
    row1col1.style.color = "darkblue";
    row1col1.innerHTML = "&nbsp  Anderson Is &nbsp";
    row1col1 = row1.insertCell(2);
    row1col1.style.border.width = 1;
    row1col1.style.border = "thin solid black";
    row1col1.style.backgroundColor = "lightblue";
    row1col1.style.color = "maroon";
    row1col1.innerHTML = "&nbsp Ketron &nbsp";
}
////</script>


//  ******  ******  ******  *    *
//  **  **  *    *  **      **   *
//  **  **  ******  ******  * *  *
//  **  **  **      **      *  * *
//  ******  **      ******  *    *



//<!-- OPEN HOURS ------------------------------------------------------------------------------------------------------------->
//<script>
//======== OPEN HOURS ============================================================================

///////////// OPEN HOURS MAIN PAGE /////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////
// ShowOpenHours - shows whether something is open or closed. 
//  Entry: OpenHours is an array of objects, loaded from OpenHoursJSON in 'dailycache.txt'
//  exit: sets the openhours element to the open hours string
// openHours format is array of objects, 1 object per business.
// each object is: name, phone, desc, href, 
//      array of H: from (mmdd), to (mmdd), H [array of 7 hhmm-hhmm opentime-closetime], 
//                  optional D2 [array of 7 additional hhmm-hhmm opentime-closetime]
//   where xxxtime = hhmm-hhmm in 24 hour format. 
//   List more restrictive from/to dates first.
//     
function ShowOpenHours() {
    var openlist;
    openlist = "";
    TXTS.OpenHours = "";

    // loop through the openHours array (each array entry is one business)
    for (var i = 0; i < OpenHours.length; i++) {
        var Oh = OpenHours[i];  // entry for 1 business
        if (gIconSwitch == 1) openlist += "<i class='material-icons' > " + Oh.Icon + "</i >&nbsp;";
        openlist += "<span style='font-weight:bold'>" + Oh.Name + "</span>:" + GetOpenStatus(Oh, gMonthDay, gTimehhmm) + "<br/>";
        TXTS.OpenHours += Oh.Name + RemoveTags(GetOpenStatus(Oh, gMonthDay, gTimehhmm)).replace("p ", "pm") + ". ";
        if (i == 2) break; // only do 1st 3 on main page
    } // end for
    openlist += "More ...";
    document.getElementById("openhours").innerHTML = openlist;
    return;
}

///////////////////////////////////////////////////////////////////////////////////////
// GetOpenStatus - determine if business is open & return string.
//      honors opendates, openhours, and openhours2
//  entry: Oh = OpenHours object for 1 business
//          mmdd = month day
//          hhmm = hours minutes
//  exit: returns html display string
function GetOpenStatus(Oh, mmdd, hhmm) {
    var i, j;
    var opentime, closetime, opentime2, closetime2;
    var TClosed = "Closed"; var TClosedAW = "Closed"; var TOpen = "Open"; var TOpens = "Opens"; 
    // for garbage pickup, change wording to 'pickup'
    if (Oh.Pickup == "on") { TClosed = ""; TClosedAW = "No Pickup"; TOpen = "Pickup"; TOpens = "Pickup";}  

    if (IsClosed(Oh.Closed, mmdd)) return " <span style='color:red;font-weight:bold'> " + TClosed + " today. </span>"
    // loop through the oh.Sch entries. Each entry is for 1 date range.
    for (i = 0; i < Oh.Sc.length; i++) {
        if (((mmdd >= Oh.Sc[i].From) && (mmdd <= Oh.Sc[i].To)) ||
            ((Oh.Sc[i].From > Oh.Sc[i].To) && ((mmdd <= Oh.Sc[i].To) || (mmdd >= Oh.Sc[i].From)))) {

            // ok we have the H entry for the date, now check it

            // Alternate week check.  return if not the correct even/odd week.
            if (Oh.AlternateWeek === undefined) {
            } else if (Oh.AlternateWeek == "odd") { // odd week, so its closed on even
                if (((GetWeekofYear(mmdd) + 1) % 2) == 0) {
                    return "<span style='color:red;font-weight:bold'> " + TClosedAW + " this week.<span/>";  // if even week
                }
            } else if (Oh.AlternateWeek == "even") {  // even week, so its closed if odd
                if (((GetWeekofYear(mmdd) + 1) % 2) == 1) {
                    return "<span style='color:red;font-weight:bold'>" + TClosedAW + " this week.<span/>";  // if odd week
                }
            }

            // array is [sunopen, sunclose, monopen, monclose, tueopen, tueclose,.....satopen, satclose]
            var H = Oh.Sc[i].H;  // array indexed by day of week
            if (H == null) return " <span style='color:red;font-weight:bold'> " + TClosed + " today.</span>";  // if no times, its closed
            opentime = H[gDayofWeek * 2]; // open time hhmm
            closetime = H[gDayofWeek * 2 + 1]; // close time hhmm
            opentime2 = 0; closetime2 = 0;
            if (Oh.Sc[i].H2 != null) {  // if there is H 2nd shift
                opentime2 = Oh.Sc[i].H2[gDayofWeek * 2];
                closetime2 = Oh.Sc[i].H2[gDayofWeek * 2 + 1];
            }
            var openlist; openlist = "";
            // test for open
            if ((hhmm >= opentime) && (hhmm < closetime)) {
                if (opentime == 1 && closetime == 2359) return " <strong><span style='color:green'> " + TOpen + "  </span>24 hours today</strong>";  // special case for open 24 hours
                return " <strong><span style='color:green'> " + TOpen + " </span>till " + VeryShortTime(closetime) + " today</strong>";
            }
            else if ((hhmm >= opentime2) && (hhmm < closetime2))  // 2nd shift for Post Office
                return " <strong><span style='color:green'> " + TOpen + " </span>till " + VeryShortTime(closetime2) + " today</strong>";
            else {
                // closed right now. Find next open time.
                openlist += " <span style='color:red;font-weight:bold'> " + TClosed + ". </span>";
                if (hhmm < opentime) return openlist + " " + TOpens + " today " + VeryShortTime(opentime);
                if (hhmm < opentime2) return openlist + " Reopens today " + VeryShortTime(opentime2);
                //  closed today find next open time
                j = gDayofWeek + 1; if (j == 7) j = 0;
                // if it opens tomorrow
                if (H[j * 2] > 0) return openlist + " " + TOpens + " tomorrow " + VeryShortTime(H[j * 2]);
                // not open tomorrow. find next open day.---------------------  
                // Note - for alternate week.  Check to see if it is open later this week. Then return closed status for next week. we know that this week is ok (because it is checked at the top), so next week must be closed
                if (!(Oh.AlternateWeek === undefined)) {  // if it is an alternate week
                    for (j = gDayofWeek + 1; j < 7; j++) { // ensure we check each day only through the end of this week.
                        if (H[j * 2] > 0) return TOpens + " " + gDayofWeekShort[j] + " " + VeryShortTime(H[j * 2]) + ".";
                    }
                    return TClosedAW + " next week.";
                }
                // not alternate week and not open tomorrow. find next open day - search next 7 days.  
                for (var k = 0; k < 7; k++) {  // ensure we check each day only once
                    j++; if (j == 7) j = 0; // handle day rollover
                    if (H[j * 2] > 0) return openlist + " " + TOpens + " " + gDayofWeekShort[j] + " " + VeryShortTime(H[j * 2]);
                } // find open day
            }
        }
    }
    return " <span style='color:red'> Closed. </span>";
}

////////////////////////////////////////////////////////////////////////////////////////
//  IsClosed - returns true if today is a closed date
//  entry   Array of closed dates, usually holidays
//          mmdd = date
//  exit    True if closed today, else false
function IsClosed(CA, mmdd) {
    if (CA == null) return false; // not closed
    for (var i = 0; i < CA.length; i++) { // loop through the closed array
        if (mmdd == CA[i]) return true;
    }
    return false;
}


///////////// OPEN HOURS DETAIL //////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////
// ShowOpenHours - shows whether something is open or closed
// openHours (global) is a string consisting of strings (rows) of store hours, separated by /n.
// each string is: name,Suntime,Montime,Tuetime,Wedtime,Thurtime,Fritime,Sattime,closedholidays
//   where xxxtime = hhmm-hhmm in 24 hour format. closedholidays = mmdd/mmdd/mmdd...
function ShowOpenHoursPage() {
    var openlist;
    gShowAllOpenHours = false;
    SetPageHeader("Open Hours");
    ShowPage("openhourspage");
    InitializeDates(0);
    ShowOpenHoursTable(gShowAllOpenHours);
    return;
}

/////////////////////////////////////////////////////////////////////////////////////////
// ShowOpenHours - Displays all businesses in the OpenHours Object
//  Entry showall = true to show all dates, false to only show hours for current dates

function ShowOpenHoursTable(showall) {
    var table;
    table = document.getElementById("openhourstable");
    gTableToClear = "openhourstable";
    clearTable(table);
    openlist = "";

    // loop through the openHours array for each business
    var i;
    for (i = 0; i < OpenHours.length; i++) {
        var Oh = OpenHours[i];  // entry for 1 business
        var openlist = FormatOneBusiness(Oh, gMonthDay, showall);
        var row = table.insertRow(-1);
        var cell = row.insertCell(0);
        cell.innerHTML = openlist;
        //cell.style.border = "solid gray";
        cell.id = i;
        cell.onclick = function () { ShowOneBusinessFullPage(this.id) }
    }
    return;
}

//function showdiv(id) {
//    var i = Number(id);
//    alert(OpenHours[i].Name + "\n" + OpenHours[i].Desc + "\n" + OpenHours[i].Addr) 
//}

//////////////////////////////////////////////////////////////////////////////////////
// FormatOneBusiness - formats one OpenHours object
//  Entry   Oh = one OpenHours object,  showall = true for all dates
//  Exit    returns html for a table entry       
//  Note:   If open=1 and close=2359, this will display "24 hours".
//
function FormatOneBusiness(Oh, mmdd, showall) {
    var showicon = "<i class='material-icons bizicon'>store</i> ";
    if (Oh.Icon != null) showicon = "<i class='material-icons bizicon'>" + Oh.Icon + " </i> ";
    var openlist = "<div style='background-color:lightblue;padding:6px'><span style='font-weight:bold;font-size:18px;color:blue'>" +
        "<img style='float:right' src='" + Oh.Img + "' width='33%'>" + showicon + RemoveTags(Oh.Name) + "<br/>" + GetOpenStatus(Oh, mmdd, gTimehhmm) + " </span></div>";
    if (showall) openlist += Oh.Desc + "<br/>" + Oh.Addr + "<br/>";
    openlist += "<div style=margin:8px>";
    var mmdd7 = Bumpmmdd(mmdd, 7);  // 7 days after

    // loop through the Oh.Sc entries. Each entry is for 1 date range.
    // We could hit multiple date ranges
    var nr = 0; // number of ranges
    for (var i = 0; i < Oh.Sc.length; i++) {
        if (showall || ((mmdd7 >= Oh.Sc[i].From) && (mmdd <= Oh.Sc[i].To)) ||
            ((Oh.Sc[i].From > Oh.Sc[i].To) && ((mmdd <= Oh.Sc[i].To) || (mmdd7 >= Oh.Sc[i].From)))) {  // if we are in range
            // print date range if there is > 1  (Oh.Sc.length>1)
            if (showall || (nr > 0)) openlist += "<strong>Open " + formatDate(Oh.Sc[i].From) + " - " + formatDate(Oh.Sc[i].To) + ":</strong><br/>";
            var H = Oh.Sc[i].H; // H is the hours array, indexed by day of week*2
            var H2 = Oh.Sc[i].H2; // 2nd hours
            nr = nr + 1;

            // Alternate week check.  Skip if not the correct even/odd week, just skip listing it.
            if (Oh.AlternateWeek === undefined) {
            } else if (Oh.AlternateWeek == "odd") { // odd week, so its closed on even
                if (((GetWeekofYear(mmdd) + 1) % 2) == 0) {
                    openlist += "Closed this week.<br/>";  // if even week
                    continue;
                }
            } else if (Oh.AlternateWeek == "even") {  // even week, so its closed if odd
                if (((GetWeekofYear(mmdd) + 1) % 2) == 1) {
                    openlist += "Closed this week.<br/>";  // if odd week
                    continue;
                }
            }

            // loop through Sun - Sat, listing hours each week.
            for (var j = 0; j < 7; j++) {
                var opentimetoday = H[j * 2]; // hhmm-hhmm open today
                var closetimetoday = H[j * 2 + 1]; // hhmm-hhmm open today
                if (opentimetoday > 0) {
                    if (j == gDayofWeek) openlist += "<strong>"; // bold today
                    if (opentimetoday == 1 && closetimetoday == 2359) openlist += "<nobr>" + gDayofWeekShort[j] + ": 24 hours";
                    else openlist += "<nobr>" + gDayofWeekShort[j] + ":" + VeryShortTime(opentimetoday) + "-" + VeryShortTime(H[j * 2 + 1]);
                    if (H2 != null) {
                        if (H2[j * 2] > 0) openlist += ", " + VeryShortTime(H2[j * 2]) + "-" + VeryShortTime(closetimetoday);
                    }
                    openlist += "</nobr>";
                    if (j == gDayofWeek) openlist += "</strong>";
                    if (j < 6) openlist += ", ";
                }
            } // for loop for each day
            openlist += "<br/>";

        }
    } // end for  for 1 date range

    // find any closed dates in this week range and list them
    var closedlist = "";
    if (Oh.Closed != null) {
        for (var i = 0; i < Oh.Closed.length; i++) {
            if ((Oh.Closed[i] >= mmdd) && (Oh.Closed[i] <= mmdd7)) closedlist += "this " + gDayofWeekShort[GetDayofWeek(Oh.Closed[i])] + " " + formatDate(Oh.Closed[i]) + ", ";
            else if (showall) closedlist += gDayofWeekShort[GetDayofWeek(Oh.Closed[i])] + " " + formatDate(Oh.Closed[i]) + ", ";
        }
        if (closedlist != "") openlist += "<span style='color:red;font-weight:bold'>Closed </span>" + closedlist + "<br/>";
    }
    // buttons for call and web site
    return openlist + "</div>&nbsp&nbsp " +
        "<button><a style='display:normal;text-decoration:none;' href='tel:" + Oh.Phone + "'>" + Oh.Phone + "</a></button>&nbsp&nbsp" +
        "<button onclick='window.open(\"" + Oh.Href + "\", \"_blank\", \"EnableViewPortScale=yes\");'>Web</button>&nbsp&nbsp" +
        "<button onclick='window.open(\"" + Oh.Map + "\", \"_blank\");'>Map</button>";
    // "<a style='display:normal;text-decoration:none;background-color:#E0E0E0;width:300px;' href='" + Oh.Href + "'>&nbsp Web Site &nbsp</a>&nbsp&nbsp&nbsp&nbsp" +
    //    "<a style='display:normal;text-decoration:none;background-color:#E0E0E0;width:300px;' href='" + Oh.Map + "'>&nbsp Map &nbsp</a>";
} // end of function

//////////////////////////////////////////////////////////////////////////////////////
// ShowOneBusinessFullPage - formats one OpenHours object for a full page display
//  Entry   id = index into the OpenHours object
//  Exit    fills out and shows businesspage.                          
function ShowOneBusinessFullPage(id) {

    ShowPage("businesspage");
    InitializeDates(0);
    var Oh = OpenHours[Number(id)]; // one business
    var t = RemoveTags(Oh.Name);
    var mmdd = gMonthDay;
    if (t == "Store") t = "General Store";
    SetPageHeader(t);
    //document.getElementById("businesspageh1").innerHTML = "<button class='buttonback' onclick='ShowOpenHoursPage()'>&larr;BACK</button>" + t;
    var showicon = "<i class='material-icons bizicon'>store</i> ";
    if (Oh.Icon != null) showicon = "<i class='material-icons bizicon'>" + Oh.Icon + "</i> ";
    var openlist = "<p style='font-weight:bold;font-size:medium'>&nbsp&nbsp&nbsp " + showicon + t + ": " + GetOpenStatus(Oh, mmdd, gTimehhmm) + " </p>";
    openlist += "<p style='margin:10px'><img src='" + Oh.Img + "' width='" + ((window.screen.width>1000) ? "40" : "100" ) + "%'></p>";
    openlist += "<div style='font-size:small'><div style='width:100%;background-color:lightblue;padding:6px'>DESCRIPTION</div><p style='margin:10px'>" + 
        Oh.Desc +
        "<br/><button><a style='display:normal;text-decoration:none;' href='tel:" + Oh.Phone + "'>&nbsp Call " + Oh.Phone + "&nbsp</a></button>&nbsp&nbsp " +   
        "</p><div style='width:100%;background-color:lightblue;padding:6px'>ADDRESS</div><p style='margin:10px'>" +
        "<button onclick='window.open(\"" + Oh.Map + "\", \"_blank\");'>&nbsp Map &nbsp</button>&nbsp&nbsp " +
        Oh.Addr + "</p>" +
        "<div style='width:100%;background-color:lightblue;padding:6px'>OPEN HOURS</div><p style='margin:10px'>";

    var mmdd7 = Bumpmmdd(mmdd, 7);  // 7 days after

    // loop through the Oh.Sc entries. Each entry is for 1 date range.
    // We could hit multiple date ranges
    var nr = 0; // number of ranges;

    for (var i = 0; i < Oh.Sc.length; i++) {
        var active = false;
        if (((mmdd7 >= Oh.Sc[i].From) && (mmdd <= Oh.Sc[i].To)) ||
            ((Oh.Sc[i].From > Oh.Sc[i].To) && ((mmdd <= Oh.Sc[i].To) || (mmdd7 >= Oh.Sc[i].From))))
            openlist += "<span style='color:green;font-weight:bold'>";
        else openlist += "<span style='color:gray;font-weight:bold'>";
        // print date range if there is > 1  (Oh.Sc.length>1)
        openlist += "Open " + formatDate(Oh.Sc[i].From) + " - " + formatDate(Oh.Sc[i].To) + ":</span><br/>";
        var H = Oh.Sc[i].H; // H is the hours array, indexed by day of week*2
        var H2 = Oh.Sc[i].H2; // 2nd hours
        nr = nr + 1;
        // loop through Sun - Sat
        for (var j = 0; j < 7; j++) {
            var opentimetoday;
            opentimetoday = H[j * 2]; // hhmm-hhmm open today
            if (opentimetoday > 0) {
                if (j == gDayofWeek) openlist += "<strong>";  // bold today
                openlist += "<nobr>" + gDayofWeekShort[j] + ":" + VeryShortTime(opentimetoday) + "-" + VeryShortTime(H[j * 2 + 1]);
                if (H2 != null) {
                    if (H2[j * 2] > 0) openlist += ", " + VeryShortTime(H2[j * 2]) + "-" + VeryShortTime(H2[j * 2 + 1]);
                }
                openlist += "</nobr>";
                if (j == gDayofWeek) openlist += "</strong>";
                if (j < 6) openlist += ", ";
            }
        } // for loop for each day
        openlist += "<br/>";
    } // end for  for 1 date range

    // find any closed dates in this week range and list them
    var closedlist = "";
    if (Oh.Closed != null) {
        for (var i = 0; i < Oh.Closed.length; i++) {
            if ((Oh.Closed[i] >= mmdd) && (Oh.Closed[i] <= mmdd7)) closedlist += "this " + gDayofWeekShort[GetDayofWeek(Oh.Closed[i])] + " " + formatDate(Oh.Closed[i]) + ", ";
            else closedlist += gDayofWeekShort[GetDayofWeek(Oh.Closed[i])] + " " + formatDate(Oh.Closed[i]) + ", ";
        }
        if (closedlist != "") openlist += "<span style='color:red;font-weight:bold'>Closed </span>" + closedlist + "<br/></p>";
    }
    // buttons for web site
    openlist += "<div style='width:100%;background-color:lightblue;padding:6px;margin-bottom:10px'>MORE</div>" +
        "<button onclick='window.open(\"" + Oh.Href + "\", \"_blank\", \"EnableViewPortScale=yes\");'>&nbsp Web Site &nbsp</button></div>";

    document.getElementById("businesspagediv").innerHTML = openlist;
} // end of function
//  ShowYRHours - show year - round hours. Toggles gShowAllOpenHours.
function ShowYRHours() {
    gShowAllOpenHours = !gShowAllOpenHours;
    ShowOpenHoursTable(gShowAllOpenHours);
}

//////////////////////////////////////////////////////////////////////////////////
// formatDate - transforms mmdd or yymmdd into string: mm/dd
//  entry   integer date in mmdd form
//  exit    string: mm/dd
function formatDate(nmmdd) {
    if (nmmdd > 9999) nmmdd = nmmdd % 10000; // remove year
    return Math.floor(nmmdd / 100).toFixed(0) + "/" + (nmmdd % 100).toFixed(0);
}

//////////////////////////////////////////////////////////////////////////
// parseOpenHours - parse the json structure in 'openhoursjson' and populate OpenHours
//  entry   local storage "openhoursjson"
//  exit    OpenHours object array is set
function ParseOpenHours() {
    var s = localStorage.getItem("openhoursjson");
    if (s != null) {
        try {
            OpenHours = JSON.parse(s);  // parse it  
        } catch (e) {
            alert("Error in OPEN HOURS data: " + e);
            gForceCacheReload = true;
        }
    } else gForceCacheReload = true;
}

//</script>


//  ******  **  **  ******  *    *  ******  ******
//  **      **  **  **      * *  *    **    **
//  ******   *  *   ******  *  * *    **    ******
//  **        **    **      *   **    **        **
//  ******    **    ******  *    *    **    ******



//<!-- COMING EVENTS ------------------------------------------------------------------------------------->
//<script>
//========= COMING EVENTS ===========================================================================

///////////// COMING EVENTS MAIN PAGE /////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////
// DisplayComingEvents MAIN PAGE - display the events in the 'comingevents'  or 'comingactivities' 
//          local storage object on the MAIN PAGE
//      Displays all activities or events for a day.
//  Entry   CE = string of coming events: rows (separated by \n) 1 for each event or activity
//              mmdd;hhmm;hhmm;t;title;location;sponsor;info
//          label = "event" or "activity"
//  Exit    returns the information to display on the main screen
function DisplayNextEvents(CE) {
    var datefmt = ""; // formatted date and event list
    var iCE; // iterator through CE
    var aCE; // CE split array 
    var aCEyymmdd; // yymmdd of Calendar Entry
    var DisplayDate = 0; // event date we are displaying
    var nEvents = 0; // number of events displayed
    var CEvent = "";

    if (CE === null) return;
    // break CE up into rows
    CE = CE.split("\n");  // break it up into rows
    if (CE == "") return;
    var yymmddP6 = IncrementDate(6); // add 6 to date

    // roll through the next 30 days
    for (iCE = 0; iCE < CE.length; iCE++) {
        if (CE[iCE] == "") continue; // skip blank lines
        aCE = CE[iCE].split(';');  // split the string
        if (BadEvent(aCE)) continue; // must have 6 entries

        //  advance schedule date to today
        aCEyymmdd = Number(aCE[0]);
        if (aCEyymmdd < gYYmmdd) continue; // not there yet.
        // if the entry is for today and it is done, skip it
        if (aCEyymmdd == gYYmmdd && Number(aCE[2]) < gTimehhmm) continue; // if today and it is done, skip it
        // found it
        //if (aCEmonthday != gMonthDay && datefmt != "") return datefmt; // don't return tomorrow if we all the stuff for today
        if ((aCEyymmdd != DisplayDate) && (nEvents >= 2) && (datefmt != "")) return datefmt; // don't return tomorrow if we all the stuff for today

        if (gIconSwitch == 1) CEvent = FormatEvent(aCE, "14");
        else CEvent = aCE[4];

        // if Today: bold time. if current, make time green.  
        if (aCEyymmdd == gYYmmdd) {
            if (datefmt == "") datefmt += "<span style='color:green'><strong>TODAY</strong></span><br/>";  // mark the 1st entry only as TODAY
            if (Number(aCE[1]) <= gTimehhmm) {
                datefmt += "&nbsp;<span style='font-weight:bold;color:green'>" + VeryShortTime(aCE[1]) + "-" + VeryShortTime(aCE[2]) + "</span>: " + CEvent + " @ " + aCE[5] + "<br/>";
                TXTS.Next = " now, " + aCE[4] + " at " + aCE[5] + ".";
            } else {
                datefmt += "&nbsp;<strong>" + VeryShortTime(aCE[1]) + "-" + VeryShortTime(aCE[2]) + "</strong>&nbsp;" + CEvent + " @ " + aCE[5] + "<br/>";
                if (nEvents < 1) TXTS.Next = " at " + FormatTime(aCE[1]) + ", " + aCE[4] + " at " + aCE[5] + "."; // text to speech
            }
            //nEvents = 99; // ensure only today
            nEvents++;  // count it
            DisplayDate = aCEyymmdd;
            continue;
        }
        // if not today, display day of week. Do NOT display > 6 days (1 week) ahead because it is too confusing.
        if (aCEyymmdd != DisplayDate) {
            if (nEvents >= 3) break;  // don't start a new date if we have shown 3 events
            if (aCEyymmdd == (gYYmmdd + 1)) datefmt += "<strong>TOMORROW</strong><br/>";
            else if (aCEyymmdd <= yymmddP6) datefmt += "<strong>" + gDayofWeekName[GetDayofWeek(aCE[0])] + "</strong><br/>";  // fails on month chagne
            //else datefmt += "<strong>" + gDayofWeekShort[GetDayofWeek(aCEyymmdd)] + " " + aCE[0].substring(2, 4) + "/" + aCE[0].substring(4, 6) + "</strong><br/>";
            else break; // if >6 days, don't show it.
        }
        // Not today: display at least 3 events. Always Display ALL events for a day. 
        datefmt += "&nbsp;" + VeryShortTime(aCE[1]) + "-" + VeryShortTime(aCE[2]) + ": " + CEvent + " @ " + aCE[5] + "<br/>";
        if (nEvents < 1) TXTS.Next = gDayofWeekName[GetDayofWeek(aCE[0])] + " at " + FormatTime(aCE[1]) + ", " + aCE[4] + " at " + aCE[5] + "."; // text to speech
        DisplayDate = aCEyymmdd;
        nEvents++; // count the events
        //if (nEvents >= 3) break; // only exit after full days
    }
    return datefmt; // end case
}


///////////// COMING EVENTS DETAIL PAGE //////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////
// DisplayComingEventsPage(type)
//  entry   type = 'events' or 'activities'
var EventFilter = ""; //letter to filter for
var EventDisp = ""; // event display type, L, W, M

function DisplayComingEventsPageE() { DisplayComingEventsPage("events");}
function DisplayComingEventsPageA() { DisplayComingEventsPage("activity"); }

function DisplayComingEventsPage(type) {
    localStorage.setItem("eventtype", type);
    document.getElementById("othercalendars").setAttribute("style", "display:none");
    ShowPage("comingeventspage");
    InitializeDates(0);
    EventFilter = ""; // clear filter
    document.getElementById("eventA").style.fontWeight = "bold";
    if (type == "events") {
        SetPageHeader("Events");
        //document.getElementById("comingeventsH1").setAttribute('style', 'display:normal;');
        document.getElementById("showevents").setAttribute('style', 'display:normal;');
        document.getElementById("showactivities").setAttribute('style', 'display:none;');
        //document.getElementById("comingactivitiesH1").setAttribute('style', 'display:none;');
    } else {
        SetPageHeader("Activities");
        //document.getElementById("comingeventsH1").setAttribute('style', 'display:none;');
        document.getElementById("showevents").setAttribute('style', 'display:none;');
        document.getElementById("showactivities").setAttribute('style', 'display:normal;');
        //document.getElementById("comingactivitiesH1").setAttribute('style', 'display:normal;');
        MarkPage("v");
    }
    DisplayComingEventsList(GetEvents())
}

////////////////////////////////////////////////////////////////////////////////////////
// GetEvents gets the events and returns them, based on the activities parameter in the url
function GetEvents() {
    if (localStorage.getItem("eventtype") == "events") {
        return localStorage.getItem("comingevents");  //display stored data in case we can't successfully reload the comingevents cache
    } else {
        return localStorage.getItem("comingactivities");  //display stored data in case we can't successfully reload the comingevents cache\
    }
}

function ShowOtherCalendars() {
    document.getElementById("othercalendars").setAttribute("style", "display:normal");
}

////////////////////////////////////////////////////////////////////////////////
// SetEventFilter - sets the filter letter and regenerates the display
//  Entry   f = A, M, S, E, A, C, G, O
function SetEventFilter(f) {
    flist = ["", "eventA", "M", "eventM", "S", "eventS", "E", "eventE", "A", "eventAA", "C", "eventAC", "G", "eventAG", "O", "eventAO"];
    var fw;
    // bold the selection
    for (var i = 0; i < flist.length; i += 2) {
        if (flist[i] == f) fw = "bold";
        else fw = "normal";
        document.getElementById(flist[i + 1]).style.fontWeight = fw;
    }

    EventFilter = f;
    switch (EventDisp) {
        case "L": DisplayComingEventsList(GetEvents()); break;
        case "W": DisplayComingWeek(GetEvents()); break;
        case "M": DisplayComingMonth(GetEvents()); break;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////
// DisplayComingEventsList - display the events in the CE string, which is a copy of the comingevents txt file
//  or the 'ACTIVITIES' section of the comingevents text file. These are cached in the 'comingevents' 
// or 'comingactivities' localStorage items. 
//  entry   CE is a single big string containing multiple lines, so we split the string by \n.
//          each CE line is: mmdd,starthhmm,endhhmm,key,title,location,sponsor,info,{i=icon,...}
//                              0,    1    ,    2  , 3 ,  4  ,    5   ,   6   ,  7 ,  8
function DisplayComingEventsList(CE) {
    var i;
    var row;
    var col;
    var idayofweek;
    var lastweek = 0;
    var previouseventdate; // date of previous event
    var datefmt; // formatted date
    var table // ref to table
    var oldrow;  // previous row
    InitializeDates(0);
    EventDisp = "L";
    table = document.getElementById("comingeventstable");
    gTableToClear = "comingeventstable";
    var iCE; // iterator through CE
    iCE = 0;
    var aCE; // CE split array 
    if (CE == null) return;
    CE = CE.split("\n");  // break it up into rows
    if (CE == "") return;
    clearTable(table); // clear table
    table.deleteRow(-1);

    // add a new week header
    row = table.insertRow(-1);
    row.style.border = "solid thin gray";
    row.style.backgroundColor = "lightblue";
    row.style.fontWeight = "bold";
    col = row.insertCell(0); col.innerHTML = "Day";
    col = row.insertCell(1); col.innerHTML = "Time";
    col = row.insertCell(2); col.innerHTML = "Event";
    col = row.insertCell(3); col.innerHTML = "Location";

    // calculate end month day. 6 for events, 1 for activities.
    //var endyymmdd;
    //if (localStorage.getItem("eventtype") == "events") endyymmdd = BumpyymmddByMonths(gYYmmdd, 6);
    //else endyymmdd = BumpyymmddByMonths(gYYmmdd, 1);
    var thisweek = GetWeekofYear(gMonthDay); // this week #

    // roll through the CE array.  Dates are yymmdd
    for (iCE = 0; iCE < CE.length; iCE++) {
        if (CE[iCE] == "") continue; // skip blank lines
        aCE = CE[iCE].split(';');  // split the string
        if (BadEvent(aCE)) continue; // must have 6 entries
        if ((EventFilter != "") && (EventFilter != aCE[3])) continue;  // skip entry if it doesnt match
        //  advance schedule date to today
        var CEyymmdd = Number(aCE[0]); // yymmdd
            // ALTERNATE on the fly year addition
            // if(CE[iCE].substr(0,15) == "0101;0000;0000;") CEyear = Number(aCE[4]);
            // CEyymmdd = CEYear*10000;
        //if (CEyymmdd > endyymmdd) return; // past end date (one month)  list the entire table now
        if (CEyymmdd < gYYmmdd) continue; // if before today
        if ((CEyymmdd == gYYmmdd) && (Number(aCE[2]) < (gTimehhmm + 10))) continue; // end time not reached.
        // found it
        datefmt = aCE[0].substring(2, 4) + "/" + aCE[0].substring(4, 6);

        //var dd = new Date(datefmt + "/" + (CEyymmdd%10000));  // date object for Calendar Entry WHY?????
        iweek = GetWeekofYear(CEyymmdd);
        idayofweek = GetDayofWeek(CEyymmdd);

        // add a row for new week. won't work if schedule days are both same day of week
        if (iweek != lastweek) {
            row = table.insertRow(-1);
            row.style.backgroundColor = "lightblue";
            col = row.insertCell(0);
            // test for this week
            if (iweek == thisweek) col.innerHTML = "This Week";
            else col.innerHTML = "";
            col.colSpan = "4";
        }

        // add a new table row
        row = table.insertRow(-1);
        row.style.border = "thin solid gray";//
        if ((CEyymmdd == gYYmmdd) && (Number(aCE[2]) > (gTimehhmm + 10))) row.style.fontWeight = "bold"; // end time not reached.
        col = row.insertCell(0);
        if (aCE[0] != previouseventdate) col.innerHTML = gDayofWeekShort[idayofweek] + " " + datefmt; // day of week
        else {
            col.innerHTML = "";
            oldrow.style.borderBottomColor = "lightblue";
        }
        col.style.backgroundColor = "azure";
        col.style.fontWeight = "bold";
        col = row.insertCell(1);
        col.innerHTML = ShortTime(aCE[1]) + "-" + ShortTime(aCE[2]); // compressed tim
        var col2 = row.insertCell(2);
        col2.innerHTML = FormatEvent(aCE, "16");//event
        //col.onclick = function(){tabletext(this);}
        col = row.insertCell(3); col.innerHTML = aCE[5];//where
        var color;
        color = eventcolor(aCE[3]);
        col2.style.color = color;
        col.style.color = color;
        row.id = aCE[0] + aCE[1];  // id = 1602141300  i.e. yymmddhhmm
        row.onclick = function () { tabletext(this.id) }
        oldrow = row; 
        lastweek = iweek;
        previouseventdate = aCE[0];
    } // end loop through CE
    document.getElementById("locations").innerHTML = LSget("locations");
}

//////////////////////////////////////////////////////////////////////
//  FormatEvent - add icon if switch is on
//  Entry   event array, font size in pixels
//          each array   is: mmdd,starthhmm,endhhmm,key,title,location,sponsor,info,{i=icon,...}
//                              0,    1    ,    2  , 3 ,  4  ,    5   ,   6   ,  7 ,  8
//  Exit    html for event, includes icon if switch is on
function FormatEvent(aCE, fontsize) {
    // iconlist:   keyword, iconname, ...
    var iconlist = ["meeting", "people", "film", "theaters","music", "music_note", "golf", "golf_course", "drop-off", "file_download", "market", "shopping_cart",
        "concert", "music_note", "karaoke", "mic", "sale", "shopping_cart", "bazaar", "shopping_cart", 
        "fitness", "fitness_center", "craft", "palette", "art", "palette", "dinner", "restaurant_menu", "luncheon", "restaurant_menu"
    ];
    var ev = aCE[4];
    if (ev == null || ev == "") {
        alert("Event error - no type: " + aCE[1] + " " + aCE[2] + aCE[3]);
        return "";
    }
    var icon;
    if (!gEventIcons) return ev;  // if no icons
    // 1. look for user specified icon in aCE[8]
    if (aCE.length >= 9) {
        if (aCE[8].substr(0, 1) == "{") {
            try {
                var us = JSON.parse(aCE[8]); // parse it
                if (us.i != null) return '<i class="material-icons">' + us.i + '</i> ' + ev;
            } catch (err) {
            }
        }
    }
    
    // default icon based on the key: E, S, A, C, G, M
    switch (aCE[3]) {
        case "E": icon = "mood"; break; // special events
        case "S": icon = "music_note"; break; // show
        case "A": icon = "directions_run"; break; // activity
        case "C": icon = "palette"; break; // craft
        case "G": icon = "games"; break; // game
        default: icon = "people";
    }
    // find an icon based on keyword in the title
    var i;
    var evlc = ev.toLowerCase();
    for (i = 0; i < iconlist.length; i = i + 2) {
        if (evlc.indexOf(iconlist[i]) >= 0) {
            icon = iconlist[i + 1];
            break;
        }
    }
    return '<i class="material-icons">' + icon + '</i> ' + ev;
}


///////////////////////////////////////////
//  tabletext - display all details for the row or item that was clicked. Works for list, week, and month views.
//  tc = cell id: date (mmdd) time (hhmm) as a string. mmdd9999 to match all times on mmdd. mmddhh99 to match all minutes
//  The date and time are used to look up the entry in the CE array.
//  Each table entry has an id which is the index into the CE array, and onclick=AddToCal.
function tabletext(tc) {
    //alert(tc);
    var nc = 0;
    var d = tc.substr(0, 6);  // yymmdd part of id
    var t = tc.substr(6, 4); // hhhmm part of id. could be hh99 or 9999
    var as = "Tap button to add to your ";
    if (!isPhoneGap()) as += "Google ";
    as += "calendar.<br/> <table style='border:thin solid black;border-collapse:collapsed'>";
    var CE = GetEvents().split("\n");
    for (iCE = 0; iCE < CE.length; iCE++) {
        if (CE[iCE] == "") continue; // skip blank lines
        CE[iCE] = CE[iCE].replace("&lt;", "<"); // remove html special <
        CE[iCE] = CE[iCE].replace("&gt;", ">"); // remove html special >
        CE[iCE] = CE[iCE].replace("&amp;", "&"); // remove html special &
        var aCE = CE[iCE].split(';');  // split the string
        if (d < aCE[0]) break;
        if (d != aCE[0]) continue;
        var t99 = aCE[1].substr(0, 2) + '99'; // hh99
        if ((t == aCE[1]) || (t == '9999') || (t == t99)) {
            nc++;
            // create table entry. id = the numeric index into the CE array
            as += "<tr id='" + iCE.toFixed() + "'><td style='border:thin solid black'><strong>" +
                formatDate(aCE[0]) + " " + VeryShortTime(aCE[1]) + "-" + VeryShortTime(aCE[2]) + ":</strong> " +
                 aCE[4] + " at " + aCE[5] + "<br/>Sponsor: " + CreateLink(aCE[6]) + "<br/>";
            // to include a link: must start with http and be at the end of the element and not have ' or "
            if (aCE.length >= 8) as += CreateLink(aCE[7]) + "<br>";
            as += "<button onclick='AddToCal(" + iCE.toFixed() + ")'>Add to Calendar</button></td></tr>";
        }

    }
    if (nc == 0) return;
    as += "</table>";
    Dialog(as, "Schedule Detail");
    //alert(as);
}

///////////////////////////////////////////////////////////////////////////
//  CreateLink - changes an http string into a hyperlink <a href='http...xxxx'/> string
//  Entry   string with http in it
//  Exit    string with hyperlink
function CreateLink(s) {
    if (s.indexOf("<a") > 0) return s;  // don't touch if it has an a already
    var i = s.indexOf("http");
    if (i > 0) {
        var j = s.indexOf(" ", i);  // trailing space after the hyperlink
        if (j > 0) {
            var lk = s.substring(i, j);
            s = s.substr(0, i) + lk.link(lk) + s.substr(j);
        } else {
            var lk = s.substr(i);
            s = s.substr(0, i) + lk.link(lk);
        }
        s = s.replace(">http", ',target="_blank">http');  // target a different 
    }
    return s;
}

////////////////////////////////////////////////////////
//  Add to Cal -  Add to a calendar
//  Non-phonegap: use link to google calendar.
//  Phonegap:  call plugin.
//  Entry: id = the index into the CE array of the selected event
function AddToCal(id) {
    ModalClose();
    MarkPage("d");
    var CE = GetEvents().split("\n");
    var aCE = CE[Number(id)].split(';');
    // prep some variables  Date(year, m, d, h, m, 0, 0
    var y = Number(aCE[0].substring(0, 2)) + 2000; // year
    var m = Number(aCE[0].substring(2, 4)) - 1; // month
    var d = Number(aCE[0].substring(4, 6)); // day
    var startDate = new Date(y, m, d, Number(aCE[1].substring(0, 2)), Number(aCE[1].substring(2, 4)), 0, 0); // beware: month 0 = january, 11 = december
    var endDate = new Date(y, m, d, Number(aCE[2].substring(0, 2)), Number(aCE[2].substring(2, 4)), 0, 0);
    var title = aCE[4];
    var eventLocation = aCE[5];
    var notes = "";

    // NOT PHONEGAP - use google calendar  http://www.google.com/calendar/event?
    //    action=TEMPLATE&text=title&dates=yyyymmddThhmmssZ/yyyymmddThhmmssZ&details=xxx&location=xxx
    //      NOTE: for google link: convert to UTC, change spaces to %20.
    //if (!isPhoneGap() || isAndroid()) { //NOT PHONEGAP  OR   Phonegap and Andriod
    if (!isPhoneGap() ) { //NOT PHONEGAP 
        title = title.replace(/ /g, '%20');
        eventLocation = eventLocation.replace(/ /g, '%20');
        //https://calendar.google.com/calendar/render?action=TEMPLATE&text=Farm+Work+Party&dates=20160525T160000Z/20160525T190000Z&location=A
        //           var link = "http://www.google.com/calendar/event?action=TEMPLATE&text=" + title + 
        var link = "http://calendar.google.com/calendar/render?action=TEMPLATE&text=" + title +
            "&dates=" + y + Leading0(startDate.getUTCMonth() + 1) + Leading0(startDate.getUTCDate()) +
            'T' + Leading0(startDate.getUTCHours()) + Leading0(startDate.getUTCMinutes()) + "00Z/" +
             gYear + Leading0(endDate.getUTCMonth() + 1) + Leading0(endDate.getUTCDate()) +
            'T' + Leading0(endDate.getUTCHours()) + Leading0(endDate.getUTCMinutes()) + "00Z" +
            "&location=" + eventLocation;
        //alert(link);
        window.open(link, "_blank");
    return;
   }

    // PHONEGAP: IOS create an event interactively using the phonegap plugin
    var success = function (message) { };
    var error = function (message) { alert("Unable to add to calendar."); };
    window.plugins.calendar.createEventInteractively(title, eventLocation, notes, startDate, endDate, success, error);
}

///////////////////////////////////////////////////////////////////////////////////////////////
// DisplayComingWeek - display the events in the CE structure in a 1 week form
// CE = string of events, \n separated.
// changed to include year. 9/29/16.
function DisplayComingWeek(CE) {

    var i, h;
    var row;
    var col;
    var startyymmdd;
    var table // ref to table
    MarkPage("3");
    EventDisp = "W";
    table = document.getElementById("comingeventstable");
    var iCE; // iterator through CE
    iCE = 0;
    var aCE; // CE split array 
    var CE;

    if (CE == null) return;
    CE = CE.split("\n");  // break it up into rows

    // build table
    clearTable(table);
    table.deleteRow(-1);

    var yymmdd = Bumpyymmdd(gYYmmdd, -gDayofWeek) ;  // reset mmdd to 1st day of week

    // loop for each week
    for (var nw = 0; nw < 2; nw++) {
        startyymmdd = yymmdd;
        // add a new week header
        row = table.insertRow(-1);
        row.style.border = "solid thin gray";
        row.style.border.width = 1;
        row.style.backgroundColor = "lightblue";
        row.style.fontWeight = "bold";

        col = row.insertCell(0); col.innerHTML = ""; col.style.width = "5%"; //
        for (i = 0; i < 7; i++) {
            col = row.insertCell(-1);
            col.innerHTML = gDayofWeekShort[i] + "<br/>" + (yymmdd % 100).toFixed(0);
            col.style.width = "13%"// 
            if (yymmdd == gYYmmdd) col.style.backgroundColor = "yellow"; // color today yellow
            yymmdd = Bumpyymmdd(yymmdd, 1);
        }


        // build the week table with all hour rows and day columns, but no events

        for (h = 7; h < 23; h++) {  // for hours 7 - 22
            row = table.insertRow(-1);
            //row.style.border = "thin solid blue";
            col = row.insertCell(0);
            // time row
            if (h < 13) col.innerHTML = h + 'am';
            else col.innerHTML = (h - 12) + 'pm';
            if (gTimehh == h) col.style.backgroundColor = "pink";
            else col.style.backgroundColor = "azure";
            col.style.border = "thin solid lightblue";
            yymmdd = startyymmdd;
            // add day columns (add  one for each hour row)
            for (i = 0; i < 7; i++) {
                col = row.insertCell(-1);
                var id = yymmdd.toFixed(0) + Leading0(h) + "99"; //yymmddhh99
                col.id = id;
                col.onclick = function () { tabletext(this.id) }
                col.innerHTML = "";
                if (yymmdd == gYYmmdd) {  // highlight today and now
                    if (gTimehh == h) col.style.backgroundColor = "pink";
                    else col.style.backgroundColor = "lightyellow";  // make today yellow
                }
                col.style.border = "thin solid lightblue";
                yymmdd = Bumpyymmdd(yymmdd, 1);
            }
        }

        // roll through the CE array for 7 days and populate the week table with events from CE
        var endyymmdd = yymmdd;  // end day + 1j
        for (iCE = 0; iCE < CE.length; iCE++) {
            if (CE[iCE] == "") continue; // skip blank lines
            aCE = CE[iCE].split(';');  // split the string
            if (BadEvent(aCE)) continue; // must have 6 entries
            var dateCE = Number(aCE[0]); // yymmdd
            if (dateCE >= endyymmdd) break; // past one week
            if (dateCE < startyymmdd) continue; // if before today
            if ((EventFilter != "") && (EventFilter != aCE[3])) continue;  // skip entry if it doesnt match
            // add to entry. entries have an id of: yymmddhh99
            var e = "";
            if (dateCE == gYYmmdd) e = "<strong>";
            if (aCE[1].substring(2, 4) != "00") e = ShortTime(aCE[1]) + " "; // add time if not one the hour
            e += "<span style=color:" + eventcolor(aCE[3]) + ">" + aCE[4] + "</span>";
            var id = aCE[0] + aCE[1].substring(0, 2) + "99"; //id = yymmddhh99
            var c = document.getElementById(id);
            if (dateCE == gYYmmdd) e = "<strong>" + e + "</strong>";
            c.innerHTML += e + "<br/>";
            c.style.backgroundColor = "azure";
            // now the fancy part:  if end time is > 1 hour more than start time, color next blocks if they exist
            var sh = Number(aCE[1].substring(0, 2));  //start hour
            if (sh < 7) sh = 7;
            var eh = Number(aCE[2].substring(0, 2));  //end hour
            if (eh < 7) eh = 7; if (eh > 22) eh = 22;
            // if > 1 hour, color next cell
            for (var i = sh + 1; i < eh; i++) {
                var id = aCE[0] + Leading0(i) + "99";//id = yymmddhh99
                document.getElementById(id).style.backgroundColor = "azure";
            }

        } // end for 1 week

    }  // end for 2 weeks
    return;
} // end function

// Leading4 - returns number n (999 - 9999) as a 4 digit string
function Leading4(n) {
    var s = n.toFixed();
    if (s.length == 4) return s;
    else return "0" + s;
}

///////////////////////////////////////////////////////////////////////////////////////////////
// DisplayComingMonth - display the events in the CE structure in a 1 month form
// CE = string of events, \n separated.
function DisplayComingMonth(CE) {
    var i, w;
    var row;
    var col;
    var table // ref to table
    MarkPage("2");
    EventDisp = "M";
    table = document.getElementById("comingeventstable");
    var iCE; // iterator through CE
    iCE = 0;
    var aCE; // CE split array 
    var CE;

    if (CE == null) return;
    CE = CE.split("\n");  // break it up into rows
    if((CE.length) < 2) return; // not enough data
    var startyymmdd = Bumpyymmdd(gYYmmdd, -gDayofWeek); // back up to beginning of month
    var yymmdd = startyymmdd;
    var yymm = (gYear - 2000) * 100 + gMonth;

    // build table
    clearTable(table);
    table.deleteRow(-1);
    // add a new week header
    row = table.insertRow(-1);
    row.style.border = "solid thin gray";
    row.style.border.width = 1;
    row.style.backgroundColor = "lightblue";
    row.style.fontWeight = "bold";
    col = row.insertCell(0); col.innerHTML = "Sun"; col.style.width = "14%";//
    col = row.insertCell(1); col.innerHTML = "Mon"; col.style.width = "14%";  //
    col = row.insertCell(2); col.innerHTML = "Tue"; col.style.width = "14%"; //
    col = row.insertCell(3); col.innerHTML = "Wed"; col.style.width = "14%";//
    col = row.insertCell(4); col.innerHTML = "Thur"; col.style.width = "14%";//
    col = row.insertCell(5); col.innerHTML = "Fri"; col.style.width = "14%";
    col = row.insertCell(6); col.innerHTML = "Sat"; col.style.width = "14%";
    var numofweeks = 99;

    // build the month table with all rows and columns. Each day has an id of 'yymmdd9999'.
    for (w = 1; w <= numofweeks; w++) { // loop for each week
        var rowN = table.insertRow(-1);
        row = table.insertRow(-1);

        // loop for the 7 days in a week.   day rows with date
        for (i = 0; i < 7; i++) {
            // cell with date
            col = rowN.insertCell(i);
            if (Math.floor(yymmdd / 100) != yymm) { // if not this month
                col.innerHTML = formatDate(yymmdd); // use month 
                //col.style.fontWeight = 'bold';
            }
            else col.innerHTML = (yymmdd % 100).toFixed(0); // this month: show day only
            col.style.color = "darkblue";
            if (yymmdd == gYYmmdd) col.style.backgroundColor = "yellow";
            else col.style.backgroundColor = "azure";
            col.style.border = "thin solid lightblue";
            // cell that will hold the events
            col = row.insertCell(i);
            col.innerHTML = "&nbsp";
            col.style.border = "thin solid lightblue";
            col.style.verticalAlign = "top";
            col.id = yymmdd.toFixed(0) + '9999';  // id = yymmdd9999
            col.onclick = function () { tabletext(this.id) }
            if (yymmdd == gYYmmdd) col.style.backgroundColor = "lightyellow";  // make today yellow
 
            // add elements
            var e = "";
            for (; iCE < CE.length; iCE++) {
                if (CE[iCE] == "") continue; // skip blank lines
                aCE = CE[iCE].split(';');  // split the string
                if (BadEvent(aCE)) continue; // must have 6 entries
                //  advance schedule date to today
                var dateCE = Number(aCE[0]); // yymmdd
                if (dateCE > yymmdd) break; // if past today, exit
                if (dateCE < yymmdd) continue; // if before today, continue
                if ((EventFilter != "") && (EventFilter != aCE[3])) continue;  // skip entry if it doesnt match
                // add to entry 
                e += "<span style=color:" + eventcolor(aCE[3]) + "><strong>" + VeryShortTime(aCE[1]) + "</strong> " +
                      aCE[4] + "</span><br/>";// add time 
            } // end for
            if (e != "") col.innerHTML = e ;
            if (iCE >= CE.length) numofweeks = 0;  // stop week loop
            yymmdd = Bumpyymmdd(yymmdd, 1); // quick bump of yymmdd//
        }
    }

} // end function

//////////////////////////////////////////////////////////////////////////////////////
//  BadEvent - returns true if the event is bad.
//  Entry   aCE = arry of form: mmdd;hhmm;hhmm;t;title;location;sponsor;info;icon object
//  Exit    true if bad, false if good
function BadEvent(aCE) {
    if (isNaN(aCE[0])) return true;
    if (isNaN(aCE[1])) return true;
    if (isNaN(aCE[2])) return true;
    if (aCE.length < 6) return true; // must have 6 entries
    if (aCE.length > 9) return true; // must have 6 entries
    return false;
}


///////////////////////////////////////////////////////////////////////////////////
// eventcolor - return the color for an event, based on the key (M, S, E, A, C, G)
//  exit: color value
function eventcolor(key) {
    switch (key) {
        case "E": return "#0000ff"; break; // special events
        case "S": return "darkred"; break; // entertainment
        case "A": return "blue"; break; // entertainment
        case "C": return "darkred"; break; // entertainment
        case "G": return "darkgreen"; break; // entertainment

        default: return "#000000";
    }
}

///////////////////////////////////////////////////////////////////////////
//  Bumpyymmdd  add DAYS to yymmdd and adjust mm and dd and yyyy
//  entry   mmdd = original yymmdd
//          n = days to add or subtract, up to one month
//  exit    returns new mmdd.   Note than 1231 rolls to 0101;
function Bumpyymmdd(mmdd, n) {
    if (n == 0) return mmdd;
    var yyyy = gYear - 2000;   
    if (mmdd > 9999) {
        yyyy = Math.floor(mmdd / 10000);
        mmdd = mmdd % 10000;
    }
    var mm = Math.floor(mmdd / 100);
    var dd = mmdd % 100;
    dd = dd + n;
    if (dd > 0) {  // increasing to next month
        if (dd <= gDaysInMonth[mm]) return mmdd + n  + yyyy*10000;
        else {
            dd = dd - gDaysInMonth[mm];
            mm++;
            if (mm == 13) { // if next year
                mm = 1; // dec rolls to jan
                yyyy++;
            }
            return mm * 100 + dd  + yyyy*10000;
        }
    }

    // dd<0. handle subtract which rolls the month backward
    mm = mm - 1; if (mm == 0) {
        mm = 12;
        yyyy--;
    }
    return (mm * 100) + (gDaysInMonth[mm] + dd) + yyyy * 10000;
}

function Bumpmmdd(mmdd, n) {
    if (n == 0) return mmdd;
    var mm = Math.floor(mmdd / 100);
    var dd = mmdd % 100;
    dd = dd + n;
    if (dd > 0) {  // increasing to next month
        if (dd <= gDaysInMonth[mm]) return mmdd + n;
        else {
            dd = dd - gDaysInMonth[mm];
            mm++;
            if (mm == 13) { // if next year
                mm = 1; // dec rolls to jan
            }
            return mm * 100 + dd;
        }
    }
    // handle subtract which rolls the month backward
    mm = mm - 1; if (mm == 0) mm = 12;
    return (mm * 100) + gDaysInMonth[mm] + dd;
}

//  BumpyymmddByMonths - adds MONTHS to mmdd and adjusts mm for rollover to January and adds year
//  entry   yymmdd = original yymmdd
//          n = months to add
//  exit    returns new yymmdd. 
function BumpyymmddByMonths(yymmdd, m) {
    var mmdd = (yymmdd % 10000) + m * 100;  // remove yy and add in month
    if (mmdd > 1300) return yymmdd + m * 100 - 1200 + 10000; // if overflow, add in year
    else return yymmdd + m * 100;
}

//</script>

//  ******  ****  ***     ******
//    **     **   ** **   **  
//    **     **   **  **  ******
//    **     **   ** **   **
//    **    ****  ***     ******


//<!-- TIDES ------------------------------------------------------------------------------------------->
//<script>
//===== TIDES =======================================================================================
var gUserTideSelection = false; // true when user sets tides
var gPeriods;  // parsed data

//////////////// TIDES MAIN PAGE //////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////
// ShowNextTides MAIN PAGE - read json tide data and build main page tide info
//  Note: we get tide data using NOAA web site and return a json structure in the aeris json format.
// Original AERIS call changed to get NOAA data on the server, but still return an AERIS format.
//  entry: localStorage "jsontides" = tide data  (refreshed nightly)
//  exit: gForceCacheReload = true to reload tide data.  
//          html populated.
//          TXTS.TideData = TTS tide string
var gTideTitleNoIcon; // title for tide, with up or down arrow
var gTideTitleIcon; // title for tide, with up or down arrow

function ShowNextTides() {
    var hilow;
    var nextTides;
    var oldtide = -1;
    var newtidetime, oldtidetime, newtideheight, oldtideheight;
    var curtidespeech = "";
    var json = localStorage.getItem("jsontides");
    // get tide data
    if (json === null) {
        gForceTideReload = true;
        document.getElementById("tides").innerHTML = "Tide data not available.";
        return;
    }
    // parse the json tides structure
    try {
        var periods = JSON.parse(json); // parse it
    } catch (err) {
        document.getElementById("tides").innerHTML = "Error in tide data.<br/>" + err.message;
        return;
    }
    // roll through the reply in json.response.periods[i]
    var i;
    for (i = 0; i < periods.length; i++) {
        var thisperiod = periods[i];
        var m = Number(thisperiod.dateTimeISO.substring(5, 7));
        var d = Number(thisperiod.dateTimeISO.substring(8, 10));
        var h = Number(thisperiod.dateTimeISO.substring(11, 13)); // tide hour
        var mi = Number(thisperiod.dateTimeISO.substring(14, 16));  // time min
        var tidehhmm = ((h) * 100) + (mi);
        if (thisperiod.type == 'h') hilow = 'High';
        else hilow = 'Low';
        // if tide is past, color row gray
        if ((gMonth > m) || (gMonth == m && gDayofMonth > d) || (gMonth == m && gDayofMonth == d && (gTimehhmm > tidehhmm))) {
            oldtide = 0;
            oldtidetime = tidehhmm; oldtideheight = thisperiod.heightFT;
        } else if (oldtide != 1) {
            var cth = CalculateCurrentTideHeight(tidehhmm, oldtidetime, thisperiod.heightFT, oldtideheight);
            var tiderate2 = (CalculateCurrentTideHeight10(tidehhmm, oldtidetime, thisperiod.heightFT, oldtideheight) - cth) * 6;
            if (thisperiod.type == 'h') {
                nextTides = "<i class='material-icons'>&#xe5d8;</i> Rising ";
                curtidespeech = " Rising. "
                gTideTitleIcon = "<span style='white-space:nowrap'><i class='material-icons mpicon'>&#xe5d8;</i><span class='mptext'>Tide</span></span>";
                gTideTitleNoIcon = "TIDE <i class='material-icons mpicon'>&#xe5d8;</i>";
                //arrow_upward
            } else {
                gTideTitleIcon = "<span style='white-space:nowrap'><i class='material-icons mpicon'>&#xe5db;</i><span class='mptext'>Tide</span></span>";
                gTideTitleNoIcon = "TIDE <i class='material-icons mpicon'>&#xe5db;</i>";
                nextTides = "<i class='material-icons'>&#xe5db;</i> Falling ";
                curtidespeech = " Falling. "
                //arrow_downward  file_upload<
            }
            SetTideTitle();

            //nextTides += cth.toFixed(1) + "ft.<br/>Next: " + hilow + " " + thisperiod.heightFT + " ft. at " + ShortTime(tidehhmm) +
            //     " (in " + timeDiffhm(gTimehhmm, tidehhmm) + ")<br/>";
            var tdx = "<td style='padding:0;margin:0'>";
            nextTides = "<table border-collapse: collapse; style='padding:0;margin:0;' ><tr>" + tdx + "<strong>Now:</strong></td>" + tdx + cth.toFixed(1) +
                " ft.</td>" + tdx + nextTides + Math.abs(tiderate2).toFixed(1) + " ft/hr</td></tr> " +
                "<tr>" + tdx + "<strong>" + ShortTime(tidehhmm) + ":&nbsp</strong></td>" + tdx + thisperiod.heightFT + " ft.</td>" + tdx + hilow +
                " (in " + timeDiffhm(gTimehhmm, tidehhmm) + ")</td></tr>";
            TXTS.TideData = "The current tide is " + cth.toFixed(1) + " feet " + curtidespeech + " The next " + hilow + " tide is " + thisperiod.heightFT + " feet at " + ShortTime(tidehhmm, 1);
            oldtide = 1;
        } else if (oldtide == 1) {  // save next tide
            //nextTides += hilow + " " + thisperiod.heightFT + " ft. at " + ShortTime(tidehhmm) + " (in " + timeDiffhm(gTimehhmm, tidehhmm) + ")";
            nextTides += "<tr>" + tdx + "<strong>" + ShortTime(tidehhmm) + ":&nbsp</strong></td>" + tdx + thisperiod.heightFT + " ft.</td>" + tdx + hilow + " (in " + timeDiffhm(gTimehhmm, tidehhmm) + ")</td></tr></table>";
            document.getElementById("tides").innerHTML = nextTides;
            return;
        }
    }
    gForceTideReload = true; // if we haven't gotten today's tides, reload it
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// Calculate current tide height using cosine - assumes a 1/2 sine wave between high and low tide
// entry: t2 = next hi/low tide time as hhmm
//        t1 = previous hi/low tide time as hhmm
//        tide2, tide1 = next and previous tide heights;
//  returns current tide height
function CalculateCurrentTideHeight(t2, t1, tide2, tide1) {
    var td = RawTimeDiff(t1, t2);
    var cd = RawTimeDiff(t1, gTimehhmm);
    var c = cd / td * Math.PI;
    c = Math.cos(Math.PI - c);// cos(PI to 0) = -1 to 1
    tide = ((tide2 + tide1) / 2) + ((tide2 - tide1) / 2) * c;
    return tide;
}
// CalculateCurrentTideHeight10 - calculates the tide height in 10 minutes - same algorightm but adds 10 min
function CalculateCurrentTideHeight10(t2, t1, tide2, tide1) {
    var td = RawTimeDiff(t1, t2);
    var cd = RawTimeDiff(t1, gTimehhmm) + 10;
    if (cd > td) return tide2;
    var c = cd / td * Math.PI;
    c = Math.cos(Math.PI - c);// cos(PI to 0) = -1 to 1
    tide = ((tide2 + tide1) / 2) + ((tide2 - tide1) / 2) * c;
    return tide;
}
// Calculate current tide rate using sine. Parameters identical to CalculateCurrentTideHeight
// This seems to work correctly.
//function CalculateCurrentTideRate(t2, t1, tide2, tide1) {
//    var td = RawTimeDiff(t1, t2);
//    var cd = RawTimeDiff(t1, gTimehhmm);
//    var c = cd / td * Math.PI;
//    c = Math.sin(Math.PI - c);// sin(PI to 0) = 0 to 1 to 0
//    rate = (tide2 - tide1) * c * Math.PI/2 / td * 60; // rate per hour
//    return rate;
//}

//////////////// TIDES DETAIL PAGE /////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////
// TidesDataPage
function TidesDataPage() {
    InitializeDates(0);
    ShowPage("tidespage");
    SetPageHeader("Tides at Yoman Point");
    // show the tide data in the jsontides global
    gUserTideSelection = false;
    var json = localStorage.getItem("jsontides");
    if (IsEmpty(json)) return;
    try {
        gPeriods = JSON.parse(json);
    } catch (err) {
        alert("ERROR parsing Tides data: " + err.message + "\n" + json)
    }
    if (gPeriods == null) return;
    var i = ShowTideDataPage(gPeriods, true);
    showingtidei = i;
    //GraphTideData(gPeriods[i - 1].heightFT, gPeriods[i].heightFT, gPeriods[i + 1].heightFT,
    //    gPeriods[i - 1].dateTimeISO, gPeriods[i].dateTimeISO, gPeriods[i + 1].dateTimeISO, true);
    GraphTideData(i, true);

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ShowTideData - show the data in the gPeriods array
//  entry periods = array of data returned by aeris. Each array entry is one tide period.
//        showcurrent = true to show current tides, else false
//  exit  returns periods index of NEXT tide
//        fills the tidestable
//        sets tidepagecurrent to current tide
function ShowTideDataPage(periods, showcurrent) {

    var table = document.getElementById("tidestable");
    gTableToClear = "tidestable";
    clearTable(table);
    var olddate = periods[0].dateTimeISO.substring(5, 10);
    var oldtide = -1;
    var i;
    var nexttidei;
    var currentTide;
    var newdate;
    var starti = 0;
    var showingtidei = 0;  // row id of tide to display

    // roll through the reply in jason.response.periods[i] and find next tide row
    if(showcurrent) {
        for (i = 1; i < periods.length; i++) {
            var thisperiod = periods[i];
            var m = Number(thisperiod.dateTimeISO.substring(5, 7));
            var d = Number(thisperiod.dateTimeISO.substring(8, 10));
            var h = Number(thisperiod.dateTimeISO.substring(11, 13)); // tide hour
            var mi = Number(thisperiod.dateTimeISO.substring(14, 16));  // time min
            var tidehhmm = ((h) * 100) + (mi);
            // if tide is past, move on to next row
            if ((gMonth > m) || (gMonth == m && gDayofMonth > d) || (gMonth == m && gDayofMonth == d && (gTimehhmm > tidehhmm))) continue;
            starti = i - 1; // back up one row
            break;  // exit loop
        }
    }


    // roll through the reply in jason.response.periods[i]
    for (i = starti; i < periods.length; i++) {
        // if date changed, add a blank row
        newdate = periods[i].dateTimeISO.substring(5, 10);
        if (newdate != olddate) {
            var row1; row1 = table.insertRow(-1);
            var row1col1; row1.insertCell(0).innerHTML = " ";
            row1.insertCell(1).innerHTML = " ";
            row1.insertCell(2).innerHTML = " ";
            row1.insertCell(3).innerHTML = " ";
            olddate = newdate;
        }
        // Insert New Row for table at end of table.

        var row1 = table.insertRow(-1);
        row1.id = i.toFixed();
        row1.onclick = function () { TideClick(this.id) }

        // Insert New Column for date
        var row1col1 = row1.insertCell(0);
        var m = Number(periods[i].dateTimeISO.substring(5, 7));
        var d = Number(periods[i].dateTimeISO.substring(8, 10));
        row1col1.innerHTML = gDayofWeekShort[GetDayofWeek(m * 100 + d)] + " " + m + "/" + d + '&nbsp';
        row1col1.style.border = "thin solid gray";
        // time
        row1col1 = row1.insertCell(1);
        var h = Number(periods[i].dateTimeISO.substring(11, 13)); // tide hour
        var mi = Number(periods[i].dateTimeISO.substring(14, 16));  // time min
        tidehhmm = (Number(h) * 100) + Number(mi);
        row1col1.innerHTML = "&nbsp" + ShortTime(tidehhmm);
        row1col1.style.border = "thin solid gray";
        if (periods[i].type == 'h') {
            hilow = 'HIGH';
            row1.style.background = "azure";
        } else {
            hilow = 'Low';
            row1.style.background = "lightyellow";
        }
        // if tide is past, color row gray and show current tide info
        if (showcurrent) {
            if ((gMonth > m) || (gMonth == m && gDayofMonth > d) || (gMonth == m && gDayofMonth == d && (gTimehhmm > tidehhmm))) {
                row1.style.color = "gray";
                //currentTide = "<span style='font-size:16px;font-weight:bold;color:blue'>";
                if (periods[i].type == 'h') currentTide = "Outgoing since: ";  // incoming outgoing flag
                else currentTide = "Incoming since: ";
                currentTide += ShortTime(tidehhmm) + " (for " + timeDiffhm(tidehhmm, gTimehhmm) + ")";
                oldtideheight = Number(periods[i].heightFT);
                oldtidetime = tidehhmm;
                oldtide = 0;
            } else if ((oldtide < 1)) {

                // this is the next tide, bold it and calculate approx height                             
                row1.style.fontWeight = "bold";
                oldtide = 1;
                nexttidei = i;
                // calculate current tide height
                if (showcurrent) {
                    var tideheight = CalculateCurrentTideHeight(tidehhmm, oldtidetime, Number(periods[i].heightFT), oldtideheight);
                    //var tiderate = CalculateCurrentTideRate(tidehhmm, oldtidetime, Number(periods[i].heightFT), oldtideheight);
                    var tiderate2 = (CalculateCurrentTideHeight10(tidehhmm, oldtidetime, Number(periods[i].heightFT), oldtideheight)-tideheight) * 6;
                    currentTide = "<span style='font-size:16px;font-weight:bold;color:blue'>" +
                        "Date:" + formatDate(gMonthDay) +
                        "&nbsp&nbsp&nbsp<button onclick='ShowCustom();'>New Date</button><br/>" +
                        "Tide now: " + tideheight.toFixed(1) + " ft. " + ((tiderate2 > 0) ? "Rising " : "Falling ") + Math.abs(tiderate2).toFixed(1) + " ft/hr.<br/>" +
                        currentTide;
                        //"&nbsp&nbsp&nbsp<span style='background-color:silver;font-weight:normal' onclick='ShowCustom()'>&nbsp Change...&nbsp</span><br/>" + currentTide;
                    // calculate time till next tide                                 
                    currentTide += "<br/>" + hilow + " tide: " + periods[i].heightFT + " ft. at " + ShortTime(tidehhmm) + " (in " + timeDiffhm(gTimehhmm, tidehhmm) + ")";
                    nextTides = "Tides: " + hilow + " " + periods[i].heightFT + " ft. at " + ShortTime(tidehhmm) + ";";
                }
            } else if (oldtide == 1) {  // save next tide
                oldtide = 2;
                if (showcurrent) {
                    nextTides += hilow + " " + periods[i].heightFT + " ft. at " + ShortTime(tidehhmm) + ";";
                    localStorage.setItem("nextTides", nextTides); // save tide
                }
            }
        }

        // type
        row1col1 = row1.insertCell(2);
        row1col1.innerHTML = hilow;
        row1col1.style.border = "thin solid gray";
        // height
        row1col1 = row1.insertCell(3);
        row1col1.innerHTML = periods[i].heightFT;
        row1col1.style.border = "thin solid gray";
        //// range ()
        if (i < periods.length - 1) {
            row1col1 = row1.insertCell(-1);
            row1col1.innerHTML = "<span style='color:#bfbfbf'>" + Math.abs(periods[i].heightFT - periods[i + 1].heightFT).toFixed(1) + "</span>";
            row1col1.style.border = "thin solid lightgray";
        }
    }
    // now save the current tide
    if (!showcurrent) currentTide = "<span style='font-size:16px;font-weight:bold;color:blue'> Date:" +
        periods[0].dateTimeISO.substring(5, 7) + "/" + periods[0].dateTimeISO.substring(8, 10) +
        "<span style='color:darkgray' onclick='ShowCustom()'>&nbsp&nbsp&nbsp [Change...]";
    document.getElementById("tidepagecurrent").innerHTML = currentTide + "</span>";
    //$("#tideButton").show();

    return nexttidei; // return index
}

/////////////////////////////////////////////////////////////////////////////
//  TideClick   draw a graph for the clicked row.
//  Entry id = row index into gPeriods array
//
function TideClick(id) {
    var i = Number(id);
    if (i == 0) i = 1;
    if (i > (gPeriods.length - 5)) i = gPeriods.length - 5;
    showingtidei = i;
    gUserTideSelection = true;
    window.scroll(0, 0);  // force scroll to top
    //GraphTideData(gPeriods[i - 1].heightFT, gPeriods[i].heightFT, gPeriods[i + 1].heightFT,
    //    gPeriods[i - 1].dateTimeISO, gPeriods[i].dateTimeISO, gPeriods[i + 1].dateTimeISO, false);
    GraphTideData(i, false);
    var h = Number(gPeriods[i].dateTimeISO.substring(11, 13)); // tide hour
    var tidehhmm = (h * 100) + Number(gPeriods[i].dateTimeISO.substring(14, 16));
    var hilo = "HIGH tide: ";
    if (gPeriods[i].heightFT < gPeriods[i - 1].heightFT) hilo = "Low tide: ";
    document.getElementById("tidepagecurrent").innerHTML = "<span style='font-size:16px;font-weight:bold;color:blue'> Date:" +
      gPeriods[i].dateTimeISO.substring(5, 7) + "/" + gPeriods[i].dateTimeISO.substring(8, 10) +
      "&nbsp&nbsp&nbsp<button onclick='ShowCustom();'>New Date</button><br/>" + 
      hilo + gPeriods[i].heightFT + " ft. at " + ShortTime(tidehhmm);
}

function ShowTideNext() {
    TideClick(showingtidei + 1);   
}
function ShowTidePrevious() {
    TideClick(showingtidei - 1);
}

/////////////////////////////////////////////////////////////////////////
//  GraphTideData
//  Entry: tide1t, tide2t, tide3t, tide4t, tide5t, tide6t, tide7t = successive tide points in feet, text (HLH or LHL)
//         t1, t2, t3, t4t,t5t,t6t, t7t = corresponding times in dateTimeISO text format: 2016-05-15T19:55:00-07:00
//         NOTE: current tide must be between t1 and t2
//  modified 6/29/18 to draw on a 720px wide canvas and show 7 tide points (6 curves)
function GraphTideData(ix, showtoday) {
    var i;
    var canvas = document.getElementById("tidecanvas");
    var axes = {}, ctx = canvas.getContext("2d");
    //canvas.width = window.innerWidth; canvas.height = window.innerWidth;
    var w = canvas.width; var h = canvas.height;
    ctx.textAlign = "start";

    // convert tides to numbers
    var tideh = [7]; // tide height
    var tt = [7]; // tide time string
    var t = [7]; // tide time fp
    var LB = 999; var UB = -999;
    for (i = 0; i < 8; i++) {
        tideh[i] = Number(gPeriods[ix - 1 + i].heightFT);
        if (tideh[i] < LB) LB = tideh[i];
        if (tideh[i] > UB) UB = tideh[i];
        tt[i] = gPeriods[ix - 1 + i].dateTimeISO;
        t[i] = tHours(tt[i]);
        if (i > 0) {
            if (t[i] < t[i - 1]) t[i] += 24;
            if (t[i] < t[i - 1]) t[i] += 24;
        }
    }
    LB = Math.floor(LB - .9); // lower bound
    UB = Math.floor(UB + 1.99); // upper bound
    var tLB = Math.floor(t[0] - 1); // time lower bound
    var tUB = Math.floor(t[7] + .99); // time upper bound
    var pixelsfoot = h / (UB - LB);  // pixels per foot

    // convert time to numbers as fp hours from 0 to 48.
    var t1hhmm = Number(tt[0].substring(11, 13)) * 100 + Number(tt[0].substring(14, 16));
    var t2hhmm = Number(tt[1].substring(11, 13)) * 100 + Number(tt[1].substring(14, 16));

    var pixelshour = w / (tUB - tLB);
    var x0 = 0; // x offset to 0

    // draw background
    ctx.fillStyle = "#A0D2FF";
    ctx.fillRect(0, 0, w, h);  // fill dark blue
    // do sunnrise lighter
    var sunrisefp = gDateSunrise.getHours() + gDateSunrise.getMinutes() / 60; // convert to floating pt
    var sunsetfp = gDateSunset.getHours() + gDateSunset.getMinutes() / 60;
    // from 0 to 24
    ctx.fillStyle = "#B0E2FF"; //"#B8EAFF"; //"#C0F2FF";  // snrise times

    var ss = (sunsetfp - sunrisefp) * pixelshour;
    ctx.fillRect((sunrisefp - tLB) * pixelshour, 0, ss, h);
    ctx.fillRect((sunrisefp - tLB + 24) * pixelshour, 0, ss, h);
    ctx.fillRect((sunrisefp - tLB + 48) * pixelshour, 0, ss, h);

    // draw y axis (tide feet)
    ctx.beginPath();
    ctx.strokeStyle = "rgb(128,128,128)";
    ctx.moveTo(x0, 0); ctx.lineTo(x0, h);  // Y axis
    ctx.stroke();

    // draw tide grid lines
    ctx.strokeStyle = "#000000"; // bottom  line black
    // label x axis. skip bottom number because the time is shown there.
    var y = h - pixelsfoot;
    ctx.font = "12px Arial";
    ctx.fillStyle = "#0000ff";
    for (i = LB + 1; i <= UB; i++) {
        ctx.beginPath();
        ctx.moveTo(x0, y); ctx.lineTo(w, y);
        ctx.stroke();
        ctx.fillText(i + " ft", x0, y);
        y -= pixelsfoot;  // bump to next one
        ctx.strokeStyle = "#ffffff";
    }

    // Draw x axis (time)
    var y0 = h;
    ctx.strokeStyle = "rgb(128,128,128)";
    ctx.fillStyle = "#000000";
    ctx.beginPath();
    ctx.moveTo(x0, y0); ctx.lineTo(w, y0);  // Y axis
    ctx.stroke();
    var x = pixelshour;
    var hr;
    ctx.strokeStyle = "#ffffff";
    // label the time
    for (i = tLB + 1; i < tUB; i++) {
        if (i < 24) hr = VeryShortTime(i * 100);
        else if (i < 48) hr = VeryShortTime((i - 24) * 100);
        else hr = VeryShortTime((i - 48) * 100);
        hr = hr.substr(0, 2);
        if (hr == "no") hr = "12";
        // draw verticals for time every 4 hours
        if ((i % 4) == 0) {
            if (i == 24 || i == 48) ctx.strokeStyle = "#000000"; // black at midnight
            else ctx.strokeStyle = "#ffffff";
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, h);
            ctx.stroke();
        }
        ctx.fillText(hr, x - 8, h - 3);
        x += pixelshour;  // bump to next one
    }

    // draw the  sine waves
    for (i = 0; i < 7; i++) DrawCurve(ctx, tideh[i], tideh[i+1], t[i], t[i+1], pixelsfoot, pixelshour, h, tLB, LB);


    // draw vertical for t2 which is next high/low
    ctx.lineWidth = 1;
    ctx.strokeStyle = "#A0A0A0";
    ctx.beginPath();
    x = (t[1] - tLB) * pixelshour;
    ctx.moveTo(x, h - (tideh[1] - LB) * pixelsfoot); ctx.lineTo(x, h);
    ctx.stroke();

    // draw vertical now for current time
    if (showtoday) {
        ctx.strokeStyle = "#ff0000";
        var now = (gTimehh + gTimemm / 60);
        if (now < tLB) now += 24;
        now = (now - tLB) * pixelshour;
        ctx.beginPath();
        ctx.moveTo(now, 14); ctx.lineTo(now, h);
        ctx.stroke();
        // calculate and display current value
        var up = "\u2191"; if (tideh[1] < tideh[0]) up = "\u2193";  // up down arrow
        tide = CalculateCurrentTideHeight(t2hhmm, t1hhmm, tideh[1], tideh[0]);
        ctx.fillStyle = "#ff0000";
        ctx.font = "16px Arial";
        ctx.fillText("@ " + tide.toFixed(1) + " ft " + up, now - pixelshour, 14);
    }

    // label the date using the date for tide2 
    ctx.fillStyle = "#0000ff";
    ctx.fillText(tt[2].substring(5, 7) + "/" + tt[2].substring(8, 10), w * 0.3, 14);
    ctx.fillText(tt[6].substring(5, 7) + "/" + tt[6].substring(8, 10), w * 0.8, 14);
}

/////////////////////////////////////////////////////////////////////////////////////
// DrawCurve - draw 1/2 a sine wave for tide1@t1 to tide2@t2
//  Entry: ctx = draw context;
//          tide1, tide2 = tide height in fp feet
//          t1, t2 = times in fp hours (0 - 48)
//          pixelsfoot, pixelshour = pixel scale
//          h = height in pixels, tLB = time lower bound in hours
function DrawCurve(ctx, tide1, tide2, t1, t2, pixelsfoot, pixelshour, h, tLB, tideLB) {
    ctx.beginPath();
    //ctx.lineWidth = thick;
    var mtide = (tide1 + tide2) / 2; // mid
    var dtide = Math.abs(tide2 - tide1) / 2; // delta
    var dtide = (tide2 - tide1) / 2; // delta
    var dt = t2 - t1;
    var tide = tide1;
    var t = t1;
    var xscale = pixelshour; var yscale = pixelsfoot;
    ctx.strokeStyle = "rgb(0, 0, 255)";
    ctx.lineWidth = 2;
    ctx.moveTo(xscale * (t - tLB), h - yscale * (tide - tideLB));

    var c;
    // loop througth time from t1 to t2 by .25 hour (15 min)
    for (t = t1; t < (t2 + .25) ; t = t + .25) {
        if (t > t2) t = t2;
        c = (t - t1) / dt * Math.PI;
        /////if (tide2 > tide1) c = Math.cos(Math.PI - c);
        /////else c = Math.cos(c);
        c = Math.cos(Math.PI - c);
        //if (tide2 < tide1) c = c * -1;
        tide = mtide + dtide * c;
        ctx.lineTo(xscale * (t - tLB), h - yscale * (tide - tideLB));
    }
    ctx.stroke();
}

// tHours: convert ISO datetime to floating point hours
function tHours(tISO) {
    return Number(tISO.substring(11, 13)) + Number(tISO.substring(14, 16)) / 60;
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ShowCustom - get date for custom tides, call the aeris query, and display the result.
//
function ShowCustom() {
    InitializeDates(0);
    GetDateFromUser(ShowCustomCallback);
}
function ShowCustomCallback(tidedate){
    if (tidedate == "") return;
    gUserTideSelection = true;
    MarkPage("1");
    getCustomTideData(tidedate);
}

//////////////////////////////////////////////////////////////////
// GetDateFromUser (callback function on success or failure)
//  exit    returns mm/dd/yyyy or ""
var gDateCallback;  // date callback function
function GetDateFromUser(callback) {
    gDateCallback = callback;
    Show("getdatediv");
    document.getElementById("getdate").focus();
    return;
}
// GetDateOK - OK button from the date control
function GetDateOK() {
    Hide("getdatediv");
    var date = document.getElementById("getdate").value;  // get date as yyyy/mm/dd
    if (date != "") {
        date = date.substr(5, 2) + "/" + date.substr(8, 2) + "/" + date.substr(0, 4);
    }
    gDateCallback(date);  // return mm/dd/yyyy
}
// GetDateCancel - Cancel button from the date control
function GetDateCancel() {
    Hide("getdatediv");
    gDateCallback(""); // return null
}

///////////////////////////////////////////////////////////////////
// GetDateFromUser - asks user for date and returns as mm/dd/yy
//  return mm/dd/yy or ""
//function GetDateFromUser() {

//    var tidedate = prompt("Please enter the date as mm/dd or mm/dd/yyyy", gMonth + "/" + gDayofMonth);
//    if (tidedate == null) return "";
//    // validate the date
//    var tda = tidedate.split("/");
//    if (tda.length != 2 && tda.length != 3) {
//        alert("Invalid date. An example of a valid date is: 1/22");
//        return "";
//    }
//    var m = Number(tda[0]); // month
//    var d = Number(tda[1]);  // day
//    if (isNaN(m) || isNaN(d) || (m < 1) || (m > 12) || (d < 1) || (d > 31)) {
//        alert("Invalid date. An example of a valid date is: 1/22");
//        return;
//    }
//    m = Leading0(m);
//    d = Leading0(d);
//    var y = gYear;
//    // handle year as nn or 20nn
//    if (tda.length == 3) {
//        y = tda[2];
//        if (isNaN(tda[2])) {
//            alert("Invalid year.");
//            return;
//        }
//        y = Number(y); // make numeric
//        if (y < 2000) y += 2000;
//        if ((y < gYear) || (y - gYear > 4)) {
//            alert("Invalid year.");
//            return;
//        }
//    }
//    return Leading0(m) + "/" + Leading0(d) + "/" + y;
//}

/////////////////////////////////////////////////////////////////////////////////////////////
// getCustomTideData get tide data using the aeris api and returning a jsonp structure. 
// This is used to get custom tide data from NOAA, via my web site.
// used only for custom date queries, not for normal tides.
//  fromdate =  starting date for the tides as: mm/dd/yyyy
//  gCustomTides = 0 for customtidelink or AERIS
//                  1 for NOAA direct link
//  data is used to display tide data. It is not stored.
const gCustomTides = 1; // NOAA direct tide request

function getCustomTideData(fromdate) {
    // clear the old data display
    document.getElementById("tidepagecurrent").innerHTML = "...Retrieving tide data for " + fromdate + "...";
    var table = document.getElementById("tidestable");
    gTableToClear = "tidestable";
    clearTable(table);
    // clear the graph
    var canvas = document.getElementById("tidecanvas");
    var ctx = canvas.getContext("2d");
    ctx.fillStyle = "#A0D2FF";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // build link
    if (gCustomTides==1) { // NOAA direct
        fromdate = fromdate.substr(6, 4) + fromdate.substr(0, 2) + fromdate.substr(3,2);  //  mm/dd/yyyy -> yyyymmdd
        var myurl = "https://tidesandcurrents.noaa.gov/api/datagetter?station=9446705&product=predictions&units=english&time_zone=lst_ldt&application=ports_screen&format=json&datum=MLLW&interval=hilo&begin_date="
            + fromdate + '%2000:00&range=72';
    } else {  //customtidelink or AERIS
        var myurl = GetLink("customtidelink", 'http://api.aerisapi.com/tides/9446705?client_id=U7kp3Zwthe8dc19cZkFUz&client_secret=4fHoJYS6m9T7SERu7kkp7iVwE0dewJo5zVF38tfW');
        myurl = myurl + '&from=' + fromdate + '&to=+48hours';   
    }
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) HandleCustomTidesReply(xhttp.responseText);
    }
    xhttp.open("GET", myurl, true);
    xhttp.send();
}

function HandleCustomTidesReply(reply) {
    try {
        var json = JSON.parse(reply);
    } catch (err) {
        document.getElementById("tidepagecurrent").innerHTML = "Tides not available." + err.message;
        return;
    }
    if (gCustomTides == 1) {
        // Convert NOAA Direct Reply -> AERIS format
        gPeriods = NOAAtoAERIS(json);
    } else {
        // AERIS FORMATTED REPLY
        gPeriods = json.response.periods;
    }
    // AERIS FORMATTED REPLY
    ShowTideDataPage(gPeriods, false);
    TideClick(2);
    return;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//  NOAAtoAERIS - converts NOAA tides object to AERIS tides object
//  entry   json = json object based on noaa reply
//            { "predictions" : [ {"t":"2018-06-01 02:22", "v":"7.106", "type":"L"},...
//  exit    returns an array of AERISPeriod objects for gPeriods: 
//              dateTimeISO "2018-05-20T23:25:00-07:00" string
//              heightFT 14.0  number
//              type "h" or l   string
function NOAAtoAERIS(json) {
    var periods = []; //Array of Aeris Periods
    var i; var t; var d; var h;
    for(i=0; i<json.predictions.length; i++) {
        d = json.predictions[i].t.substr(0, 10) + "T" + json.predictions[i].t.substr(11, 16) + ":00-07:00";
        t = json.predictions[i].type.toLowerCase();  // type -> type
        h = Number(json.predictions[i].v).toFixed(1);  // v -> heightFT
        periods[i] = {dateTimeISO: d, heightFT: h, type: t };  // new object
        //        periods[i] = new AERISPeriod(d, h, t);

    }
    return periods;
}

// AERISPeriod - construct tide period objects in the original AERIS format, which is what the code understands.
//              dateTimeISO "2018-05-20T23:25:00-07:00" string
//              heightFT 14.0  number
//              type "h" or l   string
//function AERISPeriod(dateTimeISO, heightFT, type) {
//    this.dateTimeISO = dateTimeISO;
//    this.heightFT = heightFT;
//    this.type = type;
//}


/////////////////////////////////////////////////////////////////////////////////////
// ShowNOAA - query NOAA for the tide page
function ShowNOAA() {
    InitializeDates(0);
    var link = GetLink("noaalink", "http://opendap.co-ops.nos.noaa.gov/axis/webservices/highlowtidepred/response.jsp?stationId=9446705");
    link = link & "beginDate=" + gYear + gMonth + gDayofMonth + "&endDate=" + gYear + gMonth + gDayofMonth + "&datum=MLLW&unit=0& =0&format=html&Submit=Submit";
    window.open(link, "_blank");
}

//</script>







//<!-- EMERGENCY -------------------->
//<script>
//====EMERGENCY=====================================================================================

//////////////////////////////////////////////////////////////////////////////////////
// ShowEmergency
function ShowEmergencyPage() {
    ShowPage("emergencypage");
    SetPageHeader("Emergency Contacts");
    document.getElementById("emergencytable").innerHTML = localStorage.getItem("emergency");
}
//</script>


//  *    *  ******    **    ******  **  **  ******  ******
//  *    *  **       *  *     **    **  **  **      *    *
//  * ** *  ******   ****     **    ******  ******  ******
//  **  **  **      *    *    **    **  **  **      ****
//  **  **  ******  *    *    **    **  **  ******  **  **



//<!-- WEATHER ---------------------->
//<script>
//==== WEATHER =====================================================================================

/////////// WEATHER MAIN PAGE /////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////
// getCurrentWeather  using openweathermap.com free service
//  entry: "currentweathertime" = hhmm of last weather.  null to force now. otherwise we ask every 20 min
//  exit: saves "currentweathertime" and "currentweather"
// actual json response {"coord":{"lon":-122.6,"lat":47.17},"weather":[{"id":800,"main":"Clear","description":"clear sky","icon":"01n"}],"base":"cmc stations","main":{"temp":54.18,"pressure":1023.6,"humidity":69,"temp_min":54.18,"temp_max":54.18,"sea_level":1034.45,"grnd_level":1023.6},"wind":{"speed":3.36,"deg":351.002},"clouds":{"all":0},"dt":1456447797,"sys":{"message":0.0082,"country":"US","sunrise":1456498595,"sunset":1456537846},"id":5812092,"name":"Steilacoom","cod":200}
function getCurrentWeather() {
    var timestamp = Date.now() / 1000; // time in sec
    var t = localStorage.getItem("currentweathertime");
    if (t != null && ((timestamp - t) < (15 * 60))) return; // gets weather async every 15 min.

    //$.ajax({
    //    url: 'http://api.openweathermap.org/data/2.5/weather?id=5812092&units=imperial&APPID=f0047017839b75ed3d166440bef52bb0',
    //    dataType: 'jsonp',
    var myurl = GetLink("currentweatherlink", 'http://api.openweathermap.org/data/2.5/weather?id=5812092&units=imperial&APPID=f0047017839b75ed3d166440bef52bb0');
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) HandleCurrentWeatherReply(xhttp.responseText);
    }
    xhttp.open("GET", myurl, true);
    xhttp.send();
    gWeatherCurrentCount++;
}
//////////////////////////////////////////////////////////////////////////////////////
//  HandleCurrentWeatherReply - decode the json response from OpenWeatherMap
//  entry responseText = json encoded response text weather reply
//
function HandleCurrentWeatherReply(responseText) {
    var rain;
    var timestamp = Date.now() / 1000; // time in sec
    localStorage.setItem("currentweathertime", timestamp); // save the cache time 
    try {
        var r = JSON.parse(responseText);
    } catch (err) {
        alert("Error: cannot parse current weather reply: " + err.message + "\n" + responseText);
        return;
    }

    // create short string for front page
    var icon = "<img src='img/" + r.weather[0].icon + ".png' width=30 height=30>";
    if (typeof r.rain == 'undefined' || typeof r.rain["3h"] == 'undefined') rain = "0";
    else rain = (Number(r.rain["3h"]) / 25.4).toFixed(2);
    var current = icon + " " + StripDecimal(r.main.temp) + "&degF, " + r.weather[0].description + ", " + DegToCompassPoints(r.wind.deg) + " " +
        StripDecimal(r.wind.speed) + " mph" + ((rain != "0") ? (", " + rain + " rain") : "");
    TXTS.WeatherCurrent = "the current weather is " + r.weather[0].description + ", " + StripDecimal(r.main.temp) + "degrees," + " , wind " + DegToCompassPointsTTS(r.wind.deg) + " " +
        StripDecimal(r.wind.speed) + " mph. ";
    localStorage.setItem("TXTSWeatherCurrent", TXTS.WeatherCurrent);
    localStorage.setItem("currentweather", current);
    document.getElementById("weather").innerHTML = current; // jquery equivalent. Is this really easier?
    FormatWeatherIcon(r.weather[0].icon); // format weather icon for menu

    // detailed string for weather detail page
    gDateSunrise = new Date(Number(r.sys.sunrise) * 1000);
    gDateSunset = new Date(Number(r.sys.sunset) * 1000);
    var DateWeather = new Date(Number(r.dt) * 1000); //Date of weather observation
    localStorage.setItem("sunrise", gDateSunrise.getTime()); // save
    localStorage.setItem("sunset", gDateSunset.getTime()); // save
    var currentlong = icon + " " + r.weather[0].description + ", " + StripDecimal(r.main.temp) + "&degF, " +
        r.main.humidity + "% RH<br/>Wind " + DegToCompassPoints(r.wind.deg) + " " + StripDecimal(r.wind.speed) + " mph " +
        ", " + rain + " in. rain<br/>" +
        "<span style='font-weight:normal'>Pressure " + r.main.pressure + " hPa, Visibility " + (Number(r.visibility) / 1609).toFixed(0) + " mi" +
        "<br/><span style='font-size:small'>(Weather from " + DateWeather.toLocaleTimeString('en-us', { hour: '2-digit', minute: '2-digit' }) + ")</span></span>" +
        "<br/><span style='color:green'>Sunrise: " + gDateSunrise.toLocaleTimeString('en-us', { hour: '2-digit', minute: '2-digit' }) +
        "</span><span style='color:black'> | </span><span style='color:orangered'>Sunset: " + gDateSunset.toLocaleTimeString('en-us', { hour: '2-digit', minute: '2-digit' }) + "</span>";


    localStorage.setItem("currentweatherlong", currentlong);
} // end of function
//////////////////////////////////////////////////////////////////////////////
//  FormatWeatherIcon - create a main page icon from the google icon font, based on the OpenWeather icon
// entry    icon = xxd or xxn, where xx is a number for the weather icons.
var gWeatherIcon = "&#xe2c2;" //"cloud_queue";  // default
function FormatWeatherIcon(icon) {
    var iconlist = ["&#xe3a7;", "&#xe430;", "&#xe2c2;", "&#xe2c2;", "&#xe2bd;", "&#xe2bd;", "&#xe2bd;", "&#xe2bd;", "&#xe2bd;",
        "&#xe2c0;", "&#xe2c0;", "&#xe2c0;", "", "&#xeb3b;"];
    //var iconlist = ["brightness_2", "wb_sunny", cloud_queue", "cloud_queue", "cloud", "cloud", "cloud", "cloud", "cloud",
    //    "cloud_download", "cloud_download", "cloud_download", "", "ac_unit"];

    i = Number(icon.substr(0, 2));
    if (i > 13) i = 3;
    if (icon == "01n") i = 0;
    gWeatherIcon = iconlist[i];
    if (gIconSwitch == 1) document.getElementById("weathertitle").innerHTML = "<span style='white-space:nowrap'><i class='material-icons mpicon'>" + gWeatherIcon + "</i><span class='mptext'>Weather</span></span>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// getForecast using OpenWeatherMap. This is the ONLY routine that gets the forecast
// get weather data using the OpenWeatherMap api and returning a jsonp structure. This is the only way to get data from a different web site.
// License as of 2/25/16 is for 60 hits/min for free. http://openweathermap.org/price
//  exit: forecastjson = json full forecast structure, used on full forecast page
//        forecastjsontime = timestamp
//        forecast = short form of forecast for main page
function getForecast() {
    // kludge to prevent over fishing of forecast
    var timestamp = Date.now() / 1000; // time in sec
    var t = localStorage.getItem("forecasttime");
    if (t != null && ((timestamp - t) < (60 * 60))) return; // gets weather forecast async every 60 min.
    //$.ajax({
    //    url: 'http://api.openweathermap.org/data/2.5/forecast?id=5812092&units=imperial&APPID=f0047017839b75ed3d166440bef52bb0',

    //    dataType: 'jsonp',
    //    success: function (json) {
    var myurl = GetLink("weatherforecastlink", 'http://api.openweathermap.org/data/2.5/forecast?id=5812092&units=imperial&APPID=f0047017839b75ed3d166440bef52bb0');
    // ajax request without jquery
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) HandleForecastAReply(xhttp.responseText);
    }
    xhttp.open("GET", myurl, true);
    xhttp.send();
    gWeatherForecastCount++;
}
//////////////////////////////////////////////////////////////////////////////
//  HandleForecastAReply - read the jason forecast from OpenWeatherMap
//  entry   jsondata = string json data reply  (note: string, NOT an object)
function HandleForecastAReply(jsondata) {
    localStorage.setItem("forecastjson", jsondata);  // save it for full forecast
    localStorage.setItem("forecastjsontime", gTimehhmm);
    var timestamp = Date.now() / 1000; // time in sec
    localStorage.setItem("forecasttime", timestamp); // save the cache time so we don't keep asking forever
    // get hi and low
    var i, t;
    var mint = 9999; var maxt = 0;
    // scan 8 periods for min and max
    var json = JSON.parse(jsondata); // create the json object
    for (i = 0; i < 8; i++) {
        t = Math.ceil(Number(json.list[i].main.temp_max));
        if (t > maxt) maxt = t;
        if (t < mint) mint = t;
    }
    var r = json.list[0];
    var forecast = "<strong>Forecast:</strong> " + maxt + "&deg/" + mint + "&deg, " +
        r.weather[0].description + ", " + DegToCompassPoints(r.wind.deg) + " " + StripDecimal(r.wind.speed) + " mph ";
    localStorage.setItem("forecast", forecast);
    TXTS.WeatherForecast = "The forecast is " + r.weather[0].description + ", high " + maxt + ", low " + mint;
    localStorage.setItem("TXTSWeatherForecast", TXTS.WeatherForecast);
    document.getElementById("forecast").innerHTML = forecast;

    // if the forecast page is being displayed, regenerate it
    if (gDisplayPage == "weatherpage") generateWeatherForecastPage();
}  // end of function


/////////// WEATHER DETAIL PAGE /////////////////////////////////////////////////////////////////////////////////

//  ShowWeatherPage
function ShowWeatherPage() {
    ShowPage("weatherpage");
    SetPageHeader("Weather");
    InitializeDates(0);
    document.getElementById("currentweatherpage").innerHTML = localStorage.getItem("currentweatherlong") + localStorage.getItem("moon");
    generateWeatherForecastPage(); // display page from cache
    getForecast(); // start refresh of forecast if necessary (only happens every 60 min)
}


//////////////////////////////////////////////////////////////////////////////////
//  generateWeatherForecastPage - generates the forecast using the existing json reply from openweathermap
//  Entry   forecastjson = string version of json forecast object (must be parsed)
//          forecastjsontime = hhmm of when the json forecast was last retrieved
//
function generateWeatherForecastPage() {
    if (localStorage.getItem("forecastjson") == null) return;
    try{
        var json = JSON.parse(localStorage.getItem("forecastjson")); // retrieve saved data and turn it into an object again
    } catch (err) {
        document.getElementById("forecastpage").innerHTML = "Forecast data error: " + err.message;
        return;
    }
    if (json == null) return;
    //var r = json.list[0];
    //var forecast = "Forecast: " + StripDecimal(r.main.temp_max) + "&deg/" + StripDecimal(r.main.temp_min) + "&deg, " +
    //   r.weather[0].description + ", " + DegToCompassPoints(r.wind.deg) + " " + StripDecimal(r.wind.speed) + " mph ";
    var resp = json.list;
    var mydayofweek = gDayofWeek;
    var firstrow = true;  // true for first row
    var olddate = "";
    var row1;
    // roll through the reply in jason.list[i]
    var table = document.getElementById("forecasttable");
    gTableToClear = "forecasttable";
    // don't clear the table so it is there in case we don't have network coverage for a new forecast
    clearTable(table);
    var fdate = new Date();
    for (var i = 0; i < resp.length; i++) {
        // if date changed, add a blank row
        var r = resp[i];
        //var row1 = table.insertRow(-1);
        //var t = r.dt_txt.substring(11, 13) * 100;  // hh00
        var timef = Number(r.dt);// unix gmt time in sec since 1970
        if (gTimeStampms > (timef * 1000)) continue;// if this row is old, skip it. Happens when weather is not updated .

        fdate.setTime(timef * 1000);// force UTC
        newdate = fdate.getDate(); //dd
        var t = fdate.getHours() * 100 + fdate.getMinutes(); //hhmm

        // Insert New Row for table for new day 
        if (newdate != olddate) {
            row1 = table.insertRow(-1); // dummy row
            var row1col1 = row1.insertCell(0);
            row1col1.colSpan = "6";
            //row1.insertCell(1); row1.insertCell(2); row1.insertCell(3); row1.insertCell(4);
            var dow = fdate.getDay();;
            row1col1.innerHTML = gDayofWeekShort[dow] + " " + (fdate.getMonth() + 1) + "/" + newdate;
            olddate = newdate;
        }
        var row1 = table.insertRow(-1);
        if ((t < 600) || (t > 1800)) row1.style.backgroundColor = "lightgray";
        else row1.style.backgroundColor = "lightyellow";

        // Insert New Column for time
        var row1col1 = row1.insertCell(0);
        row1col1.innerHTML = "&nbsp&nbsp&nbsp" + VeryShortTime(t);  // time
        firstrow = false;
        row1col1.style.border = "thin solid gray";
        // high/low
        row1col1 = row1.insertCell(-1);
        row1col1.innerHTML = StripDecimal(r.main.temp_max) + "&deg";
        row1col1.style.border = "thin solid gray";
        // icon
        var icon = r.weather[0].icon.substr(0, 2);
        if ((t < 600) || (t > 1800)) icon += 'n';
        else icon += 'd';
        icon = "<img src='img/" + icon + ".png' width=30 height=30>";
        // weather
        row1col1 = row1.insertCell(-1);
        row1col1.innerHTML = icon + "&nbsp " + r.weather[0].description;
        row1col1.style.border = "thin solid gray";
        // rain
        row1col1 = row1.insertCell(-1);
        var rain;
        if (typeof r.rain == 'undefined' || typeof r.rain["3h"] == 'undefined') rain = "0";
        else rain = (Number(r.rain["3h"]) / 25.4).toFixed(2);
        row1col1.innerHTML = rain;
        row1col1.style.border = "thin solid gray";
        // wind
        row1col1 = row1.insertCell(-1);
        row1col1.innerHTML = DegToCompassPoints(r.wind.deg) + " " + StripDecimal(r.wind.speed);
        row1col1.style.border = "thin solid gray";
    }  // for end

    document.getElementById("forecastpage").innerHTML = "Data from " + FormatTime(localStorage.getItem("forecastjsontime"));
}




//</script>show

//<!-- ABOUT ------------------------>
//<script>
//====ABOUT=========================================================================================

// FERRY WEB CAM
function ShowFerryWebCam() {
    ShowPage("mferrywebcampage");
    SetPageHeader("Ferry Lane Cameras");
    // steilacoom link from local storage
    var link = GetLink("ferrycams","http://online.co.pierce.wa.us/xml/abtus/ourorg/PWU/Ferry/Steilacoom.jpg");
    link = link + "?random" + gTimehhmm.toFixed(0); // defeat the cache
    document.getElementById("steilacoomcam").setAttribute("src", link);
    document.getElementById("steilacoomcam").setAttribute("onclick", "window.open('" + link + "', '_blank', 'EnableViewPortScale=yes')");
    document.getElementById("scamera").innerHTML="Steilacoom: next @ " + FindNextSingleFerryTime(UseFerryTime("S"));
    // anderson link from local storage
    link = GetLink("ferrycama","http://online.co.pierce.wa.us/xml/abtus/ourorg/PWU/Ferry/AndersonIsland.jpg");
    link = link + "?random" + gTimehhmm.toFixed(0); // defeat the cache
    document.getElementById("aicam").setAttribute("src", link);
    document.getElementById("aicam").setAttribute("onclick", "window.open('" + link + "', '_blank', 'EnableViewPortScale=yes')");
    document.getElementById("aicamera").innerHTML="Anderson Island: next @ " + FindNextSingleFerryTime(UseFerryTime("A"));

}

//====ABOUT=========================================================================================
/////////////////////////////////////////////////////////////////////////////////////
// About
function ShowAboutPage() {
    ShowPage("aboutpage");
    SetPageHeader("About");
    if (isPhoneGap()) document.getElementById("phoneweb").innerHTML = "Phone version.";
    DisplayLoadTimes();
}

//====LINKS=========================================================================================
/////////////////////////////////////////////////////////////////////////////////////
// ShowLinksPage - show the information in the "links" download.
//  Entry should contain <span id='x'>  and <span id='y'>. then use showme and hideme to show and hide sections.
//  entry   showme = id to show
//          hideme = id to hide
function ShowLinksPage(showme,hideme) {
    ShowPage("linkspage");
    SetPageHeader("Island Information Links");
    document.getElementById("islandlinks").innerHTML = localStorage.getItem("links");
    Show(showme);
    Hide(hideme);
}

// HELP
function ShowHelpPage() {
    ShowPage('helppage');
    SetPageHeader("Help");
}

////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////  TEXT TO SPEECH using the TTS plugin  and global TXTS Object /////////////////////////////////////////////////////////////////

//  TTSToggle - toggles the TXTS.OnOff switch and saves it as TTS. Note that the html will set ttstog.checked = true or false
//
function TTSToggle() {
    if (MenuIfChecked("ttstog")) {
        TXTS.OnOff = 1;
        document.getElementById("topline").innerHTML = "Tap LEFT for speech.   Tap RIGHT for details.";
    } else {
        TXTS.OnOff = 0;
        document.getElementById("topline").innerHTML = "Tap Row for Details";
    }
    localStorage.setItem("TTS", TXTS.OnOff.toFixed(0));
}

function BigTextToggle() {
    if (MenuIfChecked("bigtog")) {
        BIGTX.OnOff = 1;
    } else {
        BIGTX.OnOff = 0;
    }
    localStorage.setItem("bigtext", BIGTX.OnOff.toFixed(0));
}

/////////////////////////////////////////////////////////////////////
//  InitializeSpeech - show startup message if necessary.
//  Entry: local storage "TTS" = stored state, null if 1st call.
//  Exit:   TXTS.OnOff set.
//      If first time call, display message and set "TTS".
TXTS.InitializeSpeechMessage  = function () {
    var itts;
    if (!isPhoneGap()) itts = 0; // 0 if not phonegap
    else itts = localStorage.getItem("TTS"); // 

    if (itts == null) {
        this.FirstTime = true;
        //alert("The Anderson Island Assistant now speaks.\nFor speech, tap the LEFT side of a row.\nFor details, tap the RIGHT side of a row.\nTo turn off speech select:\n     Menu -> speech -> Off\n in the upper left-hand corner of the main screen.\nFor BIG TEXT instead of speech, select Manu -> Big Text -> On.");
        itts = 1;
        localStorage.setItem("TTS", "1");
    }
    TXTS.OnOff = Number(itts);  // load text to speech. Defaults to 1 (on). For web, it is always off
    if (TXTS.OnOff == 0) document.getElementById("topline").innerHTML = "Tap Row for Details";
}

/////////////////////////////////////////////////////////////
//  FirstTimeMsg - issue first time message, which is a DIV in index.html. This prevents timeouts from the alert.
TXTS.FirstTimeMsg = function () {
        Show("speechdialog");
        //alert("This app now has SPEECH and BIG TEXT.\nFor speech or big text, tap the LEFT side of a row.\nFor details, tap the RIGHT side.\nTo turn off speech select:\n     Menu -> Speech -> Off\nTo turn on BIG TEXT, select:\n     Menu -> Big Text -> On");
        TXTS.FirstTime = false;
}

BIGTX.InitializeBigText = function () {
    var ibs;
    ibs = localStorage.getItem("bigtext"); // 

    if (ibs == null) {
        //alert("The Anderson Island Assistant now speaks.\nFor speech, tap the LEFT side of a row.\nFor details, tap the RIGHT side of a row.\nTo turn off speech select:\n     Menu -> Speech -> Off\n in the upper left-hand corner of the main screen.");
        ibs = 0;
        localStorage.setItem("bigtext", "1");
    }
    BIGTX.OnOff = Number(ibs);  // load text to speech. Defaults to 1 (on). For web, it is always off
    //if (TXTS.BigSpeech == 0) document.getElementById("topline").innerHTML = "Tap Row for Details";
}

////////////////////////////////////////////////////////////////////////////////////
//  TTSSpeak - Speak the text (if speech is on)
//  Entry: speech = text string to talk
//          displayfunction = function to call if TXTS. = 0 (off)
TXTS.Speak = function(speech, displayfunction, bigtext) {
    var reason;

    if (BIGTX.OnOff) {
        document.getElementById("bigtextpage").innerHTML = bigtext;
        ShowPage("bigtextpage");
        SetPageHeader("Big Text");
        if (TXTS.OnOff == 0) return;
    }

    switch (TXTS.OnOff) {
        // 0 = ignore. behave in the default way.
        case 0: 
            displayfunction();
            break;

        // 1 = speak
        case 1:
            TTS
                .speak(speech).then(function () {
                }, function (reason) {
                    alert(reason);
                });
            break;

        // 2 = large text
        case 2:
            break;
    }
}

////////////////////////////////////////////////////////////////////////////////////
//  TTSFerrySchedule - announce the ferry departure time
//  Entry: TXTS.FerryTime = text string to speak.  Built by FindNextFerryTime
//
function TTSFerrySchedule() {
    TXTS.Speak(TXTS.FerryTime, DisplayFerrySchedulePage, BIGTX.FerryTime);
}

////////////////////////////////////////////////////////////////////////////////////
//  TTSTidesData - announce the tide data
//  Entry: TXTS.TideData = text string to speak.  Built by ShowNextTides
function TTSTidesData() {
    TXTS.Speak(TXTS.TideData, TidesDataPage, TXTS.TideData);//document.getElementById("tides").innerHTML);
}

///////////////////////////////////////////////////////////////////////////////////
//  TTSShowWeather - announce the weather current and forecast data
//  Entry: TXTSWeatherCurrent in localstorage = text strings to speak.  Built by
function TTSShowWeather() {
    TXTS.Speak(LSget("TXTSWeatherCurrent") + LSget("TXTSWeatherForecast"), ShowWeatherPage, LSget("currentweather") + "<br/>" + LSget("forecast"));
}

////////////////////////////////////////////////////////////////////////////////////
//  TTSShowEvent - announce the next event
//  Entry: TXTS.NextEvent = text string to speak.  Built by 
function TTSShowEvent() {
    document.getElementById("nextevent").innerHTML = DisplayNextEvents(localStorage.getItem("comingevents"));
    TXTS.Speak("The next event is " + TXTS.Next + ".", DisplayComingEventsPageE, document.getElementById("nextevent").innerHTML);
}

///////// TTS Next Activity /////////////////////////////////////////////////////////////////////
function TTSShowActivity() {
    document.getElementById("nextactivity").innerHTML = DisplayNextEvents(localStorage.getItem("comingactivities"));
    TXTS.Speak("The next activity is " + TXTS.Next + ".", DisplayComingEventsPageA, document.getElementById("nextactivity").innerHTML);
}

///////// TTS Show Open Hours /////////////////////////////////////////////////////////////////////
function TTSShowOpen() {
    TXTS.Speak(TXTS.OpenHours, ShowOpenHoursPage, document.getElementById("openhours").innerHTML);
}

///////// TTS Burn Ban /////////////////////////////////////////////////////////////////////
function TTSBurnBan() {
    TXTS.Speak(RemoveTags(document.getElementById("burnbanalert").innerHTML), ShowBurnBan, document.getElementById("burnbanalert").innerHTML );
}

///////// TTS Tanner Outage /////////////////////////////////////////////////////////////////////
function TTSTannerOutage() {
    TXTS.Speak("Tanner status as of " + RemoveTags(document.getElementById("tanneroutagealert").innerHTML), ShowTannerOutage, document.getElementById("tanneroutagealert").innerHTML);
}


////////////////////////////////////////////////////////////////////////////////////////////////////
//  FerryInitialize - set switches and ask user for geolocate permission.
//  Note: "ferryhighlight" controls highlighting and is also the 1st time flag for geolocation.
//      if user declines location permission, ferryhighlight is set to 0.
//  Exit:   Sets gFerryShow3, gFerryShowIn, gFerryHight, gLocationOnAI
function FerryInitialize() {
    if (window.screen.width >= 500) gFerryShow3 = 1;  // default to 3 ferry schedules if >360 pixels
    var s = localStorage.getItem("ferryshow3");
    if (s != null) gFerryShow3 = Number(s);
    s = localStorage.getItem("ferryshowin");
    if (s != null) gFerryShowIn = Number(s);

    s = localStorage.getItem("ferryhighlight");
    if (s != null) gFerryHighlight = Number(s);

    if (gFerryHighlight) {
        s = localStorage.getItem("glocationonai");  // restore last 'on ai' setting
        if (s != null) gLocationOnAI = Number(s);
    }
}
// delayed ask of permission, to prevent a timeout in the initial startup.
//function FerryAskPermission() {
//    if (confirm("Anderson Island Assistant wants to use your current location to automatically highlight either the Steilacoom or Anderson Island ferry schedule row.\nClick 'OK' to allow this.\nClick 'CANCEL' to prevent this.")) {
//        gFerryHighlight = 1;
//        getGeoLocation();
//    }
//    else gFerryHighlight = 0;
//    localStorage.setItem("ferryhighlight", gFerryHighlight);
function LocationAllow() {
    gFerryHighlight = 1;
    localStorage.setItem("ferryhighlight", "1");
    getGeoLocation();
    document.getElementById("locationdialog").style.width = "0";
}
function LocationPrevent() {
    gFerryHighlight = 0;
    localStorage.setItem("ferryhighlight", "0");
    document.getElementById("locationdialog").style.width = "0";
}

////////////////////////////////////////////////////////////////////////////////////////////////////
//  Switch icon/no icon mode

var icona = ["ferrytitle", "directions_boat", "Ferry", "webcamtitle", "videocam", "Camera",
    "loctitle", "pin_drop", "Location", "tickettitle", "confirmation_number", "Tickets",
    "weathertitle", "cloud_queue", "Weather",
    "eventtitle", "event", "Events", "activitytitle", "directions_run", "Activity",
    "opentitle", "schedule", "Open", "burnbantitle", "whatshot", "Burnban",
    "tannertitle", "flash_on", "Tanner", "parkstitle", "nature_people", "Parks",
    "fishtitle", "rowing", "Fishing", "newstitle", "chat", "News",
    "maptitle", "map", "Map", "linkstitle", "link", "Island Information Links",
    "contactstitle", "phone", "Emergency Contacts", "helptitle", "help", "Help",
    "feedbacktitle", "email", "Feedback", "abouttitle", "info_outline", "About",
    "voluntitle", "group", "Volunteering"];
//////////////////////////////////////////////////////////
//  ShowIcons - show icons in the front page
//
// icona: html id, icon name, title, ...
//  Entry: nt=1 for icons and LC titles, 2 for icons and UC titles, 3 for icons only, 4 (or 0) for UC titles only, 5 for LC titles only
//   only 1 and 4 are used.
function ShowIcons(nt) {
    if (nt == 0) nt = 4; // 0 becomes 4 Titles only
    if (gIconSwitch != nt) {
        gIconSwitch = nt;
        var i = 0;
        var s;
        for (i = 0; i < icona.length; i = i + 3) {
            switch (nt) {
                case 1: s = "<span style='white-space:nowrap'><i class='material-icons mpicon'>" + icona[i + 1] + "</i><span class='mptext'>" + icona[i + 2] + "</span></span>";
                    break;
                case 2: s = "<i class='material-icons mpicon'>" + icona[i + 1] + "</i>" + icona[i + 2].toLocaleUpperCase();
                    break;
                case 3: s = "<i class='material-icons mpicon'>" + icona[i + 1];
                    break;
                case 4: s = icona[i + 2].toLocaleUpperCase();
                    break;
                case 5: s = icona[i + 2];
                    break;
            }
            if (document.getElementById(icona[i]) != null) document.getElementById(icona[i]).innerHTML = s;
        }
    }

    // remember it
    localStorage.setItem("icons", nt.toFixed(0)); // icons = 1 - 5 string,
    // special case for icons
    SetTideTitle();
    if (gIconSwitch == 1) {  // if icons
        MarkPage("5"); // 5= icons
        document.getElementById("weathertitle").innerHTML = "<span style='white-space:nowrap'><i class='material-icons mpicon'>" + gWeatherIcon + "</i><span class='mptext'>Weather</span></span>";
    } else {  // no icons
        MarkPage("6"); // 6=no icons
    }
}

// ShowIconsToggle - toggle the icon status between 1 and 4.
//  Exit    ShowIcons called.
function ShowIconsToggle() {
    if (MenuIfChecked("showiconstog") == 0) ShowIcons(4);  // if not checked
    else ShowIcons(1);
}

//////////////////////////////////////////////////////////////////////
//  InitializeIcons - show startup message if necessary. Hide icons if requested.
//  ASSUMES THAT index.html HAS ICONS AT STARTUP!
//  Entry: local storage "icons" = icon value
function InitializeIcons() {
    gIconSwitch = 1;
    var ic = Number(LSget(localStorage.getItem("icons"), "0")); // icons = 0 (null) - 5,
    if (ic == 0) {
        //alert("The Anderson Island Assistant now uses icons on its main screen. To revert to the former text-only display, select:\n Menu -> Icons -> Off\n in the upper left-hand corner of the main screen.");
        ic = 1;
    }
    ShowIcons(ic);
}

/////////////////////////////////////////////////////////////
//  SetTideTitle - sets the tide title with icon in appropriate place, based on gIconSwitch
function SetTideTitle() {
    if (gIconSwitch == 1 || gIconSwitch == 2) {
        document.getElementById("tidestitle").innerHTML = gTideTitleIcon;
    } else {
        document.getElementById("tidestitle").innerHTML = gTideTitleNoIcon;
    }
}






//<!-- MAIN ---------------------------------->
//<script type="text/javascript">
//======================================================================================================
/////////////////////////////////////////////////////////////////////////////////////////////////
//  MAIN APP CODE
//
function StartApp() {
    app.initialize();
    initializeMobile(); // set flags
    FixiPhoneHeader();
    document.getElementById("versionnumber").innerHTML = "&nbsp&nbsp AIA Ver: " + gVer; // version stamp on footer
    gMyVer = gVer.substr(0, 4); //n.nn
    gDisplayPage = "mainpage";
    CountPage("main");
    InitializeDates(0);
    if (gYear % 4 == 0) gDaysInMonth[2] = 29; // leap year
    gDateSunrise = new Date(Number(localStorage.getItem("sunrise"))); // reload sunrise and sunset
    gDateSunset = new Date(Number(localStorage.getItem("sunset"))); // reload sunrise and sunset
    gAppStartedTime = gTimeStampms;
    gAppStartedDate = gMonthDay * 10000 + gTimehhmm;
    gLastUpdatems = gTimeStampms;
    document.getElementById("updatetime").innerHTML = "Updated " + FormatTime(gTimehhmm);
    gForceCacheReload = false; // cache reload not needed
    gForceTideReload = false;
    alert("at 1");//////////////////////////////
    InstallAvailable();  // point user to google play only if a mobile browser that is NOT PhoneGap
    UpdateAvailable(); // point user to google play only if a new version is available

    // Restore Ferry schedule switches used by ShowFerryTimes
    FerryInitialize();

    // ios - hide the update app at the request of the Apple App Review team 3/19/17.
    if (isPhoneGap() && !isAndroid()) document.getElementById("updateappswitch").setAttribute('style', 'display:none;');
    alert("at 2");//////////////////////////////
    // Replace icons with Labels if user selected them
    InitializeIcons();
    TXTS.InitializeSpeechMessage(); // initial speech message
    BIGTX.InitializeBigText(); // initial big text
    alert("at 3");//////////////////////////////
    //  Show the cached data immediately if there is no version change. Otherwise wait for a cache reload.
    if(LSget("myver") == gMyVer) {
        ParseFerryTimes();  // moved saved data into ferry time arrays
        ParseOpenHours();
        ShowCachedData();
    } else gForceCacheReload = true;
    
    //// show the page
    //Show("mainpage");  // now display the main page
    //Show("vermsg"); // display the version

    // -------------  after main page has been displayed ---------------------------
    alert("at 4");//////////////////////////////
    //reload the 'dailycache' cache + coming events + tides + forecast if the day or MyVer has changed .
    var reloadreason = "";
    var dailycacheloaded = localStorage.getItem("dailycacheloaded");
    if (dailycacheloaded == null) {
        gForceCacheReload = true;
        reloadreason = "initial cache load";
    } else if (Number(dailycacheloaded) != gMonthDay) {
        reloadreason = "dailycacheloaded != monthday";
        gForceCacheReload = true;
    } else if (localStorage.getItem("comingevents").charAt(4) == ";") {
        gForceCacheReload = true; // reload cache if coming events does not have a year as yymmdd
        reloadreason = "comingevents year";
    }

    if (gForceCacheReload) {
        document.getElementById("reloadreason").innerHTML = reloadreason;
        ReloadCachedData();  // reload daily cache, alerts (always), weather (always)
    } else {
        // show Alert and Weather immediately.
        gAlertTime = 0; // force immediate reload of alert info
        getAlertInfo(); // always get alert info 
        getCurrentWeather(); // gets weather async every 20 min.
        getForecast(); // updates forecast every 2 hrs

    }
    alert("at 5");//////////////////////////////
    if (isPhoneGap()) {
        s = localStorage.getItem("ferryhighlight");
        if (s == null) document.getElementById("locationdialog").style.width = "100%";// the 1st time, ask user for permission
        else if (gFerryHighlight) getGeoLocation();
    }

    // set refresh timners
    gMyTimer = setInterval("timerUp()", 60000);  // timeout in milliseconds. currently 60 seconds
    window.addEventListener("focus", focusEvent);
    window.addEventListener("blur", blurEvent);
    document.addEventListener("backbutton", backKeyDown, true);
    document.addEventListener("pause", onPause, false);
    document.addEventListener("resume", onResume, false);
    //DisplayLoadTimes();
    alert("at 6");//////////////////////////////
    MenuSetup(); // setup the menu switches

    // show the page
    Show("mainpage");  // now display the main page
    Show("vermsg"); // display the version
    alert("at 7");//////////////////////////////
    // initial alerts are shown now (otherwise they cause a timeout error)
    if (TXTS.FirstTime) TXTS.FirstTimeMsg();
    alert("at 8");//////////////////////////////
}
