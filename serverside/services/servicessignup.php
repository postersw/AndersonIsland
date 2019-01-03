<!DOCTYPE html>
<html>
<head>
    <title>SERVICES SIGNUP</title>
</head>
<body>
    <h1>Services Signup</h1>
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
    ?>

    <div>Thank you for your submission. Your entry will appear within 48 hours.</div>
</body>
</html>