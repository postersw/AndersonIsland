Created 1/31 by Phonegap developer:
	/.cordova
	/hooks
	/platforms
	/plugins
	/www  this contains all my source
Created 2/5 by Visual Studio - which is not needed by phonegap build:
	/.vs
	/bin
	/bld
	AndersonIsland.jsproj
	AndersonIsland.jsproj.user
	AndersonIsland.sln
	taco.json
1/30/16  Everything is in GitHub as a free public repository.  //github.com/postersw/AndersonIsland.git
2/10/16  Linked to phonegap build account. app = org.anderson-island.andersonislandassistant
3/09/16  Google Play Build: version 1.0.308.2200 as ver 42. (does not show update time on bottom of main screen);
3/15/16 PROBLEMS WITH IOS ICONS. MOVED TO ROOT DIRECTORY ABOVE WWW.
3/18/16. IOS Distribution Build: Approved for Apple app store. 
4/14/16. Uploaded to web. version 1.3.0414.  
		This version also works with pushbots for android, but has not been deployed to the google play store.
4/16/16. Google Play Build: Version 1.3.16. built and uploaded to google play store as ver 130. (actually build 131). Includes pushbots.
		Includes Push notifications for andriod (pushbots), adds splash screen (which I may want to remove).
		correct dates and icons for weather forecast, caching of the full weather forecast, 
		minor formatting improvements and the on pause/on resume events.
		In production on 4/16/16.  Pushbots works from getferryalerts.php.
4/22/16. IOS Distribution build: Version 1.3.16 submitted to apple app store as ver 1.3. profile AIADistributionPush.
5/9/16. Version 1.5 to Web only. Tides graph.  Open Hours json structure with multiple date ranges.
		Color coding for Events.
5/15/16. Version 1.5.0515 for web. local jquery library. Color coding for activities. Tide graph supports other dates.
		formatting improvements. 
5/29/16	Version 1.5.0528: Tides: Tides graph, range. OpenHours: additional OpenHours information with date ranges for hours, full page
		business page with map link. Ferry: Support for 2 ferry schedules with automatic cutover. 
		Events/Activities: Color coding for events and activities. Color in blocks on weekly calendar. Add to Google calendar.
		Parks:  row and link. Weather: correct month. Tanner and Burnban: support status in getalerts. 
		Internal: Links now in dailycache. Use getalerts.php.
5/30/16. 1.5.0530. remove jquery use for ajax.
6/05/16. Google Play build: 1.5.0605.1400 versionCode 220 (but play shows it as 2208). Uploaded to Google Play. as version 1.5....
6/07/16. Google Play build: 1.6.0607.1601 versionCode 221 (2218). Consoldate dailycache.txt, comingevents.txt, tidedata.txt into dailycache.php.
		send usage statistics up with dailycache.php.  Uploaded to Google Play.
10/8/16. 1.07.1007. versonCode 222. Add "Fishing" row. Improve Ferry Schedule display. Improve Coming Events display.
		Improve ability to support ferry schedule changes (with new ferry schedule including code for special cases.)
		Improve handling of Alerts by always querying for alert on startup. 
		Display message when an update is available (using the ANDROIDVER and IOSVER in dailycache).
		Fix Coming Events year-rollover problem (by inserting the year into comingevents and activities when loaded,
			and doing all date checks with yymmdd).
		Fix Coming Events weekly and monthly grid problem.
10/14/16. Google Play build: 1.07.10142320 version Code 2221. Uploaded to Google Play.   
		Add myver to cache. Reload cache on version change. Remove splashscreen.hide(). 
10/30/16. IOS Distribution build: 1.07.10142320 version Code 2221. Uploaded to Apple app store as version 1.7. 
10/30/16. Branch Ver17 merged to master.  Branch Ver18 created.
03/07/17. Google Play build: 1.08.030717 version code 2222. Branch Ver18. Uploaded to Google Play @ 1330.
		Move link for Ferry Location to dailycache. Improve Events table display.
03/11/17. Google Play build: 1.09.030917 version 2223. Branch Ver19. Add main screen button to launch ticket app. Uploaded to Google Play.
03/14/17. Branch Ver110.  Ver 1.10 created to fix font width on Ferry webcam row.
03/14/17. IOS Distribution build: Build #306, ver 1.10.031417 2342. Using Branch ver110. Submitted to App store 3/14.
		  using new posterswprod.p12 cert (3/10/17) and the AIADistributionPush profile.   
		  (Android verion 2224 not yet in GooglePlay).
