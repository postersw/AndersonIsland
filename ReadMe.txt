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
04/10/17. Ver 1.11.0410171 to IOS App Store. Fixes IOS icons for iphone 5 & 6. Fixes Launch Ticket app for iphone.
		  Gets alerts every minute. 
04/13/17. Ver 1.12.0411173 to Android Google Play. Version#2225.  Removes splash screen for startup speedup.
05/14/17. Ver 1.13 branch Ver113 created.  Add 3 ferry times/line, ferry countdown, highlight ferry time row by location using location services.
		  Add android notification icon for pushbots.
06/12/17. Ver 1.13 uploaded to Google Play. Ver#2226. 
06/15/17. Ver 1.14 (on branch Ver113) Uploaded to Google Play. ver#2227. Fixed Android launch icon problem in 1.13. 
06/18/17. Ver 1.15 (for IOS).
09/09/17. Ver 1.15.08271130 Uploaded to Apple App Store.   (No changed since 6/18).
01/06/18. Ver 1.16 (branch Ver116) created.  
04/17/18. Ver 1.16.0416 uploaded to Google Play ver#2229. MinAPI set to 15 FOR Pushbots (was 14). Minor enhancements to the 'tide' display and the 'options' menu.  Correct calculation of date for 'thanksgiving'.
04/25/18. Ver 1.17 Branch Ver117 created.  Only change is config.xml cli set to cli-7.1.0

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
			Select Branches menu in VS. Select Master.  
			Go to Sync screen.  sync.
			Go back to Branches screen.  Under remotes/origin, the new branch (Vernnn) should be visible.
			Double click remotes/origin->the new branch.  
			This will create a new local branch of the same name and check it out and set it to track the remote.
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

------------------------------------------------------------------------------------------------------------------------
BUILD/DEBUG

WEB DEBUG/BUILD
	Upload to anderson-island.org using godaddy.com file interface.
	Debug using chrome, F12. All features should work except pushbots.

WEB LOCAL DEBUG
	Move files to C:\inetpub\wwwroot
	Open http://localhost
	Use Developer tools to debug.

ANDROID DEBUG/BUILD
	Build on PhoneGap build.  Then download the apk using the bar code scanner to open the download web page.
	Alernatively run PhoneGap build on the phone and click on the apk icon to download it.
	Download.  Then find the downloaded file and click on it, which will install it.
	Test on: 1 My phone, 2 Sue's phone, 3 my old motorola E phone
	Note:  Debugging can be done without a signed certificate, using 'no key selected': builds xxx-debug.apk.  OR
	 with a signed certificate, using 'postersw' key (pw=dd): builds xxx-production.apk. unlike the iphone, either cert will work on my phone.
	 But only the production apk can be uploaded to the google store. 
	Note: if you install the debug version on the phone, you must uninstall it before you can install the prod version,
	Debug for different phone screen sizes:  Put on web, open Chrome.  
		Open www.anderson-island.org.  Select 'Developer tools' menu.
	   Select 'Device Mode' (2nd icon from right).   Select the different phone screen sizes and checkout the results.

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

