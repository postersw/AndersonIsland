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
//  Exit: sends an email to postersw. In the future this will create a DB entry and regenerate the services table.
    $emailbody= "Services Signup: {$_POST['bname']},{$_POST['category']},{$_POST['services']},{$_POST['password']},{$_POST['owner']},{$_POST['address']},{$_POST['phone']},{$_POST['phone2']},{$_POST['contractor']},{$_POST['email']},{$_POST['website']},{$_POST['notes']}";
$emailaddr = "postersw@comcast.net,robertbedoll@gmail.com";
$headers = "From: support@anderson-island.org\r\nMime-Version: 1.0\r\nContent-type: text/html; charset=\"iso-8859-1\"";

// add to log file
$fhl = fopen("../private/servicesignuplog.log", 'a');
fwrite($fhl, date("Y/m/d H:i:s") . "|" . $emailbody . "\n");
fclose($fhl);

// mail it
$r = mail($emailaddr, "Services Signup",$emailbody,$headers);
if($r == false) {
    echo "Your service signup failed. Contact support@anderson-island.org";
    exit(0);
}

// add to db
include "dbadd.php";
if($insertid > 0) {
    echo "Thank you for your submission. Your entry will appear after approval, generally within 48 hours. <br/>";
    echo "Once your listing is approved, you can edit it by clicking or tapping on it, and then clicking on the EDIT button. <br/>";
    echo "<a href='http://www.anderson-island.org/servicedetail.php?id=$insertid'>To preview your listing, click here.</a> <br />";
} else {
    echo "Your request had an error and was not added. <br/>";
}
?>
</body>
</html>