03/18/17. Ver 1.10.031417 accepted by App Store.  NOTE: NEXT TIME MUST REMOVE 'UPDATE APP' menu pick per app store feedback.
03/19/17. Ver 1.10.0314172342 Android version # 2224, released through Google Play @2300. (662 installs).
03/19/17. Branch Ver111 created. All IOS icons moves to www/res/icon/ios/. 
03/23/17. Ver 1.11.032317 to Web.  Remove '0 rain' from WEATHER line.

GIT CREATE NEW BRANCH
	1. Merge current branch (e.g. Ver18) into Master:
		1. Select 'Branches" Menu
		2. Switch Current branch to Master: Screen "Branches". Select 'master'.
		3. Select Merge.  Then set Merge from Branch "xxxx (e.g. Ver18)"  Into current Branch "Master" and commit changes after merging.  
		4. Sync.
	2. Create new branch directly on GetHub. github.com/postersw.
		Select 'master' in the Branch dropdown.   
		Select the Branch dropdown again, and fill in the name of the new branch. Then select Create.
		Then go to  Visual Studio on local machine and create a new local branch from this branch: 
			Branches screen. Right click on AndersonIsland (master) and select Create New Local Branch.
		    Enter local name (Verxxx) and from: origin/verxxx.   Check 'Checkout branch' and 'Track remote branch'. Click on 'Create Branch'.
	2a. Alternative to #2: Create new branch (e.g. Ver19) directly in Visual Studio. 
		1. Select "Branches" menu
		2. Select "AndersonIsland" repository in branch list, right click, and -> Create New Local Branch
		3. Fill in new branch name (e.g. Ver19) and then source branch (Master)
		4. After branch appears in the Branches list select the branch, right click, and select "Publish Branch".
 		   Then it will appear in the 'remotes/origin' list.
	3. New branch will be on this PC. Be sure to Publish it from the Branches screen. Be sure to use it on other PCs.
	4. Switch phonegap.build to new branch (e.g. Ver19). NOTE: branch name must match existing GitHub branch. CASE SENSITIVE.
	   If it doesn't you get a message "Cannot access Github repository".
	5. Switch other PCs to new branch. NOTE: If new branch does not show on Visual Studio GIT/Branches list, 
	   try switching to the Sync screen and running Fetch. Or set the VS branch to Master, and then run Fetch.
	   If that doesn't work, try 'git fetch' from the command line.
	   or run GitUI and click on "Remote -> Fetch from Origin" which will update things. Then restart Visual Studio.

WEB DEBUG/BUILD
	Upload to anderson-island.org using godaddy.com file interface.
	Debug using chrome, F12. All features should work except pushbots.
ANDROID DEBUG/BUILD
	Build on PhoneGap build.  Then download the apk using the bar code scanner to open the download web page.
	Alernatively run PhoneGap build on the phone and click on the apk icon to download it.
	Download.  Then find the downloaded file and click on it, which will install it.
	Note:  Debugging can be done without a signed certificate, using 'no key selected': builds xxx-debug.apk.  OR
	 with a signed certificate, using 'postersw' key (pw=dd): builds xxx-production.apk. unlike the iphone, either cert will work on my phone.
	 But only the production apk can be uploaded to the google store. 
	Note: if you install the debug version on the phone, you must uninstall it before you can install the prod version,
	 and vice versa. Otherwise the phone gives a message: "install failed"  

DEBUG CHECKOUT:
	0. Update config.xml for version and versionCode. Note for some reason I set the versionCode = 220
		and googleplay shows it as 2208.
	1. Check each screen.
	2. Check each button.
	3. Check each menu item.
	4. Reload data. check times in About.
	5. Test on web.
	6. Test on Android.
	7. Test on iphone or iPad.

ANDROID GOOGLE PLAY STORE
	1. Log into play.google.com/apps/publish -> developer console;  robertbedoll@gmail.com
		dev_acc=13833158091009122644 ; AIA ver 1.3 on 4/16/16; 1.5 on 6/5/16; 1.6 on 6/7/16. 1.7 on 10/14/16. 1.8 on 3/7/17.
		1.9 (2223) on 3/11/17. 
	2. Click on Anderson Island Assistant.  Click on "APK".   Click on "Upload new APK to Production";
	3. Set the what's new.
	4. make sure that the versioncode is higher than the previous version.