------------------------------------------------------------------------------------------------------------
IOS DEBUG/BUILD 
	DEBUG
	 in pg build, select the IOS 'AIADevPush040317' certificate (pw=dd). then build.  Then download to the device.
	This is done by going to build.phonegap.com on the phone in safari, and clicking on the ipa icon.
	This works on the registered devices in developer.apple.com. Currently Sue's iPad and Ester's iPhone 5.

	To register a new device: 1. log in to developer.apple.com -> certificates -> Devices -> iPhone -> + -> enter the device UDID. 
	2. You will need the UDID.  To get the UDID:   on the iphone safari browser, enter get.udid.io.  Accept the certificate. 
	  then it gives you the UDID.  
	3. copy the UDID into the udid  you got above when you hit the + .  Then click on Register.
	4. Then go to Provisioning Proviles -> Development -> Edit -> Add devices.  Select the new device.  
	  you will get a NEW provisioning profile.  Download it to my PC.  
	5. Then upload this NEW profile with the existing dev p12 key to phonegap build (you can use the existing p12 key with an updated profile): 
		go to build.phonegap.com, select the Anderson Island Assistant,  Edit Account -> Signing Keys -> IOS add a key
		select the dev p12 key, and the new Provisioning Profile from the provisioning profile just downloaded. Upload them.

	DEPLOYMENT
	 click the 'AIADistributionPush' certificate.  enter key pw=dd. Then build.  
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
	Add the icons to the solution in www/res/icon/ios and config.xml.  After adding icons, do a build, download the ipa file,
	  unzip it (change type from ipa to zip), and look at the icons in the file. They should ALL be good.

	Debug for different iphone screen sizes:  Put on web, go to MacinCloud, open Safari.  
		Open www.anderson-island.org.  Select 'Developer' menu.
	   Select 'Enter Responsize design mode'.  Select the different iphone screen sizes and checkout the results.

	Macincloud:  Start windows utilities->remote desktop to LA051.macincloud.com, then login as user901584 pw= pwd29837

	iTunes install:
	0. Windows Accessories -> Remote Desktop Connection -> LA051.macincloud.com:6000.
	   Log in as user901584 pw= pwd29837.  Start Safari. Go to build.phonegap.com. 
	   on phonegap build, select the 'AIADistributionPush' key.  This enables push notifications. Then build.
	1. download the ipa file to your desktop.
	2. log in to developer.apple.com.  Click on iTunes Connect. log in.  robertbedoll@gmail.com  pw=DD1
	3. select MyApps, then Anderson Island Assistant, 
	4. Create a new version.  The AIA version (like 1.3, 1.4, ...) should match the version in the comfig.xml of the app.
	5. You will get a new page. fill out the what's new.
	6. Start "Application Loader 3.6".   select Deliver Your App and click on 'Choose'.  
	7. Select your xxx.ipa binary.  The process takes a while but no further interaction should be needed other than clicking on 'next' or 'ok'
	8. Wait until you get the email that says "Version x of Anderson Island Assistant has finished processing".
	9. Go back to the itunes connect my app page. under the Build section of the web page, select the version you just uploaded.
	   If your new version is not there, click on another choice and then back onto your version (1.x Prepare for Submission) to refresh the 
	   page. Then your new version should show up.
	9. Click SAVE and SUBMIT FOR REVIEW.
	10. You app will go into a waiting for review state.
	11. Log out (under the apple icon).

-----------------------------------------------------------------------------------------------------------------------

IOS CERTIFICATES
HISTORY
These files are on the desktop of the virtual mac. They are duplicated in OneDrive/Documents/PhoneGap/Keys, but actually were created on my virtual mac and the developer.apple.com web site.
3/12/17 Development Certificate (because original one expired)
	AndersonIslandDevPush.mobileprovision (profile)  Uploaded to build.phonegap.com
	posterswdev.p12  (cert in P12 format)		 Uploaded to build.phonegap.com
	ios_development.cer (actual certificate) this cert is the one referenced by AndersonIslandDevPush.mobileprovision profile.
	posterswdev.certSigningRequest 
3/13/17 Production Distribution Certificate (because original one expired)
	AIADistributionPush.mobileprovision (profile) uploaded to build.phonegap
	posterswprod.p12  				uploaded to build.phonegap
	ios_distribution.cer	this cert is the one referenced by AIADistributionPush profile.
	posterswprod.certSigningRequest
4/3/14  New Provisioning Profile for Esther's iPhone 5. Created new phonegap build signing key: "AIADevPush040317" consisting
	of posterswdev.p12 cert and AndersonIslandDevPush.mobileprovision on my PC only which enables Esther's iPhone 5 for testing.

