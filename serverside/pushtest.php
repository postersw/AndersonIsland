<?php
// ONE SIGNAL 5/24/18.  FREE SERVICE WITH NO LIMITS
// Tested and working 11/6/23. RFB.
$title = "TEST MESSAGE";
$msg = "This is a test of OneSignal messaging. Please ignore.";

PushOSNotification($title, $msg);
return;

////////////////////////////////////////////////////////
//  PushOneSignalNotification. 5/25/18
//  Entry   title = message title
//          msg = the message
//  Users curl library.
//  https://documentation.onesignal.com/reference#create-notification
//  7/9/18: ios_badgeCount set to 0
//  4/7/19: Rest API Key moved from code to file in root after key was hacked.
//
function PushOSNotification($title, $msg) {
    require ('../private/OneSignal.php');
    $fields = array(
        'app_id' => "a0619723-d045-48d3-880c-6028f8cc6006",
        'included_segments' => array('Test Users'),
        'headings' => array("en" => $title),
        'contents' => array("en" => $msg),
        'ttl' => 4*3600,
        'ios_badgeType' => 'SetTo',
        'ios_badgeCount' => 0
    );
    $fields = json_encode($fields);
    print("\nJSON sent:\n");
    print($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    // "Authorization: Basic YOUR_REST_API_KEY (from the OneSignal web site for my app). After 4/7/19 this key must be set
    //  before the code is uploaded to the web site.
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', $RestAPIKey));
    //                                           'Authorization: Basic YWQyZmE5OGUtNGY0MC00OTAyLWEyOTYtMTUyZjVjZjEyNzA0'));
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