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
		In production on 4/16/16.  Pushbots works from getferryalerts.php.

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
IOS DEBUG/BUILD 
	to debug: in pg build, select the IOS Developer2 certificate (pw=dd). then build.  Then download to the device.
	I think this is done by running pg build on the phone, and clicking on the ipa icon.
	This works on moms iphone or ipad. Must be a registered device in developer.apple.com.
	For deployment: click the Anderson Island certificate.  enter key pw=dd. Then build.  
	The resulting ipa must be uploaded using the virtual mac.  This ipa can NOT be run directly on mom's phone or ipad.
	Start the virtual mac.  Go to the apple dev site.  