IOS DEBUG/BUILD 
	to debug: in pg build, select the IOS 'AIADevelopmentPush' certificate (pw=dd). then build.  Then download to the device.
	I think this is done by running pg build on the phone, and clicking on the ipa icon.
	This works on moms iphone or ipad. Must be a registered device in developer.apple.com.
	For deployment: click the 'AIADistributionPush' certificate.  enter key pw=dd. Then build.  
	The resulting ipa must be uploaded using the virtual mac.  This ipa can NOT be run directly on mom's phone or ipad.
	Start the virtual mac.  Go to the apple dev site.  
	See Separate file: IOS_Certificates_HowTo.txt.
	NOTE: my app id is org.anderson-island.andersonislandassistant, and Push Notifications are enabled. 
	My app has 2 Provisioning Profiles, and each profile has a certificate (which has a P12 file): 
		1. AndersonIslandDevPush, whose certificate is is called Robert Bedoll, type iOS Development (expires 3/12/18 as of 3/12/17).
		2. AIADistributionPush, whose certificate is called ?, type iOS Distribution. Expired 3/10/17

		Note that there is an APN certificate called org.anderson-island.andersonislandassistant which is type 
		  'APNs Development iOS) which is the APN cert to be used by Pushbots to send messages to the app. Is is 
		  separate fromn the Development Certificate, and is not referenced by the app. But it is specific for my app's id.
	
	ICON sizes: Note that you need lots of icons of exact size.  To resize the icons, just use Paint and the resize button.
	Add the icons to the solution (seems to happen automatically if you put them in the root) and config.xml.

	Macincloud:  Start windows utilities->remote desktop to LA051.macincloud.com, then login as user901584 pw= pwd29837

	iTunes install:
	0. Windows Accessories -> Remote Desktop Connection -> LA051.macincloud.com:6000.
	   Log in as user901584 pw= pwd29837.  Start Safari. Go to build.phonegap.com. 
	   on phonegap build, select the 'AIADistributionPush' key.  This enables push notifications. Then build.
	1. download the ipa file to your desktop.
	2. log in to developer.apple.com.  Click on iTunes Connect. log in.  robertbedoll@gmail.com  pw=2...E
	3. select MyApps, then Anderson Island Assistant, 
	4. Create a new version.  The AIA version (like 1.3, 1.4, ...) should match the version in the comfig.xml of the app.
	5. You will get a new page. fill out the what's new.
	6. Start "Application Loader".   select Deliver Your App and click on 'Choose'.  
	7. Select your xxx.ipa binary.  The process takes a while but no further interaction should be needed other than clicking on 'next' or 'ok'
	8. Wait until you get the email that says "Version x of Anderson Island Assistant has finished processing".
	9. Go back to the itunes connect my app page. under the build section of the web page, select the version you just uploaded.
	   If your new version is not there, click on another choice and then back onto your version (1.x Prepare for Submission) to refresh the 
	   page. Then your new version should show up.
	9. Click SAVE and SUBMIT FOR REVIEW.
	10. You app will go into a waiting for review state.
	11. Log out (under the apple icon).

