// JavaScript source code specifically for Anderson Island Assistant
// global date variables
//  RFB 2/2/2016

var table; // the schedule table as a DOM object
var d; // date object
var dayofweek;  // day of week in 0-6
var letterofweek; // letter for day of week
var timehhmm;  // hhmm in 24 hour format
var timehh; // time in hours
var timemm; // time in seconds
var year; // year 
var month;  // month 1-12. note starts with 1
var dayofmonth; // day of month 1-31
var monthday; // mmdd
var laborday; // first monday in sept.  we need to compute this dyanmically
var memorialday;  // last monday in may
var thanksgiving;
var holiday;  // true if  holiday
var ferrytimeS, ferrytimeA;
ferrytimeS = [545, "H123456A", 645, "*", 800, "*", 900, "*", 1000, "F", 1200, "*", 1410, "*", 1510, "*", 1610, "*", 1710, "*", 1830, "*", 1930, "*", 2040, "4560H", 2200, "X6H", 2300, "Y"];
ferrytimeA = [615, "H123456A", 730, "*", 830, "*", 930, "*", 1030, "F", 1230, "*", 1440, "*", 1540, "*", 1640, "*", 1740, "*", 1900, "*", 2000, "*", 2110, "4560H", 2230, "X6H", 2330, "Y"];
var dayofweekname, dayofweekshort, scheduledate;
dayofweekname = ["SUNDAY", "MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY"];
dayofweekshort = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
scheduledate = ["5/1/2014"];

// openHours format is array of strings, 1 string per business 
// each string is: name(phone),Suntime,Montime,Tuetime,Wedtime,Thurtime,Fritime,Sattime,closedholidays
//   where xxxtime = hhmm-hhmm in 24 hour format. closedholidays = mmdd/mmdd/mmdd...
var openHours;
openHours = ["Store (884-4001),1000-1800,0700-2000,0700-2000,0700-2000,0700-2000,0700-2100,0800-2100,<a href='http://www.andersonislandgeneralstore'>\n" +
            "Restaurant</a> (884-3344),0930-1900,,,1600-2000,1600-2100,1600-2100,0930-2100,<a href='http://rivieracommunityclub.com/amenities/restaurant'>\n" +
            "Dump</a> (884-4072),1000-1400,1300-1700,,,,,<a href='https://www.co.pierce.wa.us/index.aspx?NID=1541'>"];
var openHoursLastUpdate; // time of last update

// tides
var nextTides; // string of next tides for the main page
var tidesLastUpdate; // time of last update

