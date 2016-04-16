<?php
PushANotification("this is a push message test");

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
    // Push it !
    $pb->Push();
}
?>