<!DOCTYPE html>
    <html lang="en" xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, height=device-height, target-densitydpi=medium-dpi" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <title>Anderson Island Service Listing Signup</title>
    <link rel="stylesheet" href="lib/w3.css">
    <style>
        tr {border-style: solid; border-width: thin;border-color: gray; padding:6px; }
        .cat {background-color: lightgray}
        p {font-size:small}
    </style>
    </head>
    <body>
        <div class="w3-container" style="background-color:antiquewhite">
        <h1>Services Signup</h1>
    <p >
        <?php
//////////////////////////////////////////////////////////////////////////////////
//  services signup - process service signup form
//  rfb. 12/15/18.
//
//  Entry: $_Post[name] = name, address, etc.
//  Exit: Record added to database with ok=0
//        Sends an email to postersw and to the user
//        Entry logged in servicessignuplog.log
// emailbody= "Anderson Island Assistant Services Signup:<br/> {$_POST['bname']},{$_POST['category']},{$_POST['services']},{$_POST['password']},{$_POST['owner']},{$_POST['address']},{$_POST['phone']},{$_POST['phone2']},{$_POST['contractor']},{$_POST['email']},{$_POST['website']},{$_POST['notes']}";
$emailaddr = "postersw@comcast.net,robertbedoll@gmail.com";
$headers = "From: support@anderson-island.org\r\nMime-Version: 1.0\r\nContent-type: text/html; charset=\"iso-8859-1\"";

// add to db
include "dbadd.php";
if($insertid > 0) {
    $thankyou = "Thank you for your submission. Your entry will appear after approval, generally within 2 days.<br/>" .
    "<a href='https://www.anderson-island.org/services/servicedetail.php?id=$insertid'>To preview or edit your listing, click here.</a><br/>" .
    "Once your listing is approved, you can edit it by clicking or tapping on it, and then clicking on the EDIT button. <br/>" .
    "For support, email <a href='mailto:support@anderson-island.org'>support@anderson-island.org</a>.<br/><br/>" .
    "Thanks,<br/>Bob Bedoll<br/>Anderson Island Assistant. Poster Software, LLC.<br/>";
    echo $thankyou;
} else {
    // if dbadd failed
    echo "Your request had an error and was not added. <br/>";
    $email = ""; // suppress email to user
    $thankyou = "DBADD FAILED!<br/>";
}
$emailbody = $displayinfo . "<br/>id=$insertid<br/>" . $thankyou;

// add to log file
$fhl = fopen("../private/servicesignuplog.log", 'a');
fwrite($fhl, date("Y/m/d H:i:s") . "|" . $emailbody . "\n");
fclose($fhl);

// mail it

$r = mail("$emailaddr,$email", "Anderson Island Assistant Services Signup",$emailbody,$headers);
if($r == false) {
    echo "Your service signup failed. Contact support@anderson-island.org";
    exit(0);
}

        ?>
</body>
</html>