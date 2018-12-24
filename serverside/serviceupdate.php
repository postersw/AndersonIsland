<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, height=device-height, target-densitydpi=medium-dpi" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <title>Signup for Local Anderson Island Services</title>
    <link rel="stylesheet" href="lib/w3.css" />
    <style>
        tr {
            border-style: solid;
            border-width: thin;
            border-color: gray;
            padding: 6px;
        }

        a {
            text-decoration: none
        }
    </style>
</head>
<body>
    <div class="w3-container" style="background-color:antiquewhite">
        <h1 class="w3-brown w3-text-white">SERVICE LISTING SIGNUP</h1>
        <p>
            To update your business listing, simply make changes on this form and click the UPDATE button.
    Your changes will appear immediately.
        </p>
        <?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////
//  Present updatable record to user.
//  Entry   Post:  "business" = business name primary key
//          "password" = password, not encoded
//  Exit    calls dbupdate.php to update the database

// read the record.  "business" = business name primary key

include "dbconnect.php"; // connect to the database.  returns $myconn.
// unpack request
$business = trim($_POST['business']);
$password = trim($_POST['password']);
$sql = "Select * from business where business=?";


// Check the password

// Generate the update html.
include "dbconnect.php"; // connect to the database.  returns $myconn.
    // unpack request
    $business = $myconn->real_escape_string((trim($_POST['business'])));
    $password = trim($_POST['password']);

    // read the record for the business
    $sql = "Select * from business where business='" . $business . "'";
    $result = $myconn->query($sql);
    if($result->num_rows = 0) {
        echo "<br/><br/>ERROR: The business '" . $business . "' does not exist.<br/>";
        exit();
    }

    // Check the password
    $row = $result->fetch_assoc(); // get first row
    if($password != $row['password']) {
        echo "<br/><br/>ERROR: Invalid password. If you forgot your password, send email to support@anderson-island.org.";
        exit();
    }

    // generate the html for the edit form;
    $s=<<<HEREDOC
<form class="w3-container" name="business" method="post" action="dbupdate.php">
    <label class="w3-label">Business Name: (Required. This name must be unique.) </label><input class="w3-input w3-border" type="text" name="bname"  required="required" maxlength="40" value="{$row['business']}"/> <br/>
    <label class="w3-label">Password: (Required. You will need this password to update your listing.) </label><input class="w3-input w3-border" type="text" name="password"  size="20" required="required"  maxlength="20" value="{$row['password']}"/> <br/>
    <label class="w3-label">Business Category: (Required. One Category. Example: Landscaping)</label><input class="w3-input w3-border" type="text" name="category" required="required" maxlength="40" value="{$row['category']}" /><br/>
    <label class="w3-label">Owner: (Required)</label><input class="w3-input w3-border" type="text" name="owner"  required="required" size="50" maxlength="40" value="{$row['owner']}"/><br/>
    <label class="w3-label">Services: (Required. A list of one or more services. Example: lawnmowing, trimming)</label><input class="w3-input w3-border" type="text" name="services"  required="required" size="50"  maxlength="100" value="{$row['services']}"/><br/>
    <label class="w3-label">Anderson Island Address: (Required. not displayed to customers.)</label><input class="w3-input w3-border" type="text"  name="address"  required="required" size="50" maxlength="50" value="{$row['address']}" /><br/>
    <label class="w3-label">Phone Number: (Required, 10 digits with area code)</label><input class="w3-input w3-border" type="tel"  name="phone"  required="required" value="{$row['phone']}"/><br/>
    <label class="w3-label">CellPhone Number:(optional, 10 digits with area code)</label><input class="w3-input w3-border" type="tel"  name="phone2" value="{$row['phone2']}" /><br/>
    <label class="w3-label">Email: (required, must be a valid email address)</label><input class="w3-input w3-border" type="email" name="email"  required="required" value="{$row['email']}"/><br/>
    <label class="w3-label">Contractor #: (optional)</label><input class="w3-input w3-border" type="text" name="contractor"  maxlength="50" value="{$row['contractor']}"/><br/>
    <label class="w3-label">Web Site: (optional url, not displayed to customers)</label><input class="w3-input w3-border" type="url" name="website"  value="{$row['website']}" /><br/>
    <!--<label class="w3-label">Additional Information:</label><input class="w3-input w3-border" type="text"  name="notes" size="255"/><br/>-->
    <label class="w3-label">Notes: (optional, not displayed to customers)</label><textarea class="w3-input w3-border" name="notes" rows="12">value="{$row['notes']}</textarea><br/>
    <input type="hidden" name="oldpassword" value="{$row['password']}">
    <input type="hidden" name="oldbusiness" value="{$row['bname']}">

    <input type="submit" value="UPDATE"/>
</form>
HEREDOC;
    echo $s;
        ?>
</body>
</html>