DATA LOADS
	Data is loaded from:
	1. Daily Cache: dailycache.txt. manually maintained by rfb. loaded 1/day by the app getdailycache.php.
	2. Coming Events: (comingevents.php  which copies comingevents.txt to stdout. is ver 1.3 only).
		comingevents.txt is loaded 1/day by the app getdailycache.php (called by the app) as of 1.6.
		comingevents.txt is created daily by getgooglecalendarcron.php cron once/day.  Read google AndersonIsland calendar and extracts 2 months of events.
	3. getalerts.php which copies alerts.txt (filled by getferryalerts.php cron every 5 min),
		 tanneroutage.txt (filled by gettanneralerts.php cron every 10 min),
		 burnban.txt (filled by getburnbanalerts.php cron every 15 min) to stdout.
		 run every 10 minutes by the app as of 1.6.
	4. tidedata.txt  which is filled by gettidescron.php every 6 hrs.loaded 1/day by the getdailycache.php script as of 1.6.
	5. openweathermap.com which returns json structures for current weather and forecast.
		current loaded every 15 min by the app. forecast loaded every 30 min by the app.

	DAILYCACHE.TXT
	Parameters: 
    parseCache(data, "ferrytimess", "FERRYTS", "\n");
    parseCache(data, "ferrytimesa", "FERRYTA", "\n");
    parseCache(data, "ferrytimesk", "FERRYTK", "\n");
    parseCache(data, "emergency", "EMERGENCY", "EMERGENCYEND");
    parseCache(data, "links", "LINKS", "LINKSEND");
    parseCache(data, "openhoursjson", "OPENHOURSJSON", "OPENHOURSJSONEND");
    parseCache(data, "ferrytimess2", "FERRYTS2", "\n");
    parseCache(data, "ferrytimesa2", "FERRYTA2", "\n");
    parseCache(data, "ferrytimesk2", "FERRYTK2", "\n");
    ParseFerryTimes();
    parseCache(data, "ferrydate2", "FERRYD2", "\n"); // cutover date to ferrytimes2 as 'mm/dd/yyyy'
    parseCacheRemove(data, "ferrymessage", "FERRYMESSAGE", "FERRYMESSAGEEND");
    s = parseCacheRemove(data, "message", "MOTD", "\n");  // message
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
    // tides (added 6/6/16)
    var json = JSON.parse(parseCache(data, "", "TIDES", "TIDESEND"));

	DAILYCACHE.PHP
	1. Called 1/day per app starting with ver 1.6 on 6/6/16.
	2. Retrieves dailycache.txt, comingevents.txt, tidesdata.txt and returns them as one data string with keywords.
	3. Replaces separate loads of dailycache.txt, comingevents.php (which loaded comingevents.txt), and the call to 
		aerisweather.com to load the daily tides (which was replaed in early june by gettidescron.php which runs 
		every 6 hours to get the tides and write thgem to tidesdata.txt. So there are only 4 calls/day to aerisweather.com)
	4. Logs data to dailycachelog.txt.  Codes:
		V=version number.
		K=kind. PG=phonegap, MW = mobile web, DW=desktop web.  And=Android. IOS=IOS.
		N=number of app starts since last call to dailycache.php. (usually 24 hours).
		P=page loads:	a=about, b=business, c=coming events, d=add to cal, e=emergency num, f=ferry schedule, g=google map,
						h=help, l=links,
						m=web cam, n=News, o=openhours, p=parks, r=Tanner, s=show location, t=tides, u=Burn Ban, 
						v=activities, w=weather, x=PC web page, y=upgrade, 
						1=custom tides, 2=monthly activities, 3=weekly activities, 4=motify off


CRON PHP JOBS:
	1. gettides.php
	   RUns 4 times/day. Queries ariesweather.com for tides and writes json output to tidesdata.txt.
	2. gettanneralerts.php Runs every 10 minutes via cron. Reads tanner AI web page and writes to tanneroutage.txt
	3. getburnbanalerts.php. Runs every 15 min via cron. Reads pscleanair.com web page and writes to burnban.txt.
	4. getferryalerts.php. Runs every 5 min via cron. Reads pierce county RSS read and writes to ferryalert.txt. 
	5. getgooglecalendarcron.php runs nightly. Reads 2 months of events from google AndersonIsland public calendar and writes them to currentevents.txt.
		https://www.googleapis.com/calendar/v3/calendars/orp3n7fmurrrdumicok4cbn5ds@group.calendar.google.com/events?singleEvents=True&key=AIzaSyDJWvozAfe-Evy-3ZR4d3Jspd0Ue5T53E0
		which is the API key from https://console.developers.google.com/apis/credentials?showWizardSurvey=true&project=andersonislandassistant
		see https://developers.google.com/google-apps/calendar/v3/reference/events/list.	
	6. switchferryscriptcron2.php runs nightly. 
	OBSOLETE: 
		makecomingevents.php used to run nightly.  Replaced by getgooglecalendarchron.php. Copies comingeventsmaster.txt to comingevents.txt with 2 months of data.
		genrecurringevents.php  reads comingeventsmaster.txt and recurring.txt and writes newcomingevents.txt with
		all the recurring events expanded.  Replaced by google calendar.

		
	PHP DEBUGGING:
	Switch to project AIAPHP.  This uses the files in the 'serverside' directory, so it is still under GIT control.
	Use F5 to debug a file using the local php and phptools. (you must set the file to the startup file).
	Only works on my laptop.