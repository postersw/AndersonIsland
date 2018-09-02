<?php
// ONE SIGNAL 5/24/18.  FREE SERVICE WITH NO LIMITS
$title = "TEST MESSAGE";
$msg = "This is a test of OneSignal messaging. Please ignore.";

PushOSNotification($title, $msg);
return;
/////////////////////////////////////////////////////////
//  PushOneSignalNotification. 5/25/18
//  Entry   title = message title
//          msg = the message
//  Users curl library.
//  https://documentation.onesignal.com/reference#create-notification
//
function PushOSNotification($title, $msg) {
		$fields = array(
			'app_id' => "a0619723-d045-48d3-880c-6028f8cc6006",
			'included_segments' => array('Active Users'),
            'headings' => array("en" => $title),
			'contents' => array("en" => $msg),
            'ttl' => 4*3600
		);
		$fields = json_encode($fields);
    	print("\nJSON sent:\n");
    	print($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
												   'Authorization: Basic YWQyZmE5OGUtNGY0MC00OTAyLWEyOTYtMTUyZjVjZjEyNzA0'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
        print($response);
		return $response;
	}

/////////////////////////////////////////////////////////////////////////
// PUSHBOTS
PushOSNotification("this is a push message test");

function PushANotification($note) {
    // Push The notification with parameters
    require_once('PushBots.class.php');
    $pb = new PushBots();
    // Application ID
    $appID = '570ab8464a9efaf47a8b4568';
    // Application Secret
    $appSecret = '297abd3ebd83cd643ea94cbc4536318d';
    $pb->App($appID, $appSecret);

    // Notification Settings
    $pb->Alert($note);
    $pb->Platform(1);  // android
    // Push it !
    $res = $pb->Push();
    echo($res['status']);
    echo($res['code']);
    echo($res['data']);
}
?>