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
3/09/16  Google play store for version 1.0.308.2200 as ver 42. (does not show update time on bottom of main screen);

3/15/16 PROBLEMS WITH IOS ICONS. MOVED TO ROOT DIRECTORY ABOVE WWW.
3/18/16. Approved for Apple app store. 
4/14/16. Uploaded to web. version 1.3.0414.  
		This version also works with pushbots for android, but has not been deployed to the google play store.
4/16/16. Version 1.3.16. built and uploaded to google play store as ver 130. (actually build 131). Includes pushbots.
		Includes Push notifications for andriod (pushbots), adds splash screen (which I may want to remove).
		correct dates and icons for weather forecast, caching of the full weather forecast, 
		minor formatting improvements and the on pause/on resume events.
		In production on 4/16/16.  Pushbots works from getferryalerts.php.
4/22/16. Version 1.3.16 submitted to apple app store as ver 1.3. profile AIADistributionPush.
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
6/05/16. 1.5.0605.1400 versionCode 220 (but play shows it as 2208). Uploaded to Google Play. as version 1.5....
6/07/16. 1.6.0607.1601 versionCode 221 (2218). Consoldate dailycache.txt, comingevents.txt, tidedata.txt into dailycache.php.
		send usage statistics up with dailycache.php.  Uploaded to Google Play.
10/8/16. 1.07.1007. versonCode 222. Add "Fishing" row. Improve Ferry Schedule display. Improve Coming Events display.
		Improve ability to support ferry schedule changes (with new ferry schedule including code for special cases.)
		Improve handling of Alerts by always querying for alert on startup. 
		Display message when an update is available (using the ANDROIDVER and IOSVER in dailycache).
		Fix Coming Events year-rollover problem (by inserting the year into comingevents and activities when loaded,
			and doing all date checks with yymmdd).
		Fix Coming Events weekly and monthly grid problem.
10/14/16. 1.07.10142320 version Code 2221. Uploaded to Google Play.   
		Add myver to cache. Reload cache on version change. Remove splashscreen.hide(). 
10/30/16. 1.07.10142320 version Code 2221. Uploaded to Apple app store as version 1.7. 
10/30/16. Branch Ver17 merged to master.  Branch Ver18 created.

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
		dev_acc=13833158091009122644 ; AIA ver 1.3 on 4/16/16; ver 1.5 on 6/5/16.
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
	NOTE: each 'certificate' has a P12 cert AND a provisioning profile.
	IOS Development2 is the dev P12 and dev provisioning profile for org.anderson-island.*.  WONT work for PUSH.
	AndersonIslandDevPush is the dev p12 and provisioning provile for org.anderson-island.andersonislandassistant. WILL work for push with the push P12.
	Anderson Island Assistant is the distribution profile for org.anderson-island.*. WONT work for push.

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
		comingevents.txt is manually maintained by rfb. loaded 1/day by the app getdailycache.php as of 1.6.
	3. getalerts.php which copies alerts.txt (filled by getferryalerts.php cron every 5 min),
		 tanneroutage.txt (filled by gettanneralerts.php cron every 10 min),
		 burnban.txt (filled by getburnbanalerts.php cron every 15 min) to stdout.
		 run every 10 minutes by the app as of 1.6.
	4. tidedata.txt  which is filled by gettidescron.php every 6 hrs.loaded 1/day by the getdailycache.php script as of 1.6.
	5. openweathermap.com which returns json structures for current weather and forecast.
		current loaded every 15 min by the app. forecast loaded every 30 min by the app.

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
		
	PHP DEBUGGING:
	Switch to project AIAPHP.  This uses the files in the 'serverside' directory, so it is still under GIT control.
	Use F5 to debug a file using the local php and phptools. (you must set the file to the startup file).
	Only works on my laptop.