HOW TO Create new Development/Production Certificate:
	The overall process is: 
		1. Generate a Certificate Signing Request (CSR) xxxx.certSigningRequest. using Keychain.
		2. Use that to generate the Certificate on the developer.apple.com web site.
		3. Import that certificate back into Keychain on the Mac. Then export the Public key to a P12 file.
		4. Create a Provisioning Profile using the Certificate from #2 on the developer.apple.com web site. 
		5. Download the MobileProfile file from #4.
		6. Upload the MobileProfile and the P12 file to build.phonegap.com.
	DETAILS:
	Open Mac.
		Open Safari. 
	From Safari:
		Log in to developer.apple.com. 
		Go to Certificate.  
		Click on '+' to create a new certificate.  Select 'iOS App Development'. Click on Continue.
		PRODUCTION: click on 'App Store and Ad Hoc'.
 	Back to the Mac. 
		To manually generate a Certificate, you need a Certificate Signing Request (CSR) file from your Mac. 
		To create a CSR file, follow the instructions below to create one using Keychain Access.
		In the Applications folder on your Mac, open the Utilities folder and launch Keychain Access.
	     KEYCHAIN ACCESS:
		Within the Keychain Access drop down menu, select 
			Keychain Access > Certificate Assistant > Request a Certificate from a Certificate Authority.
		In the Certificate Information window, enter the following information:
			In the User Email Address field, enter your email address. 'support@postersw.com'
			In the Common Name field, create a name for your private key (posterswdev/prodkey.)p
			The CA Email Address field should be left empty.
			In the "Request is" group, select the "Saved to disk" option.
			Click Continue within Keychain Access to complete the CSR generating process.
			I used the file names posterswdev and posterswprod.certSigningRequest
	Back to Safari.
		developer.apple.com:
		Upload the CSR file (from above): xxxx.certSigningRequest.
		Now your certificate is done.
		Download your certificate to your Mac desktop. It will be called ios_development.cer or ios_distribution.cer.
	Back to the Mac.
		Then double click the .cer file to install in Keychain Access.
		It now shows up in Keychain Access- Certificates as
			 'iPhone Developer Robert Bedoll' or iPhone Distribution: Robert Bedoll.
	     KEYCHAIN ACCESS:
		select Keys, and the key just added (posterswdev).  Select the PRIVATE key.
		right click on Export.  Select the P12 File Format.
		Set the file name to posterswdev or posterswprod.
		click Save.  It will prompt for the new password. Create the pw='dd', enter it and click OK.
		You'll be aked for the Login PW. Use the MacinCloud pw, 'pwd29837'.
		You will now have a posterswdev.p12 or posterswprod.p12 file.
		NOTE: posterswprod is really the iOS Distribution key.
	Backk to Safari.
		developer.apple.com:
		Now select correct Provisioning Profile: AIADistributionPush or AndersonIslandDevPush. 
		click Edit.
		Add the new iOS Development or iOS Distribution (code signing) certificate.
		Click on 'Generate.'
		Download it to the desktop. It will be called AndersonIslandDevPush.mobileprovision.
		NOTE: THE APP PROVISIONING PROFILE ONLY HAS THE iOS App Development Code Signing certificate.  
			IT DOESNT HAVE THE PUSH CERTIFICATE. 
			The APN SSL Certificate (the push certificate) is ONLY FOR THE APN SERVER and validates the APN Server
		  	(the Pushbots server) to the Apple APN Service. It is independent and separate from the code signing cert.
			It has no pw because Pushbots does not accept a pw. 

		BUILD.PHONEGAP.COM
		Log in to build.phonegap.com.  support@postersw.com 
		Select Accounts -> Edit Account -> Signing keys.
		Click on Add key, create a new key with the .P12 file just created and the .mobileprovision file just create.
		Set the password to the password 'dd' you created above.
------------------------------------------------------------------------------------------------------------------------
APN (Apple Push Notification) CERTIFICATE YEARLY RENEWAL. Expires every March.

1. Bring up Virtual Mac.Macincloud: Windows Accessories->remote desktop to LA051.macincloud.com, then login as user901584 pw= pwd29837
2. Bring up Safari and log into the 'developer.apple.com' -> account (robertbedoll@gmail.com, DD1) -> Certificates,Identifiers...
3. Click 'App IDs', then select the expiring id which is org.andersonisland.andersonislandassistant.
4. Click Edit and Scroll to 'Push Notifications' section and click on 'Create Certificate...'.
5. https://www.pushbots.help/install-pushbots-in-your-app-or-website/ios/the-apple-part-certificate-and-provisioning-profile
Follow instructions to generate P12 file.
6. Open Applications -> Utilities -> Keychain Access.
7. Within KeychainAccess dropdown menu which is at the very top of the main screen, 
		select Keychain Access->Certificate Assistant->Request a Certificate from a Certificate Authorit