//////////////////////////////////////////////////////////////////////////////////////
// Pad with leading zero
function Leading0(num) {
    if (num >= 10) return num;
    else return "0" + Number(num);
}
///////////////////////////////////////////////////////////////////////////////////////
// return true if a holiday for the ferry schedule. input = month*100+day
function IsHoliday(md) {
    if (md == 1231 || md == 1232 || md == 0101) return true;
    if (md == memorialday || md == 0703 || md == 0704 || md == laborday || md == thanksgiving || md == 1224 || md == 1225) return true;
    return false;
}
/////////////////////////////////////////////////////////////////////////////////////////
// initilize all date variables.  dateincr = 0 for today, 1 to go to last date + 1 (i.e. increment date by 1).
// sets the date globals above
function InitializeDates(dateincr) {
    if (dateincr == 0) d = new Date();
    else {
        // on entry month=1-12, day=1-31, year = 20xx
        dayofmonth = dayofmonth + 1;
        if ((dayofmonth == 32) || (dayofmonth > 28 && month == 2) || (dayofmonth == 31 && (month == 9 || month == 4 || month == 6 || month == 11))) {
            dayofmonth = 1; month++;
            if (month == 13) { month = 1; year++;}
        }  // month overflow
        d = new Date(year, month - 1, dayofmonth);
        //alert(" year=" + year + " day=" + day + " dow=" + d.getDay());
    }
    dayofweek = d.getDay();  // day of week in 0-6
    letterofweek = "0123456".charAt(dayofweek); // letter for day of week
    timehh = d.getHours();
    timemm = d.getMinutes();
    timehhmm = timehh * 100 + timemm;  // hhmm in 24 hour format
    month = d.getMonth() + 1;  // month 1-12. IMPORTANT: note starts with 1
    dayofmonth = d.getDate(); // day of month 1-31
    monthday = month * 100 + dayofmonth;
    year = d.getFullYear();
    // build holidays once only
    if (dateincr == 0) {
        // laborday // first monday in sept.  we need to compute this dyanmically
        var dlabordate, dlabor;
        dlabordate = new Date(year, 8, 1); // earlies possible date
        dlabor = dlabordate.getDay();
        if (dlabor > 1) laborday = 909 - dlabor;  // monday = 1... Sat=6
        else if (dlabor = 0) laborday = 902;  // monday = 1... Sat=6
        else laborday = 901;
        // memorial day last monday in may
        var dmemdate, memdate;
        dmemdate = new Date(year, 4, 25); // earliest possible date memorial day
        memday = dmemdate.getDay();
        if (memday > 1) memorialday = 525 + 8 - memday;  // monday = 1... Sat=6
        else if (memday == 0) memorialday = 526;  // monday = 1... Sat=6
        else memorialday = 525;
        // thanksgiving
        var dthanksdate, dthanks;
        dthanksdate = new Date(year, 10, 24);// earliest possible 4th thursday (4) in november
        dthanks = dthanksdate.getDay();
        if (dthanks < 5) thanksgiving = 1124 + 4 - dthanks;
        else if (dthanks == 5) thanksgiving = 1130;
        else thanksgiving = 1129;
    }
    // compute holidays
    holiday = IsHoliday(monthday);

}

///////////////////////////////////////////////////////////////////////////////////////
// GetDayofWeek - returns 0-6 for an arbitrary date in mmdd format. this year assumed.
function GetDayofWeek(mmdd) {
    var d = new Date(year, Number(mmdd.substring(0, 2)) -1, Number(mmdd.substring(2, 4) ));
    return d.getDay();
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
// return true if a valid ferry time, else false.
// the crazy special rules go here.
// flag: *=always, H=holiday, 0-6=days of week, AFXY=special rules 

function ValidFerryRun(flag) {
    if (flag == "*") return true; // good every day
    if (holiday && (flag.indexOf("H") > -1)) {
        if (flag == "A") { //	July 3, Christmas Eve, New Year's Eve Only if Monday-Friday
            if (dayofweek != 0 && dayofweek != 6 && ((mmdd == 1231) || (mmdd = 1224) || (mmdd = 0703))) return true;
        } else return true;  // holiday
    }
    if (flag.indexOf(letterofweek) > -1) return true;  // if day of week is encoded
    // special cases F, skip 1st and 3rd wednesday of every month
    if (flag == "F") {
        if (dayofweek != 3) return true;  // if not wednesday, accept it
        week = Math.floor((dayofmonth - 1) / 7);  // week: 0,1,2,3
        if (week != 0 && week != 2) return true; // if not 1st or 3rd wednesday, accept it
    }
    if (flag.indexOf("X") > -1) {  // Friday Only labor day-12/31, 0101-6/30,
        if ((dayofweek == 5) && ((monthday >= laborday) || (monthday <= 0630))) return true;
    }
    if (flag.indexOf("Y") > -1) {  // Fridays only 7/1=labor day
        if ((dayofweek == 5) && (monthday >= 0701) && (monthday <= laborday)) return true;
    }
    return false; // not a valid run;
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
    else return Leading0(Math.floor(ft / 100)-12) + ":" + Leading0(ft % 100) + ampm;
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

/////////////////////////////////////////////////////////////////////////////////////////
// clear table. Deletes all rows but the first.
function clearTable(table) {
    // clear table
    while (table.rows.length > 1) {
        table.deleteRow(-1);
    }
}
