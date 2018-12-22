<?php
//////////////////////////////////////////////////////////////////////////////////
//  services signup - process service signup form
//  rfb. 12/15/18.
//
//  Entry: $_Post[name] = name, address, etc.
//  Exit: sends an email to postersw. In the future this will create a DB entry and regenerate the services table.
$emailbody= "Services Signup: {$_POST['bname']},{$_POST['category']},{$_POST['password']},{$_POST['name']},{$_POST['address']},{$_POST['phone']},{$_POST['phone2']},{$_POST['contractor']},{$_POST['email']},{$_POST['website']},{$_POST['notes']}";
$emailaddr = "postersw@comcast.net";
$headers = "From: postersw@comcast.net\r\nMime-Version: 1.0\r\nContent-type: text/html; charset=\"iso-8859-1\"";
mail($emailaddr, "Services Signup",$emailbody,$headers);
?>
<!DOCTYPE html>
<html>
<head>
    <title>SERVICES SIGNUP</title>
</head>
<body>
    <h1>Services Signup</h1>
    <div>Thank you for your submission. Your entry will appear within 48 hours.</div>
    <?php echo $emailbody; ?>
</body>
</html>