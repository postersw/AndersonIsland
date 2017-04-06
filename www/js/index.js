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
 * 
 *  copyright 2016, Bob Bedoll
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
var gVer = "1.11.0406171";  // VERSION MUST be n.nn. ...  e.g. 1.07 for version comparison to work.
var gMyVer; // 1st 4 char of gVer

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
            // only initialize every 3 days to cut down on number of API calls, because we are limited to 10K/month
            var pbtime = Number(LSget("pushbotstime"));
            var timems = GetTimeMS();
            if ((pbtime == 0) || ((timems - pbtime) > (72 * 3600000))) {
                window.plugins.PushbotsPlugin.initialize("570ab8464a9efaf47a8b4568", { "android": { "sender_id": "577784876912" } });
                localStorage.setItem("pushbotstime", timems.toFixed(0));
            }
            window.plugins.PushbotsPlugin.resetBadge();  // clear ios counter
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


var openHoursLastUpdate; // time of last update



// tides
var nextTides; // string of next tides for the main page
var tidesLastUpdate; // time of last update

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
    if (dateincr == 0 && laborday == 0) {
        // laborday // first monday in sept.  we need to compute this dyanmically
        var dlabordate, dlabor;
        dlabordate = new Date(gYear, 8, 1); // earlies possible date
        dlabor = dlabordate.getDay();
        if (dlabor > 1) laborday = 909 - dlabor;  // monday = 1... Sat=6
        else if (dlabor == 0) laborday = 902;  // monday = 1... Sat=6
        else laborday = 901;
        // memorial day last monday in may
        var dmemdate, memdate;
        dmemdate = new Date(gYear, 4, 25); // earliest possible date memorial day
        memday = dmemdate.getDay();
        if (memday > 1) memorialday = 525 + 8 - memday;  // monday = 1... Sat=6
        else if (memday == 0) memorialday = 526;  // monday = 1... Sat=6
        else memorialday = 525;
        // thanksgiving
        var dthanksdate, dthanks;
        dthanksdate = new Date(gYear, 10, 24);// earliest possible 4th thursday (4) in november
        dthanks = dthanksdate.getDay();
        if (dthanks < 5) thanksgiving = 1124 + 4 - dthanks;
        else if (dthanks == 5) thanksgiving = 1130;
        else thanksgiving = 1129;
    }
    // compute holidays
    holiday = IsHoliday(gMonthDay);

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
/////////////////////////////////////////////////////////////////////////////////////
// GetWeekofYear - returns week of year
//  entry   mmdd = mmdd (assumes gYear) or yymmdd
function GetWeekofYear(mmdd) {
    var mmdd = Number(mmdd);
    var yyyy = gYear;
    if (mmdd > 9999) {
        yyyy = Math.floor(mmdd / 10000) + 2000; // extract year
        mmdd = mmdd % 10000;
    }
    var januaryFirst = new Date(gYear, 0, 1);
    var thedate = new Date(gYear, Math.floor(mmdd / 100) - 1, mmdd % 100);
    return Math.floor((((thedate - januaryFirst) / 86400000) + januaryFirst.getDay()) / 7);
}
////////////////////////////////////////////////////////////////////////////////////////////
// DateDiff - return difference in days between 2 dates in our funky mmdd format (0101 - 1231)
//  e.g. DateDiff(0122, 0102) = 20.  Handles rollover for a single year only.
function DateDiff(mmdd1, mmdd2) {
    var dayspermonth = [0, 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365]; // cumulative days in year
    if (mmdd1 == mmdd2) return 0;
    var m1 = Math.floor(mmdd1 / 100);
    var m2 = Math.floor(mmdd2 / 100);
    var d1 = mmdd1 % 100;
    var d2 = mmdd2 % 100;
    var r = dayspermonth[m1] + d1 - dayspermonth[m2] - d2;
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
// ValidFerryRun return true if a valid ferry time, else false.
// the crazy special rules go here.
// flag: *=always, H=holiday, 0-6=days of week, AFXY=special rules
//  A=July 3, Christmas Eve, New Year's Eve Only if Monday-Friday
//  F=every day except 1st and 3rd wednesdays of every month
//  G=1st and 3rd Tue only
//  X=Friday Only labor day-12/31, 0101-6/30,
//  Y=Fridays only 7/1=labor day

//function ValidFerryRun(flag) {
//    if (flag.indexOf("*") > -1) return true; // good every day
//    // holiday - use holiday schedule only.  Any run on a holiday must have * or H.
//    if (holiday) {
//        if (flag.indexOf("H") > -1) { // yes a valid run		 
//            // the A rule:July 3, Christmas Eve, New Year's Eve AND Only if Monday-Friday
//            if (flag.indexOf("A") > -1) { //	July 3, Christmas Eve, New Year's Eve Only if Monday-Friday		 +        else return false;
//                if (!((gMonthDay == 1231) || (gMonthDay == 1224) || (gMonthDay == 703))) return false; // if not 1231,1224,or 703, its not valid		
//                if (gDayofWeek >= 1 && gDayofWeek <= 5) return true; // if 1231, 1224, or 703 and M-F, its good
//                return false;
//            } else return true;  // holiday		
//        } else return false;
//    }


//    if (flag.indexOf(gLetterofWeek) > -1) return true;  // if day of week is encoded
//    // special cases F, skip 1st and 3rd wednesday of every month
//    if (flag.indexOf("F") > -1) {
//        if (gDayofWeek != 3) return true;  // if not wednesday, accept it
//        week = Math.floor((gDayofMonth - 1) / 7);  // week: 0,1,2,3
//        if (week != 0 && week != 2) return true; // if not 1st or 3rd wednesday, accept it
//    }
//    if (flag.indexOf("G") > -1) { //G 1 & 3rd Tue only
//        if (gDayofWeek != 2) return false;  // if not tuesday reject it
//        week = Math.floor((gDayofMonth - 1) / 7);  // week: 0,1,2,3
//        if (week == 0 || week == 2) return true; // if  1st or 3rd Tue, accept it
//    }
//    if (flag.indexOf("X") > -1) {  // Friday Only labor day-12/31, 0101-6/30,
//        if ((gDayofWeek == 5) && ((gMonthDay >= laborday) || (gMonthDay <= 630))) return true;
//    }
//    if (flag.indexOf("Y") > -1) {  // Fridays only 7/1=labor day
//        if ((gDayofWeek == 5) && (gMonthDay >= 701) && (gMonthDay <= laborday)) return true;
//    }
//    return false; // not a valid run;
//}

/////////////////////////////////////////////////////////////////////////////////////////
// ValidFerryRun return true if a valid ferry time, else false.
//  alternate to having the rules special cased
// flag: *=always, 0-6=days of week, (xxxx) = eval rules in javascript
//  eval rules are javascript, returning true for a valid run, else false
//    can use global variables gMonthDay, gDayofWeek, gWeekofMonth,...

function ValidFerryRun(flag) {
    if (flag == undefined || flag == "") return false;
    if (flag.indexOf("*") > -1) return true; // good every day
    if (flag.substr(0, 1) != "(") {
        if (flag.indexOf(gLetterofWeek) > -1) return true;  // if day of week is encoded
        return false;
    }

    // (eval rules ).
    var t = eval(flag);
    return t;
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
// format ferry time for display. 
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
//  shorttime - shortest possible time
function ShortTime(ft) {
    var ampm;
    if (ft < 1199) ampm = "a";
    else ampm = "p";
    if (ft < 100) return "12:" + Leading0(ft % 100) + ampm;
    else if (ft < 1299) return (Math.floor(ft / 100)) + ":" + Leading0(ft % 100) + ampm;
    else return (Math.floor(ft / 100) - 12) + ":" + Leading0(ft % 100) + ampm;
}
////////////////////////////////////////////////////////////////////////////////////////////
//  veryshorttime - shortest possible time
//  like ShortTime but does not return minutes if not needed
//  so it returns 1p, where ShortTime returns 1:00p;
function VeryShortTime(ft) {
    if (ft == 1200) return "noon";
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
// RawTimeDiff returns the time difference in minutes; hhmm2 - hhmm1
function RawTimeDiff(hhmm1, hhmm2) {
    var tm, ftm;
    tm = (Math.floor(hhmm1 / 100) * 60) + (hhmm1 % 100); // time in min
    ftm = (Math.floor(hhmm2 / 100) * 60) + (hhmm2 % 100);
    if (ftm < tm) ftm = ftm + 24 * 60;
    return ftm - tm; // diff in minutes
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


//////////////////////////////////// TIDE STUFF /////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////
// Calculate current tide height using cosine - assumes a 1/2 sine wave between high and low tide
// entry: t2 = next hi/low tide time as hhmm
//        t1 = previous hi/low tide time as hhmm
//        newtideheight, oldtideheight = next and previous tide heights;
//  returns current tide height
function CalculateCurrentTideHeight(t2, t1, tide2, tide1) {
    var td = RawTimeDiff(t1, t2);
    var cd = RawTimeDiff(t1, gTimehhmm);
    var c = cd / td * Math.PI;
    c = Math.cos(Math.PI - c);// cos(PI to 0) = -1 to 1
    tide = ((tide2 + tide1) / 2) + ((tide2 - tide1) / 2) * c;
    return tide;
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
    window.open(link, '_blank','EnableViewPortScale=yes' );
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
function NotifyOn() {
    localStorage.removeItem('notifyoff');
    if (isPhoneGap()) {
        window.plugins.PushbotsPlugin.initialize("570ab8464a9efaf47a8b4568", { "android": { "sender_id": "577784876912" } });
        NotifyColor(1);
    }
}
function NotifyOff() {
    MarkPage("4");
    localStorage.setItem('notifyoff', 'OFF');
    if (isPhoneGap()) {
        window.plugins.PushbotsPlugin.unregister();
        NotifyColor(0);
    }
}
//  Set the color of the notify on/off button.  
//      onoff = 1 for on, 0 for off.
function NotifyColor(onoff) {
    if (onoff == 1) {
        document.getElementById('notifyon').setAttribute("style", "color:white");
        document.getElementById('notifyoff').setAttribute("style", "color:gray")
    } else {
        document.getElementById('notifyon').setAttribute("style", "color:gray");
        document.getElementById('notifyoff').setAttribute("style", "color:white")
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
// Determine whether the file loaded from PhoneGap or the web
//  exit    true if phonegap, otherwise undefined. So test for true only.
function isPhoneGap() {
    var test = /^file:\/{3}[^\/]/i.test(window.location.href)
    && /ios|iphone|ipod|ipad|android/i.test(navigator.userAgent);
    return test;
    //(window.cordova || window.PhoneGap || window.phonegap) this returned undefined. 
}
function isAndroid() {
    return ((navigator.userAgent.toLowerCase().indexOf('chrome') > -1) ||
    (navigator.userAgent.toLowerCase().indexOf('android') > -1));
}

//isMobile - returns true if a Mobile browser (even if not PhoneGap), else false.  INDEPENDENT OF PHONEGAP.
function isMobile() {
    return /ios|iphone|ipod|ipad|android/i.test(navigator.userAgent);
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
//  LSget - local storage get always returns string or "". never returns null.
function LSget(id) {
    var s = localStorage.getItem(id);
    if (s != null) return s;
    return "";
}

function LSappend(id, s) {
    localStorage.setItem(id, LSget(id) + s)
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
}



/////////////////////////////////////////////////////////////////////////////////////////////////
// getAlertInfo - without jQuery- gets the alert info from the server every 12 minutes via php and save it in 
//      alerttext and alertdetail and ...
//  Entry   'alerthide' = true to hide the alert in 'alerttext'
//  Exit    'alerttext', 'alertdetail' set.  'alerthide' cleared if the alert has changed.
//          'burnbanalert' = burn ban alert info
//          'tanneralert' = tanner alert info
function getAlertInfo() {
    var alerttimeout = 480; // alert timeout in sec 8 minutes
    var timestamp = Date.now() / 1000; // time in sec
    var t = localStorage.getItem("alerttime");
    if (t != null && (timestamp - t) < alerttimeout) return; // gets alert async every 8 min.
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
    var timestamp = Date.now() / 1000; // time in sec
    localStorage.setItem("alerttime", timestamp); // save the cache time so we don't keep asking forever
    var s = parseCache(r, "", "FERRY", "FERRYEND");
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
        if (isPhoneGap()) {  // if the alert has disappeared, clear the badge
            var a = localStorage.getItem('alerttext');
            if ((a != null) && (a != "")) window.plugins.PushbotsPlugin.resetBadge();  // clear ios counter
        }
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
    if (gDisplayPage != "mainpage") {
        ShowMainPage();
        return;
    }
    document.getElementById("sidemenu").style.width = "150px";
    //document.getElementByID("mainpage").onclick = function () { CloseMenu(); }; /////////////
    gMenuOpen = true;
}

/* Set the width of the side navigation to 0 */
function CloseMenu() {
    document.getElementById("sidemenu").style.width = "0";
    //document.getElementByID("mainpage").onclick = null;
    gMenuOpen = false;
}

/////////////////////////////////////////////////////////////////////////////////////////
// return the next ferry time as a string. 
//      entry ferrytimes is the array of times and days (see ferrytimeA)
//            ferrytimeK = is the array of times and days for ketron
//            SA = S or A
//      exit  returns html string of ferry times
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
        if (ValidFerryRun(ferrytimes[i + 1])) {
            if (RawTimeDiff(gTimehhmm, ferrytimes[i]) < 13) {
                ft = ft + "<span style='color:red'>" + ShortTime(ferrytimes[i]) + "</span>";
            } else {
                ft = ft + ShortTime(ferrytimes[i]);
            }
            if (ferrytimeK != "") { // add ketron time for this run
                if ((ferrytimeK[i] != 0) && (ValidFerryRun(ferrytimeK[i + 1]))) {
                    ketron = true;
                    ketront = ketront + ShortTime(ferrytimeK[i]);
                } else ketront = ketront + " ------- ";
            }
            if (nruns > 0) break;  // show 2 runs
            ft = ft + ",&nbsp&nbsp ";
            ketront = ketront + ",&nbsp&nbsp ";
            nruns++;
        };
    }
    // we ran out of the schedule today so give the 1st run for tomorrow
    if (i >= ferrytimes.length) ft = ft + FindNextFerryTimeTomorrow(SA);
 
    //if (ShowTimeDiff) ft = ft + " (in " + timeDiff(gTimehhmm, ferrytimes[i]) + ")";
    // ketron only if there is a ketron run, and it is valid. note iketron ponts to 1st run
    if ((ferrytimeK != null) && ketron) ft = ft + "<br><span style='font-weight:bold;color:gray'>Ketron:&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp " + ketront + "</span>";
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
        if (ValidFerryRun(ferrytimes[i + 1])) {
            var rtd = RawTimeDiff(gTimehhmm, ferrytimes[i]);
            if (rtd < 13) return ShortTime(ferrytimes[i]) + "(in <span style='color:red'>" + rtd  + " min)</span>";
            else return ShortTime(ferrytimes[i]) + "(in " + rtd  + " min)";
        }
    }
    // we ran out of the schedule today so give the 1st run for tomorrow
    return "tomorrow";
}

//  FindNextFerryTimeTomorrow - finds the 1st run on the NEXT day
//  Entry   SA = S or A
//  Exit    returns string with 1st valid run for tomorrow
function FindNextFerryTimeTomorrow(SA) {
    var i;
    InitializeDates(1);   // tomorrow
    var ferrytimes = UseFerryTime(SA); // get the ferry time for tomorrow
    for (i = 0; i < ferrytimes.length; i = i + 2) {
        if (ValidFerryRun(ferrytimes[i + 1])) break; // break out on valid time
    }
    InitializeDates(0); // reset to today
    if (i < ferrytimes.length) return "<span style='font-weight:normal'>" + ShortTime(ferrytimes[i]) + " tomorrow</span>";
}


/////////////////////////////////////////////////////////////////////////////////////////
// Finds the next ferry times and puts them into the Dom for the FRONT PAGE
function WriteNextFerryTimes() {
    // ferrytimes = time in 24 hours, S=Steilacoom, A=Anderson Island, 
    // ferrydays:  *=always, H=holiday, 0-6=days of week, AFXY=special rules H=(12/31,1/1,Mem day, 7/3,7/4,labor day,thanksgiving, 12/24,12/25),F=Fuel run 1,3 Wednesday, X=Friday Only labor day-6/30, Y=Fridays only 7/1=labor day
    //var ferrytimeS = [545, "H123456A", 645, "*", 800, "*", 900, "*", 1000, "F", 1200, "*", 1410, "*", 1510, "*", 1610, "*", 1710, "*", 1830, "*", 1930, "*", 2040, "4560H", 2200, "X6H", 2300, "Y"];
    //var ferrytimeA = [615, "H123456A", 730, "*", 830, "*", 930, "*", 1030, "F", 1230, "*", 1440, "*", 1540, "*", 1640, "*", 1740, "*", 1900, "*", 2000, "*", 2110, "4560H", 2230, "X6H", 2330, "Y"];
    // at this point, i = the next valid ferry run

    var str;
    var v = "";
    // check for a DELAYED: or DELAYED nn MIN: and extract the string
    var s = localStorage.getItem('alerttext');
    if(!IsEmpty(s)) {
        var i = s.indexOf("DELAY");
        if(i > 0) {
            var j = s.indexOf(":", i);
            if(j > i) v = "<span style='font-weight:bold;color:red'>" + s.substring(i, j) + "</span><br/>";
        }
    }

    //if (holiday) v = v + "Hoilday<br/>"
    v = v + "<span style='font-weight:bold'>Steilacoom: " + 
         FindNextFerryTime(UseFerryTime("S"), "", "S") + "</span>";
    var a = "</br><span style='font-weight:bold;color:blue'>Anderson:&nbsp&nbsp&nbsp " + 
             FindNextFerryTime(UseFerryTime("A"), UseFerryTime("K"), "A") + "</span>";
    document.getElementById("ferrytimes").innerHTML = v + a;
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
        FA[i] = Number(FA[i]);  // convert to number
        if (isNaN(FA[i])) alert("Data error " + itemname + " " + i);
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

    // loop through the openHours array (each array entry is one business)
    for (var i = 0; i < OpenHours.length; i++) {
        var Oh = OpenHours[i];  // entry for 1 business
        openlist += "<span style='font-weight:bold'>" + Oh.Name + "</span>:";
        openlist += GetOpenStatus(Oh, gMonthDay, gTimehhmm) + "<br/>";
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
    if (IsClosed(Oh.Closed, mmdd)) return " <span style='color:red;font-weight:bold'> Closed today. </span>"
    // loop through the oh.Sch entries. Each entry is for 1 date range.
    for (i = 0; i < Oh.Sc.length; i++) {
        if (((mmdd >= Oh.Sc[i].From) && (mmdd <= Oh.Sc[i].To)) ||
            ((Oh.Sc[i].From > Oh.Sc[i].To) && ((mmdd <= Oh.Sc[i].To) || (mmdd >= Oh.Sc[i].From)))) {

            // ok we have the H entry for the date, now check it
            // array is [sunopen, sunclose, monopen, monclose, tueopen, tueclose,.....satopen, satclose]
            var H = Oh.Sc[i].H;  // array indexed by day of week
            if (H == null) return " <span style='color:red;font-weight:bold'> Closed. </span>";  // if no times, its closed
            opentime = H[gDayofWeek * 2]; // open time hhmm
            closetime = H[gDayofWeek * 2 + 1]; // close time hhmm
            opentime2 = 0; closetime2 = 0;
            if (Oh.Sc[i].H2 != null) {  // if there is H 2nd shift
                opentime2 = Oh.Sc[i].H2[gDayofWeek * 2];
                closetime2 = Oh.Sc[i].H2[gDayofWeek * 2 + 1];
            }
            var openlist; openlist = "";
            // test for open
            if ((hhmm >= opentime) && (hhmm < closetime))
                return " <strong><span style='color:green'> Open </span>till " + VeryShortTime(closetime) + " today</strong>";
            else if ((hhmm >= opentime2) && (hhmm < closetime2))  // 2nd shift for Post Office
                return " <strong><span style='color:green'> Open </span>till " + VeryShortTime(closetime2) + " today</strong>";
            else {
                // closed right now. Find next open time.
                openlist += " <span style='color:red;font-weight:bold'> Closed. </span>";
                if (hhmm < opentime) return openlist + " Opens today " + VeryShortTime(opentime);
                if (hhmm < opentime2) return openlist + " Reopens today " + VeryShortTime(opentime2);
                //  closed today find next open time
                j = gDayofWeek + 1; if (j == 7) j = 0;
                // if it opens tomorrow
                if (H[j * 2] > 0) return openlist + " Opens tomorrow " + VeryShortTime(H[j * 2]);
                // not open tomorrow. find next open day.
                for (var k = 0; k < 7; k++) {  // ensure we check each day only once
                    j++; if (j == 7) j = 0; // handle day rollover
                    if (H[j * 2] > 0) return openlist + " Opens " + gDayofWeekShort[j] + " " + VeryShortTime(H[j * 2]);
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
        if (xhttp.readyState == 4 && xhttp.status == 200) HandleCurrentWeatherReply(JSON.parse(xhttp.responseText));
    }
    xhttp.open("GET", myurl, true);
    xhttp.send();
}
//////////////////////////////////////////////////////////////////////////////////////
//  HandleCurrentWeatherReply - decode the json response from OpenWeatherMap
//  entry r = OBJECT of weather reply
function HandleCurrentWeatherReply(r) {
    var rain;
    var timestamp = Date.now() / 1000; // time in sec
    localStorage.setItem("currentweathertime", timestamp); // save the cache time 

    // create short string for front page
    var icon = "<img src='img/" + r.weather[0].icon + ".png' width=30 height=30>";
    if (typeof r.rain == 'undefined' || typeof r.rain["3h"] == 'undefined') rain = "0";
    else rain = (Number(r.rain["3h"]) / 25.4).toFixed(2);
    var current = icon + " " + StripDecimal(r.main.temp) + "&degF, " + r.weather[0].description + ", " + DegToCompassPoints(r.wind.deg) + " " +
        StripDecimal(r.wind.speed) + " mph" + ((rain!="0")? (", " + rain + " rain") : "");
    localStorage.setItem("currentweather", current);
    document.getElementById("weather").innerHTML = current; // jquery equivalent. Is this really easier?
    // detailed string for weather detail page
    gDateSunrise = new Date(Number(r.sys.sunrise) * 1000);
    gDateSunset = new Date(Number(r.sys.sunset) * 1000);
    localStorage.setItem("sunrise", gDateSunrise.getTime()); // save
    localStorage.setItem("sunset", gDateSunset.getTime()); // save
    var currentlong = icon + r.weather[0].description + ", " + StripDecimal(r.main.temp) + "&degF, " +
        r.main.humidity + "% RH<br/>Wind " + DegToCompassPoints(r.wind.deg) + " " + StripDecimal(r.wind.speed) + " mph " +
            ", " + rain + " in. rain" +
        "<br/><span style='color:green'>Sunrise: " + gDateSunrise.toLocaleTimeString() +
        "</span><span style='color:black'> | </span><span style='color:orangered'>Sunset: " + gDateSunset.toLocaleTimeString() + "</span>";

    localStorage.setItem("currentweatherlong", currentlong);
} // end of function


//////////////////////////////////////////////////////////////////////////////////////////////////////////
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
    var forecast = "Forecast: " + maxt + "&deg/" + mint + "&deg, " +
        r.weather[0].description + ", " + DegToCompassPoints(r.wind.deg) + " " + StripDecimal(r.wind.speed) + " mph ";
    localStorage.setItem("forecast", forecast);
    document.getElementById("forecast").innerHTML = forecast;

    // if the forecast page is being displayed, regenerate it
    if (gDisplayPage == "weatherpage") generateWeatherForecastPage(); 
}  // end of function



/////////////////////////////////////////////////////////////////////////////////////////////
// get tide data using the aeris api and returning a jsonp structure. This is the only way to get data from a different web site.
// License as of 2/8/16 is for 750 hits/day for free.
//  entry: localStorage "jsontides" = tide data  (refreshed nightly)
//  exit: gForceCacheReload = true to reload tide data.  
//          html populated.
function ShowNextTides() {
    var hilow;
    var nextTides;
    var oldtide = -1;
    var newtidetime, oldtidetime, newtideheight, oldtideheight;
    var json = localStorage.getItem("jsontides");
    // get tide data
    if (json === null) {
        gForceTideReload = true;
        document.getElementById("tides").innerHTML = "Tide data not available.";
        return;
    }

    var periods = JSON.parse(json); // parse it
    // roll through the reply in jason.response.periods[i]
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
            if (thisperiod.type == 'h') {
                nextTides = "Incoming. Now ";
                document.getElementById("tidestitle").innerHTML = "TIDE &uarr;";
            } else {
                nextTides = "Outgoing. Now ";
                document.getElementById("tidestitle").innerHTML = "TIDE &darr;";
            }
            nextTides += cth.toFixed(1) + "ft.<br/>Next: " + hilow + " " + thisperiod.heightFT + " ft. at " + ShortTime(tidehhmm) +
                 " (in " + timeDiff(gTimehhmm, tidehhmm) + ")<br/>";
            oldtide = 1;
        } else if (oldtide == 1) {  // save next tide
            nextTides += hilow + " " + thisperiod.heightFT + " ft. at " + ShortTime(tidehhmm) + " (in " + timeDiff(gTimehhmm, tidehhmm) + ")";
            document.getElementById("tides").innerHTML = nextTides;
            return;
        }
    }
    gForceTideReload = true; // if we haven't gotten today's tides, reload it
}


////////////////////////////////////////////////////////////////////////////////////////////
// DisplayNextEvent


///////////////////////////////////////////////////////////////////////////////////////////////
// DisplayComingEvents - display the events in the 'comingevents'  or 'comingactivities' 
//          local storage object on the main page.
//      Displays all activities or events for a day.
//  Entry   CE = string of coming events: rows (separated by \n) 1 for each event or activity
//  Exit    returns the information to display on the main screen
function DisplayNextEvents(CE) {
    var datefmt = ""; // formatted date and event list
    var iCE; // iterator through CE
    var aCE; // CE split array 
    var aCEyymmdd; // yymmdd of Calendar Entry
    var DisplayDate = 0; // event date we are displaying
    var nEvents = 0; // number of events displayed
    if (CE === null) return;
    // break CE up into rows
    CE = CE.split("\n");  // break it up into rows
    if (CE == "") return;
    // roll through the next 30 days
    for (iCE = 0; iCE < CE.length; iCE++) {
        if (CE[iCE] == "") continue; // skip blank lines
        aCE = CE[iCE].split(';');  // split the string
        //  advance schedule date to today
        aCEyymmdd = Number(aCE[0]);
        if (aCEyymmdd < gYYmmdd) continue; // not there yet.
        // if the entry is for today and it is done, skip it
        if (aCEyymmdd == gYYmmdd && Number(aCE[2]) < gTimehhmm) continue; // if today and it is done, skip it
        // found it
        //if (aCEmonthday != gMonthDay && datefmt != "") return datefmt; // don't return tomorrow if we all the stuff for today
        if ((aCEyymmdd != DisplayDate) && (nEvents >= 2) && (datefmt != "")) return datefmt; // don't return tomorrow if we all the stuff for today

        // if Today
        if (aCEyymmdd == gYYmmdd) {
            if (datefmt == "") datefmt += "<span style='font-weight:bold;color:green'>TODAY</span><br/>";  // mark the 1st entry only as TODAY
            datefmt += " <strong>" + VeryShortTime(aCE[1]) + "-" + VeryShortTime(aCE[2]) + "</strong> " + aCE[4] + " @ " + aCE[5] + "<br/>";
            nEvents = 99; // ensure only today
            DisplayDate = aCEyymmdd;
            continue;
        }
        // if Tomorrow or another day show the 1st 3 events
        // put date in
        //if (aCEyymmdd != DisplayDate) {
        //    if (aCEyymmdd == (gYYmmdd + 1)) datefmt += "<strong>Tomorrow</strong>";
        //    else if (aCEyymmdd <= (gYYmmdd + 6)) datefmt += "<strong>" + gDayofWeekShort[GetDayofWeek(aCE[0])] + "</strong>";  // fails on month chagne
        //    else datefmt += "<strong>" + gDayofWeekShort[GetDayofWeek(aCEyymmdd)] + " " + aCE[0].substring(2, 4) + "/" + aCE[0].substring(4, 6) + "</strong>";
        //}
        //datefmt += " " + VeryShortTime(aCE[1]) + "-" + VeryShortTime(aCE[2]) + " " + aCE[4] + " @ " + aCE[5] + "<br/>";

        if (aCEyymmdd != DisplayDate) {
            if (aCEyymmdd == (gYYmmdd + 1)) datefmt += "<strong>Tomorrow</strong><br/>";
            else if (aCEyymmdd <= (gYYmmdd + 6)) datefmt += "<strong>" + gDayofWeekNameL[GetDayofWeek(aCE[0])] + "</strong><br/>";  // fails on month chagne
            else datefmt += "<strong>" + gDayofWeekShort[GetDayofWeek(aCEyymmdd)] + " " + aCE[0].substring(2, 4) + "/" + aCE[0].substring(4, 6) + "</strong><br/>";
        }
        //datefmt += "<span style='margin-left:10px;'>" + VeryShortTime(aCE[1]) + "-" + VeryShortTime(aCE[2]) + " " + aCE[4] + " @ " + aCE[5] + "</span><br/>";
        datefmt += VeryShortTime(aCE[1]) + "-" + VeryShortTime(aCE[2]) + " " + aCE[4] + " @ " + aCE[5] + "<br/>";
        DisplayDate = aCEyymmdd;
        nEvents++; // count the events
        if (nEvents >= 3) break; // exit after 3 events that are not today
    }
    return datefmt; // end case
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
    //parseCache(data, "ferrytimess", "FERRYTIMESS", "\n");
    //parseCache(data, "ferrytimesa", "FERRYTIMESA", "\n");
    //parseCache(data, "ferrytimesk", "FERRYTIMESK", "\n");
    parseCache(data, "ferrytimess", "FERRYTS", "\n");
    parseCache(data, "ferrytimesa", "FERRYTA", "\n");
    parseCache(data, "ferrytimesk", "FERRYTK", "\n");

    //parseCache(data, "openhours", "OPENHOURS", "OPENHOURSEND");  obsolete in ver 1.5 5/2016.
    //parseCache(data, "morehours", "MOREHOURS", "MHEND");
    parseCache(data, "emergency", "EMERGENCY", "EMERGENCYEND");
    parseCache(data, "links", "LINKS", "LINKSEND");
    s = parseCache(data, "openhoursjson", "OPENHOURSJSON", "OPENHOURSJSONEND");
    if (s != "") OpenHours = JSON.parse(s);  // parse it
    // new ferry schedule 
    //parseCache(data, "ferrytimess2", "FERRYTIMESS2", "\n");
    //parseCache(data, "ferrytimesa2", "FERRYTIMESA2", "\n");
    //parseCache(data, "ferrytimesk2", "FERRYTIMESK2", "\n");
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
    parseCacheRemove(data, "googleplayticketlink", "GOOGLEPLAYTICKETLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "googleplaylink", "GOOGLEPLAYLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "iosticketlink", "IOSTICKETLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "ferrypagelink", "FERRYPAGELINK", "\n"); // ferry schedule
    parseCacheRemove(data, "googlemaplink", "GOOGLEMAPLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "applestorelink", "APPLESTORELINK", "\n"); // ferry schedule
    parseCacheRemove(data, "parkslink", "PARKSLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "newslink", "NEWSLINK", "\n"); // ferry schedule
    parseCacheRemove(data, "customtidelink", "CUSTOMTIDELINK", "\n"); // ferry schedule
    parseCacheRemove(data, "noaalink", "NOAALINK", "\n"); // ferry schedule


    // coming events (added 6/6/16). from the file comingevents.txt, pulled by getdailycache.php
    // format: COMINGEVENTS ...events...ACTIVITIES...activities...COMINGEVENTSEND
    parseCache(data, "comingevents", "COMINGEVENTS", "ACTIVITIES");
    parseCache(data, "comingactivities", "ACTIVITIES", "COMINGEVENTSEND");
    localStorage.setItem("comingeventsloaded", gMonthDay); // save event loaded date/time
    FixDates("comingevents");
    FixDates("comingactivities");
    document.getElementById("nextevent").innerHTML = DisplayNextEvents(localStorage.getItem("comingevents"));
    document.getElementById("nextactivity").innerHTML = DisplayNextEvents(localStorage.getItem("comingactivities"));

    // tides (added 6/6/16)
    var json = JSON.parse(parseCache(data, "", "TIDES", "TIDESEND"));
    if (json.success == true) {
        localStorage.setItem("jsontides", JSON.stringify(json.response.periods)); // store the full json reponse structure
        localStorage.setItem("tidesloadedmmdd", gMonthDay);
        ShowNextTides();
    }

    if (gReloadCachedDataButtonInProgress) {
        gReloadCachedDataButtonInProgress = false;
        alert("Data has been reloaded.");
    }
}

/////////////////////////////
// parseCache - returns the string from the daily cache
//  data is in the form:  <startstr>\n data <endstr>
//  localstoragename = name of local storage item, "" to not store it
//  startstr = starting string, endstr = ending string. can be "\n"
//  exit    returns the string. "" if no string.
function parseCache(data, localstoragename, startstr, endstr) {
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
    var s = parseCache(data, localstoragename, startstr, endstr);
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
    gLastUpdatems = gTimeStampms;
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

    // get tides once/day
    //getTideData();  moved to getDailyCache

    // current weather every 20 
    getCurrentWeather(); // gets weather async every 20 min. Timer is in routine.

    // forecast every 4 hours
    getForecast(); // gets weather async every 4 hour. Timer is in routine.

    // alerts every 12 minutes
    getAlertInfo();

    DisplayLoadTimes();
}

/////////////////////////////////////////////////////////////////////////////
// focus and blur events
function focusEvent() {
    if (gMyTimer == null) {  // if timer is off
        gMyTimer = setInterval("timerUp()", 60000);  // restart timeout in milliseconds. currently 60 seconds
        timerUp(); // restart the timer if needed
    } else {
        var d = new Date();
        if ((d.getTime() - gLastUpdatems) > 60000) timerUp(); // if > 60 secs
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

function onResume() {
    focusEvent();
}

function backKeyDown() {
    // Call my back key code here.
    if (gDisplayPage == 'mainpage' && isPhoneGap()) navigator.app.exitApp();
    ShowMainPage();
}

//////////////////////////////////////////////////////////////////////////////////
// show all cached data, i.e. all data in localStorage.
// 
function ShowCachedData() {
    WriteNextFerryTimes();  // show cached ferry schedule
    ShowNextTides(); // show cached tide data
    DisplayAlertInfo();
    ShowOpenHours(); //  open hours
    document.getElementById("nextevent").innerHTML = DisplayNextEvents(localStorage.getItem("comingevents"));
    document.getElementById("nextactivity").innerHTML = DisplayNextEvents(localStorage.getItem("comingactivities"));
    //$("#nextevent").html(DisplayNextEvents(localStorage.getItem("comingevents")));
    //$("#nextactivity").html(DisplayNextEvents(localStorage.getItem("comingactivities")));
    var s = localStorage.getItem("message");
    if (!IsEmpty(s)) document.getElementById("topline").innerHTML = s;


    var s = localStorage.getItem("forecast");
    if (s != null) document.getElementById("forecast").innerHTML = s;

    s = localStorage.getItem("currentweather"); // cached current weather
    if (s != null) document.getElementById("weather").innerHTML = s;
    DisplayLoadTimes();

}

////////////////////////////////////////////////////////////////////////////////////
// Reload cached data - calls all the ajax calls to get the data and recache it in localStorage.
//
function ReloadCachedData() {
    //alert("reload cached data");
    InitializeDates(0);
    GetDailyCache();  // no limit
    //GetComingEvents();// merged into GetDailyCache on 6/6/16
    localStorage.removeItem("alerttime");
    getAlertInfo();
    //localStorage.removeItem("tidesloadedmmdd"); // force tides
    //getTideData();// no limitmerged into GetDailyCache on 6/6/16
    localStorage.removeItem("forecasttime"); // force forecast reload at start of new day
    getForecast();// limited to every 120 min
    localStorage.removeItem("currentweathertime"); // force weather reload at start of new day
    getCurrentWeather();// limited to every 20 min
    DisplayLoadTimes();
}

var gReloadCachedDataButtonInProgress = false;
function ReloadCachedDataButton() {
    if (gMenuOpen) CloseMenu();  // close the menu            if (gMenuOpen) CloseMenu();  // close the men
    gReloadCachedDataButtonInProgress = true;
    ReloadCachedData();

}

//////////////////////////////////////////////////////////////////////////////////////
// DisplayLoadTimes() displays time data loaded
function DisplayLoadTimes() {
    document.getElementById("reloadtime").innerHTML = "App started " + gAppStartedDate +
        ", update counter: " + gUpdateCounter +
        ",<br/>Cached reloaded " + localStorage.getItem("dailycacheloaded") + " @" + localStorage.getItem("dailycacheloadedtime") +
        ", Tides:" + localStorage.getItem("tidesloadedmmdd") +
        ", PBotsInit:" + ((gTimeStampms - Number(LSget("pushbotstime"))) / 3600000).toFixed(2) + " hr ago" +
        "<br/>k=" + DeviceInfo() + " n=" + localStorage.getItem("Cmain") + " p=" + localStorage.getItem("pagehits") + 
        "<br/>Forecast:" + Math.ceil(((gTimeStampms / 1000) - Number(localStorage.getItem("forecasttime"))) / 60) + " min ago, " +
        "CurrentWeather:" + Math.ceil(((gTimeStampms / 1000) - Number(localStorage.getItem("currentweathertime"))) / 60) + " min ago ";

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
    if (isPhoneGap() && !isAndroid()) document.getElementById("h1menu").innerHTML = "&larr;back";
    else document.getElementById("h1menu").innerHTML = "&nbsp&larr;&nbsp";
}
///////////////////////////////////////////////////////////////////////////////////////////////////
// show main page
function ShowMainPage() {
    SetPageHeader(" Anderson Island Assistant");
    document.getElementById("h1menu").innerHTML = "&#9776&nbsp&nbsp";
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

//<!-- FERRY PAGE -------------------->
//<script>
//==== FERRY SCHEDULE =======================================================================================

// FERRY SCHEDULE PAGE as a DIV.  Replaces 'localferryschedule.html'

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
            // Android
            // Default handlers "com.hutchind.cordova.plugins.launcher"
            var successCallback = function (data) {
                alert("Success!");
                // if calling canLaunch() with getAppList:true, data will contain an array named "appList" with the package names of applications that can handle the uri specified.
            };
            var errorCallback = function (errMsg) {
                alert("Error! " + errMsg);
                var link = GetLink("googleplayticketlink", 'https://play.google.com/store/apps/details?id=com.ttpapps.pcf');
                window.open(link, '_system');
            }
            var pkg = GetLink("androidpackageticketlink", "com.ttpapps.pcf"); // android ticket package
            window.plugins.launcher.launch({ packageName: pkg}, successCallback, errorCallback);
            //  com.lampa.startapp
            //var sApp = startApp.set({ "package": pkg});
            //sApp.start(function () { /* success */
            //}, function (error) { /* fail */
            //    var link = GetLink("googleplayticketlink", 'https://play.google.com/store/apps/details?id=com.ttpapps.pcf');
            //    window.open(link, '_system');
            //});
        } else {
            // IOS
            // Default handlers "com.hutchind.cordova.plugins.launcher"
            var successCallback = function (data) {
                alert("Success!");
                // if calling canLaunch() with getAppList:true, data will contain an array named "appList" with the package names of applications that can handle the uri specified.
            };
            var errorCallback = function (errMsg) {
                alert("Error! " + errMsg);
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
        window.open('https://tickets.piercecountywa.org/', '_system');
    }
}


/////////////////////////////////////////////////////////////////////////////////////////
// Loads the next ferry times into the global 'table' as a row for each run. 
// ferrytimesS, ferrytimesA is the array of times and days for Steelacoom and AI;
function BuildFerrySchedule(table, ferrytimesS, ferrytimesA, ferrytimesK) {
    var i;
    var ft;
    var amcolor = "#f0ffff";
    var extrat = 13; // extra time in display 12 minutes
    var boldS = false, boldA = false, boldK = false; 
    // roll through the ferry times, skipping runs that are not valid for today
    for (i = 0; i < ferrytimesS.length; i = i + 2) {
        if ((gTimehhmm >= (ferrytimesS[i] + extrat)) && (gTimehhmm >= (ferrytimesA[i] + extrat)) && (gTimehhmm > Number(ferrytimesK[i]))) continue;  // skip ferrys that have alreaedy run
        //if ((gTimehhmm >= (Number(ferrytimesS[i]) + extrat)) && (gTimehhmm >= (Number(ferrytimesA[i]) + extrat)) && (gTimehhmm > Number(ferrytimesK[i]))) continue;  // skip ferrys that have alreaedy run
        // now determine if the next run will run today.  If it is a valid run, break out of loop.
        if (ValidFerryRun(ferrytimesS[i + 1])) {
            // Steelacoom
            var row1, row1col1, row1col2;
            row1 = table.insertRow(-1);
            row1col1 = row1.insertCell(0);
            row1col1.style.border.width = 1;
            row1col1.innerHTML = "&nbsp&nbsp" + FormatTime(ferrytimesS[i]);
            if (gTimehhmm > ferrytimesS[i]) row1col1.style.color = "lightgray";
            row1col1.style.border = "thin solid black";
            if (ferrytimesS[i] < 1200) row1col1.style.backgroundColor = amcolor;

            // Anderson Island;
            row1col2 = row1.insertCell(1);
            row1col2.innerHTML = "&nbsp&nbsp" + FormatTime(ferrytimesA[i]);
            if (gTimehhmm > ferrytimesA[i]) row1col2.style.color = "lightgray";
            else row1col2.style.color = "darkblue";
            row1col2.style.border = "thin solid black";
            if (ferrytimesA[i] < 1200) row1col2.style.backgroundColor = amcolor;

            // Ketron
            var row1col3 = row1.insertCell(2);
            if (ferrytimesK[i] != 0) {
                if (ValidFerryRun(ferrytimesK[i + 1])) {
                    row1col3.innerHTML = "&nbsp&nbsp" + FormatTime(ferrytimesK[i]);
                    if (gTimehhmm > ferrytimesK[i]) row1col3.style.color = "lightgray";
                    else row1col3.style.color = "brown";
                    row1col3.style.border = "thin solid black";
                    if (ferrytimesK[i] < 1200) row1col3.style.backgroundColor =amcolor;
                }
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

//////////////////////////////////////////////////////////////////////////////////
// ScheduleByDate - ask for date and then run schedule
function ScheduleByDate() {
    var userdate = GetDateFromUser();
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
    row1col1.style.backgroundColor = "lightblue";
    row1col1.style.color = "black";
    row1col1.innerHTML = "&nbsp Steilacoom &nbsp";
    row1col1 = row1.insertCell(1);
    row1col1.style.border.width = 1;
    row1col1.style.border = "thin solid black";
    row1col1.style.backgroundColor = "lightblue";
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

//<!-- OPEN HOURS -->
//<script>
//======== OPEN HOURS ============================================================================

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
function FormatOneBusiness(Oh, mmdd, showall) {
    var openlist = "<div style='background-color:lightblue;padding:6px'><span style='font-weight:bold;font-size:18px;color:blue'>" + Oh.Name + "&nbsp&nbsp</span><span style='font-weight:bold'>" + GetOpenStatus(Oh, mmdd, gTimehhmm) + " </span></div>";
    if (showall) openlist += Oh.Desc + "<br/>" + Oh.Addr + "<br/>";
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
            // loop through Sun - Sat
            for (var j = 0; j < 7; j++) {
                var opentimetoday;
                opentimetoday = H[j * 2]; // hhmm-hhmm open today
                if (opentimetoday > 0) {
                    if (j == gDayofWeek) openlist += "<strong>"; // bold today
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
    return openlist + "&nbsp&nbsp " +
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
    var t = Oh.Name;
    var mmdd = gMonthDay;
    if (t == "Store") t = "General Store";
    SetPageHeader(t);
    //document.getElementById("businesspageh1").innerHTML = "<button class='buttonback' onclick='ShowOpenHoursPage()'>&larr;BACK</button>" + t;

    var openlist = "<p style='font-weight:bold;font-size:medium'>&nbsp&nbsp&nbsp " + t + ": " + GetOpenStatus(Oh, mmdd, gTimehhmm) + " </p>";

    openlist += "<div style='font-size:small'><div style='width:100%;background-color:lightblue;padding:6px'>DESCRIPTION</div><p style='margin:10px'>"
        + "<button><a style='display:normal;text-decoration:none;' href='tel:" + Oh.Phone + "'>&nbsp Call " + Oh.Phone + "&nbsp</a></button>&nbsp&nbsp " +
        Oh.Desc +
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
            alert(e);
            gForceCacheReload = true;
        }
    } else gForceCacheReload = true;
}



//</script>
//<!-- COMING EVENTS ---------------->
//<script>
//========= COMING EVENTS ===========================================================================

////////////////////////////////////////////////////////////////////////////////////////////////
// DisplayComingEventsPage(type)
//  entry   type = 'events' or 'activities'
var EventFilter = ""; //letter to filter for
var EventDisp = ""; // event display type, L, W, M

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
    var endyymmdd;
    if (localStorage.getItem("eventtype") == "events") endyymmdd = BumpyymmddByMonths(gYYmmdd, 6);
    else endyymmdd = BumpyymmddByMonths(gYYmmdd, 1);
    var thisweek = GetWeekofYear(gMonthDay); // this week #

    // roll through the CE array.  Dates are yymmdd
    for (iCE = 0; iCE < CE.length; iCE++) {
        if (CE[iCE] == "") continue; // skip blank lines
        aCE = CE[iCE].split(';');  // split the string
        if ((EventFilter != "") && (EventFilter != aCE[3])) continue;  // skip entry if it doesnt match
        //  advance schedule date to today
        var CEyymmdd = Number(aCE[0]); // yymmdd
            // ALTERNATE on the fly year addition
            // if(CE[iCE].substr(0,15) == "0101;0000;0000;") CEyear = Number(aCE[4]);
            // CEyymmdd = CEYear*10000;
        if (CEyymmdd > endyymmdd) return; // past end date (one month)
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
        col2.innerHTML = aCE[4];//event
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
    var as = "Tap entry to add to your ";
    if (!isPhoneGap()) as += "Google ";
    as += "calendar.<br/> <table style='border:thin solid black;border-collapse:collapsed'>";
    var CE = GetEvents().split("\n");
    for (iCE = 0; iCE < CE.length; iCE++) {
        if (CE[iCE] == "") continue; // skip blank lines
        var aCE = CE[iCE].split(';');  // split the string
        if (d < aCE[0]) break;
        if (d != aCE[0]) continue;
        var t99 = aCE[1].substr(0, 2) + '99'; // hh99
        if ((t == aCE[1]) || (t == '9999') || (t == t99)) {
            nc++;
            // create table entry. id = the numeric index into the CE array
            as += "<tr id='" + iCE.toFixed() + "' onclick='AddToCal(this.id)'><td style='border:thin solid black'><strong>" +
                formatDate(aCE[0]) + " " + VeryShortTime(aCE[1]) + "-" + VeryShortTime(aCE[2]) + ":</strong> " +
                 aCE[4] + " at " + aCE[5] + "<br/>Sponsor: " + aCE[6] + "<br/>";
            if (aCE.length >= 8) as += aCE[7] + "<br>";
            as += "</td></tr>";
        }

    }
    if (nc == 0) return;
    as += "</table>";
    Dialog(as, "Schedule Detail");
    //alert(as);
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

    var startyymmdd = Bumpyymmdd(gYYmmdd, -gDayofWeek); // back up to beginning of month
    //if (year % 4 == 0) gDaysInMonth[2] = 29; // leap year
    // compute starting date
    //var dd = dayofmonth - gDayofWeek; // starting dayy of the month
    //var mm = month;
    //if (dd <= 0) { mm--; dd = gDaysInMonth[mm] + dd; } // if we had to back of
    //var startmmdd = mm * 100 + dd;
    var yymmdd = startyymmdd;

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

    // build the month table with all rows and columns. Each day has an id of 'yymmdd9999'.
    for (w = 1; w < 12; w++) {
        var rowN = table.insertRow(-1);
        row = table.insertRow(-1);
        //row.style.border = "thin solid blue";
        // day rows with date
        for (i = 0; i < 7; i++) {
            // cell with date
            col = rowN.insertCell(i);
            if ((yymmdd % 100) == 1) {
                col.innerHTML = formatDate(yymmdd); // use month on 1st day
                col.style.fontWeight = 'bold';
            }
            else col.innerHTML = (yymmdd % 100).toFixed(0);
            col.style.color = "darkblue";
            if (yymmdd == gYYmmdd) col.style.backgroundColor = "yellow";
            else col.style.backgroundColor = "azure";
            col.style.border = "thin solid lightblue";
            // cell that will hold the events
            col = row.insertCell(i);
            col.innerHTML = "&nbsp";
            col.style.border = "thin solid lightblue";
            col.id = yymmdd.toFixed(0) + '9999';  // id = yymmdd9999
            col.onclick = function () { tabletext(this.id) }
            if (yymmdd == gYYmmdd) col.style.backgroundColor = "lightyellow";  // make today yellow
            yymmdd = Bumpyymmdd(yymmdd, 1); // quick bump of yymmdd//
        }
    }

    var endyymmdd = yymmdd;
    // roll through the CE array for the month days
    for (iCE = 0; iCE < CE.length; iCE++) {
        if (CE[iCE] == "") continue; // skip blank lines
        aCE = CE[iCE].split(';');  // split the string
        //  advance schedule date to today
        var dateCE = Number(aCE[0]); // yymmdd
        if (dateCE > endyymmdd) break; // past end of mothb
        if (dateCE < startyymmdd) continue; // if before start of month
        if ((EventFilter != "") && (EventFilter != aCE[3])) continue;  // skip entry if it doesnt match

        // add to entry using the id to find it in the DOM
        var e;
        e = "<span style=color:" + eventcolor(aCE[3]) + "><strong>" + VeryShortTime(aCE[1]) + "</strong> " +
              aCE[4] + "</span>";// add time 
        var id = aCE[0] + "9999"; // id=yymmdd9999
        var c = document.getElementById(id);
        c.innerHTML += e + "<br/>";
    } // end for
} // end function

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

//<!-- TIDES ------------------------>
//<script>
//===== TIDES =======================================================================================
var gUserTideSelection = false; // true when user sets tides
var gPeriods;  // parsed data
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
    gPeriods = JSON.parse(json);
    if (gPeriods == null) return;
    var i = ShowTideDataPage(gPeriods, true);
    showingtidei = i;
    GraphTideData(gPeriods[i - 1].heightFT, gPeriods[i].heightFT, gPeriods[i + 1].heightFT,
        gPeriods[i - 1].dateTimeISO, gPeriods[i].dateTimeISO, gPeriods[i + 1].dateTimeISO, true);
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
                currentTide += ShortTime(tidehhmm) + " (for " + timeDiff(tidehhmm, gTimehhmm) + ")";
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
                    currentTide = "<span style='font-size:16px;font-weight:bold;color:blue'>" +
                        "Now: " + tideheight.toFixed(1) + " ft. &nbsp Date:" + formatDate(gMonthDay) +
                        "&nbsp&nbsp&nbsp<span style='background-color:silver;font-weight:normal' onclick='ShowCustom()'>&nbsp Change...&nbsp</span><br/>" + currentTide;
                    // calculate time till next tide                                 
                    currentTide += "<br/>" + hilow + " tide: " + periods[i].heightFT + " ft. at " + ShortTime(tidehhmm) + " (in " + timeDiff(gTimehhmm, tidehhmm) + ")";
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
    if (i > (gPeriods.length - 2)) i = gPeriods.length - 2;
    showingtidei = i;
    gUserTideSelection = true;
    GraphTideData(gPeriods[i - 1].heightFT, gPeriods[i].heightFT, gPeriods[i + 1].heightFT,
        gPeriods[i - 1].dateTimeISO, gPeriods[i].dateTimeISO, gPeriods[i + 1].dateTimeISO, false);
    var h = Number(gPeriods[i].dateTimeISO.substring(11, 13)); // tide hour
    var tidehhmm = (h * 100) + Number(gPeriods[i].dateTimeISO.substring(14, 16));
    var hilo = "HIGH tide: ";
    if (gPeriods[i].heightFT < gPeriods[i - 1].heightFT) hilo = "Low tide: ";
    document.getElementById("tidepagecurrent").innerHTML = "<span style='font-size:16px;font-weight:bold;color:blue'> Date:" +
      gPeriods[i].dateTimeISO.substring(5, 7) + "/" + gPeriods[i].dateTimeISO.substring(8, 10) +
      "&nbsp&nbsp&nbsp<span style='background-color:silver;' onclick='ShowCustom()'>&nbsp Change...&nbsp</span><br/>" +
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
//  Entry: tide1t, tide2t, tide3t = 3 tide points in feet, text (HLH or LHL)
//         t1, t2, t3 = corresponding times in dateTimeISO text format: 2016-05-15T19:55:00-07:00
//         NOTE: current tide must be between t1 and t2
function GraphTideData(tide1t, tide2t, tide3t, t1t, t2t, t3t, showtoday) {
    var i;
    var canvas = document.getElementById("tidecanvas");
    var axes = {}, ctx = canvas.getContext("2d");
    //canvas.width = window.innerWidth; canvas.height = window.innerWidth;
    var w = canvas.width; var h = canvas.height;
    ctx.textAlign = "start";

    // convert tides to numbers
    var tide1, tide2, tide3;
    tide1 = Number(tide1t); tide2 = Number(tide2t); tide3 = Number(tide3t);
    var LB = tide1; if (tide2 < LB) LB = tide2; if (tide3 < LB) LB = tide3; LB = Math.floor(LB - .9); // lower bound
    var UB = tide1; if (tide2 > UB) UB = tide2; if (tide3 > UB) UB = tide3; UB = Math.floor(UB + 1.99); // upper bound
    var pixelsfoot = h / (UB - LB);  // pixels per foot

    // convert time to numbers as fp hours from 0 to 48.
    var t1hhmm = Number(t1t.substring(11, 13)) * 100 + Number(t1t.substring(14, 16));
    var t2hhmm = Number(t2t.substring(11, 13)) * 100 + Number(t2t.substring(14, 16));
    var t1 = tHours(t1t); var t2 = tHours(t2t); var t3 = tHours(t3t);
    if (t2 < t1) t2 += 24; if (t3 < t2) t3 += 24;
    var tLB = Math.floor(t1 - 1); // time lower bound
    var tUB = Math.floor(t3 + .99); // time upper bound

    var pixelshour = w / (tUB - tLB);
    var x0 = 0; // x offset to 0

    // draw background
    ctx.fillStyle = "#A0D2FF";
    ctx.fillRect(0, 0, w, h);
    // do sunnrise lighter
    var sunrisefp = gDateSunrise.getHours() + gDateSunrise.getMinutes() / 60; // convert to floating pt
    var sunsetfp = gDateSunset.getHours() + gDateSunset.getMinutes() / 60;
    // from 0 to 24
    ctx.fillStyle = "#B0E2FF"; //"#B8EAFF"; //"#C0F2FF";  // snrise times
    if (sunrisefp <= tLB) ctx.fillRect(0, 0, (sunsetfp - tLB) * pixelshour, h);
    else ctx.fillRect((sunrisefp - tLB) * pixelshour, 0, (sunsetfp - tLB) * pixelshour, h);
    // from 24 to 48
    ctx.fillRect((sunrisefp - tLB + 24) * pixelshour, 0, (sunsetfp - tLB + 24) * pixelshour, h);

    // draw y axis (tide feet)
    ctx.beginPath();
    ctx.strokeStyle = "rgb(128,128,128)";
    ctx.moveTo(x0, 0); ctx.lineTo(x0, h);  // Y axis
    ctx.stroke();

    // draw tide grid lines
    ctx.strokeStyle = "#ffffff";
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
        hr = VeryShortTime(i < 24 ? i * 100 : (i - 24) * 100).substr(0, 2);
        if (hr == "no") hr = "12";
        // draw verticals for time every 4 hours
        if ((i % 4) == 0) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, h);
            ctx.stroke();
        }
        ctx.fillText(hr, x - 8, h - 3);
        x += pixelshour;  // bump to next one
    }

    // draw the 2 sine waves
    DrawCurve(ctx, tide1, tide2, t1, t2, pixelsfoot, pixelshour, h, tLB, LB);
    DrawCurve(ctx, tide2, tide3, t2, t3, pixelsfoot, pixelshour, h, tLB, LB);
    DrawCurve(ctx, tide3, tide2, t3, t3 + 6, pixelsfoot, pixelshour, h, tLB, LB); // fill out last hour with made up tide

    // draw vertical for t2 which is next high/low
    ctx.lineWidth = 1;
    ctx.strokeStyle = "#A0A0A0";
    ctx.beginPath();
    x = (t2 - tLB) * pixelshour;
    ctx.moveTo(x, h - (tide2 - LB) * pixelsfoot); ctx.lineTo(x, h);
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
        var up = "\u2191"; if (tide2 < tide1) up = "\u2193";  // up down arrow
        tide = CalculateCurrentTideHeight(t2hhmm, t1hhmm, tide2, tide1);
        ctx.fillStyle = "#ff0000";
        ctx.font = "16px Arial";
        ctx.fillText("@ " + tide.toFixed(1) + " ft " + up, now - pixelshour, 14);
    }

    // label the date using the date for tide2 
    ctx.fillStyle = "#0000ff";
    ctx.fillText(t2t.substring(5, 7) + "/" + t2t.substring(8, 10), w * 0.7, 14);
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
    var tidedate = GetDateFromUser();
    if (tidedate == "") return;
    gUserTideSelection = true;
    MarkPage("1");
    getCustomTideData(tidedate);

}

///////////////////////////////////////////////////////////////////
// GetDateFromUser - asks user for date and returns as mm/dd/yy
//  return mm/dd/yy or ""
function GetDateFromUser() {
    var tidedate = prompt("Please enter the date as mm/dd or mm/dd/yyyy", gMonth + "/" + gDayofMonth);
    if (tidedate == null) return "";
    // validate the date
    var tda = tidedate.split("/");
    if (tda.length != 2 && tda.length != 3) {
        alert("Invalid date. An example of a valid date is: 1/22");
        return "";
    }
    var m = Number(tda[0]); // month
    var d = Number(tda[1]);  // day
    if (isNaN(m) || isNaN(d) || (m < 1) || (m > 12) || (d < 1) || (d > 31)) {
        alert("Invalid date. An example of a valid date is: 1/22");
        return;
    }
    if (tda.length == 3) return tidedate;
    else return tidedate + "/" + gYear;
}

/////////////////////////////////////////////////////////////////////////////////////////////
// getCustomTideData get tide data using the aeris api and returning a jsonp structure. This is the only way to get data from a different web site.
// used only for custom date queries, not for normal tides.
// License as of 2/8/16 is for 750 hits/day for free.
//  fromdate = optional starting date for the tides
//  data is used to display tide data. It is not stored.
function getCustomTideData(fromdate) {
    //$("#tideButton").hide();  // hide the user button
    //$.ajax({
    //url: 'http://api.aerisapi.com/tides/9446705?client_id=U7kp3Zwthe8dc19cZkFUz&client_secret=4fHoJYS6m9T7SERu7kkp7iVwE0dewJo5zVF38tfW&from=' + fromdate + '&to=+48hours',
    //dataType: 'jsonp',
    //success: function (json) {
    var myurl = GetLink("customtidelink", 'http://api.aerisapi.com/tides/9446705?client_id=U7kp3Zwthe8dc19cZkFUz&client_secret=4fHoJYS6m9T7SERu7kkp7iVwE0dewJo5zVF38tfW');
    myurl = myurl + '&from=' + fromdate + '&to=+48hours';   
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (xhttp.readyState == 4 && xhttp.status == 200) HandleCustomTidesReply(xhttp.responseText);
    }
    xhttp.open("GET", myurl, true);
    xhttp.send();
}

function HandleCustomTidesReply(reply) {
    var json = JSON.parse(reply);
    if (json.success == true) {
        gPeriods = json.response.periods;
        ShowTideDataPage(gPeriods, false);
        TideClick(2);
        //GraphTideData(gPeriods[1].heightFT, gPeriods[2].heightFT, gPeriods[3].heightFT,
        //    gPeriods[1].dateTimeISO, gPeriods[2].dateTimeISO, gPeriods[3].dateTimeISO, false);
    }
    else {
        document.getElementById("tidepagecurrent").innerHTML = "Tides not available." + json.error.description;
        //alert('Could not retrieve tide information: ' + json.error.description);
    }
}

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

//<!-- WEATHER ---------------------->
//<script>
//====WEATHER=====================================================================================
// Weather
function ShowWeatherPage() {
    ShowPage("weatherpage");
    SetPageHeader("Weather");
    InitializeDates(0);
    document.getElementById("currentweatherpage").innerHTML = localStorage.getItem("currentweatherlong");
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
    var json = JSON.parse(localStorage.getItem("forecastjson")); // retrieve saved data and turn it into an object again
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

//</script>

//<!-- MAIN ---------------------------------->
//<script type="text/javascript">
//======================================================================================================
/////////////////////////////////////////////////////////////////////////////////////////////////
//  MAIN APP CODE
//
function StartApp() {
    app.initialize();
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

    InstallAvailable();  // point user to google play only if a mobile browser that is NOT PhoneGap
    UpdateAvailable(); // point user to google play only if a new version is available


    //  pushbots - set the notify switch. hide it for web.
    if (isPhoneGap()) {
        if (localStorage.getItem("notifyoff") == null) NotifyColor(1);// set that notify on/off flag
        else NotifyColor(0);
    } else document.getElementById("notifyswitch").setAttribute('style', 'display:none;');

    // ios - hide the update app at the request of the Apple App Review team 3/19/17.
    if (isPhoneGap() && !isAndroid()) document.getElementById("updateappswitch").setAttribute('style', 'display:none;');
    //  Show the cached data immediately if there is no version change. Otherwise wait for a cache reload.
    if(LSget("myver") == gMyVer) {
        ParseFerryTimes();  // moved saved data into ferry time arrays
        ParseOpenHours();
        ShowCachedData();
    } else gForceCacheReload = true;

    // show Alert and Weather immediately.
    localStorage.removeItem("alerttime"); // force immediate reload of alert info
    getAlertInfo(); // always get alert info every 10 minutes (time check is in routine)
    getCurrentWeather(); // gets weather async every 20 min.
    getForecast(); // updates forecast every 2 hrs

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
        ReloadCachedData();
    }
    
    // set refresh timners
    gMyTimer = setInterval("timerUp()", 60000);  // timeout in milliseconds. currently 60 seconds
    window.addEventListener("focus", focusEvent);
    window.addEventListener("blur", blurEvent);
    document.addEventListener("backbutton", backKeyDown, true);
    document.addEventListener("pause", onPause, false);
    document.addEventListener("resume", onResume, false);
    DisplayLoadTimes();
}

//</script>