8. In the Certificate Info window, enter: my email address(robertbedoll@gmail.com), a name for the private key (AIAAPN20xx to indicate the year).
   Leave the CA email address empty. 
   In the "Request Is"  group, select the Saved to Disk option.
   Click Continue within Keychain Access to complete the CSR generating process.
9. Save as AIAAPN20xx.certSigningRequest to the desktop.
10. Choose the AIAAPN20xx.certSigningRequest file just generated. Click Continue.
11.Download the file just generated by clicking the Download button.
12.Bring up finder and go to Downloads.  You will see the file as aps_development-x.cer.
13.Double click on the cer file just created.  This installs it in Keychain under 'My Certificates'.
14.NOW YOU NEED TO CREATE THE P12 FILE. 	
15.In Keychain Access: Select My Certificates -> certificate just created. Look for the expiration date of 1 year from now. Expand it and check the private key to make sure it is the correct one you just created.
16.Right click, then export 
17.Save as AIAAPN20xx, file format .p12.
	When prompted for 'Enter a password which will be used to protect the exported items, DO NOT ENTER A PASSWORD (NO PASSWORD PROTECT)
	You will be prompted for the login  password pwd29837
	The file will be generated as AIAAPN20xx.p12.
19.Open safari to www.pushbots.com. Log in.
20.Select Anderson Island Assistant -> Settings -> Push.
21.Upload P12 file to Pushbots.
TESTING:
1.Power on Esther's iPhone5. (it only needs wifi connectivity)
2.From Pushbots, select PUSH.
3.Select one,alias=Bob, IOS
4.Enter test message and click Send (they change this process every year)
5.You should get the message on Ester's phone immediately.

I have never bothered to Revoke old certificate, and it seems to work fine.

---------------------------------------------------------------------------------------------------------------------
APN Apple Push Notification CERTIFICATES from Bryan Musial 1/2015.

PUSHBOT-Server --->APN Cert---> Apple APN Service --->>> mobile devices

The ApplePushNotifications certificate that you created is used by what the documentation refers to as the "APNs Provider" --
In the simplest case, this is your own server that is responsible for keeping track of APNs device tokens and generating APNs Push Payloads
that instruct the APNs what message, sound, or badge to deliver to a specific device token. 
Just as the arrangement of executable code and other assets in your app is secured by a cryptographic signature 
(via your iPhone Development or iPhone Distribution certificate and associated provisioning profile), communications between your server and
the Apple APNs gateway must also be secured to prevent a rogue 3rd party from masquerading as your server and sending spammy push messages
to your users. This APNs SSL certificate is used to secure and authenticate your server's connection to the APNs, authorizing it to deliver
push payloads to your app on user's devices -- Keep those certificates secure! If anyone gains access to the private key of the SSL 
certificate then they could send spammy pushes to your app!

Your APNs Provider (PUSHBOTS) will need access to the private key for this SSL certificate. Without it, Apple's APNs gateways will reject any
and all attempts to connect. Your provider, does not need to have your provisioning profiles -- this APNs certificate is entirely separate 
than the mechanisms used to code sign an iOS app, that is, the server only needs the server certificate, while the app needs the code signing
certificate + provisioning profile. These two items do not intersect and do not exchange data with each other.

It is true that your provisioning profiles (Development, Ad-Hoc Distribution, and App Store Distribution) will need to be reissued, but that
is specifically to add the aps-environment entitlement to each of these profiles allowing apps signed with these profiles to connect with 
the APNs environments. To be absolutely clear, reissuing these profiles does not and should not add your APNs SSL certificate anywhere in 
the profile...your application code doesn't need to leverage this certificate in any way and would lead to a slight increase in your 
application's size.

You can check if your current provisioning profiles include the aps-environment entitlement by opening Terminal, copy and pasting the 
following, taking care to update the path to your specific .mobileprovision:
/usr/libexec/PlistBuddy -c 'Print :Entitlements' /dev/stdin <<< $(security cms -D -i /path/to/your/application.mobileprovision)

This command does two things:

Uses the security tool in OS X to extract the plist content from the .mobileprovision file identified after the -i argument and passes all of this content into...
PlistBuddy printing the entire contents of t
he Entitlements key to screen.
The output for a basic Development profile that has not been enabled for Push Notifications will resemble the following:

Dict {
  get-task-allow = true
  com.apple.developer.team-identifier = ABC1DEF2G3
  application-identifier = XYZW1ABC2D.com.mycompany.niftyapp
  keychain-access-groups = Array {
      XYZW1ABC2D.*
  }
}
While the output for a basic Ad-Hoc or App Store Distribution that has not been enabled for Push Notifications will resemble:

Dict {
  get-task-allow = false
  com.apple.developer.team-identifier = ABC1DEF2G3
  application-identifier = XYZW1ABC2D.com.mycompany.niftyapp
  keychain-access-groups = Array {
      XYZW1ABC2D.*
  }
}
Now that you have the APNs certificates issued for your app's AppId, you do need to step through and reissue your Development, Ad-Hoc, and Distribution provisioning profiles to add the aps-environment entitlement to each of your profiles.

Navigate to Certificates, Identifiers, and Profiles tool and find one of the profiles associated with this application.
Click the Edit button and walk through each step of the wizard -- you don't have to make any changes to the previously defined settings, you simply need the current profile reissued!
Click the Download button at the end of the wizard.
Drag and drop the updated profile on the Xcode icon on your Dock to install.
If you run that same set of Terminal commands again on these new files (remember to update the path to the new .mobileprovision if necessary!) you'll now see that aps-environment key appear in your App's entitlements:

Dict {
  get-task-allow = true
  aps-environment = development
  com.apple.developer.team-identifier = ABC1DEF2G3
  application-identifier = XYZW1ABC2D.com.mycompany.niftyapp
  keychain-access-groups = Array {
      XYZW1ABC2D.*
  }
}
There are two values for this new key:

aps-environment = development -- This will appear only on Development Provisioning Profiles and allows apps signed using iPhone Developer certificates and may only connect with the Sandbox APNs Environment
aps-environment = production -- This will appear only on Distribution Provisioning Profiles (Ad-Hoc or App Store), allowing apps signed using iPhone Distribution certificates to connect with the Production APNs Environment
Depending on which certificate you use to sign a build determines which APNs gateway your app will connect to and fetch a Push token as well as which gateway your app will receive push messages from. One of the most common errors developers make with respect to push notifications is mismatching how the app is signed with how their server is connecting to Apple's APNs gateways:

Apps signed with Development certificates can only successfully negotiate APNs and receive Push Messages when the Provider is also connecting with the Development APNs SSL certificate to the Sandbox APNs gateway and using a sandbox device token in the payloads it generates.
Apps signed with Distribution certificates can only successfully negotiate APNs and receive Push Messages when the Provider is also connecting with the Production APNs SSL certificate to the Production APNs gateway and using a production device token in the payloads it generates.
Give it a shot and let us know how things go!

-------------------------------------------------------------------------------------------------------------------------

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


---------------------------------------------------------------------------------------
ICONS ETC 6/3/17.
Android notification icons for use by Pushbots:
To generate the android small icon:
https://romannurik.github.io/AndroidAssetStudio/icons-notification.html#source.type=clipart&source.clipart=directions_boat&source.space.trim=1&source.space.pad=0&name=ic_stat_pushbots_sicon
Note that the small icon must have only 2 colors: white, and transparant. It must be called ic_stat_pushbots_sicon.png.
Then copy them into www/locales/android/drawable-xxxx.  Phonegap Build  will then copy them into res/drawable-xxxx.
For the large icon, it can be 256x256, colored. It must be called ic_pushbots_licon.png.

PGB has added beta support for Android localization files which also will put files into the Android res/ directory. 
To add, create directory locales/android/ in the root of your PGB application zip / repo, and place your localization files there. 
The contents will be copied into the Android res/ directory, and any nested sub-directory structures will persist. 
Here's an example of how these files will be compiled into your APK:
	<www.zip>/locales/android/values-fr/strings.xml --> <android_apk>/res/values-fr/strings.xml
	<www.zip>/locales/android/xml/custom.xml        --> <android_apk>/res/xml/custom.xml
Existing directories will be merged, but at this time any individual files you include will overwrite their target if it exist

-----------------------------------------------------------------------------------------------------
iPhone 10.
Display changes:
https://blog.phonegap.com/displaying-a-phonegap-app-correctly-on-the-iphone-x-c4a85664c493
