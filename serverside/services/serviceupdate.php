<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, height=device-height, target-densitydpi=medium-dpi" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <title>Update Local Anderson Island Service</title>
    <link rel="stylesheet" href="lib/w3.css" />
    <style>
        tr {
            border-style: solid;
            border-width: thin;
            border-color: gray;
            padding: 6px;
        }

    </style>
</head>
<body>
    <div class="w3-container" style="background-color:antiquewhite">

        <?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////
//  serviceupdate.php Present updatable record to user.
//  Entry   Called by serviceeditauth.php with the password.
//          Post:  "business" = business name primary key
//          "password" = password, not encoded
//  Exit    calls dbupdate.php to update the database. dbupdate calls dbgentable2.php to update services.html
//
//  RFB. 1/3/2019


include "dbconnect.php"; // connect to the database.  returns $myconn.

    $business = $myconn->real_escape_string((trim($_POST['business'])));
    $password = trim($_POST['password']);
    $id = preg_replace('/\D/', '', $_POST["id"]);// allow only numbers by deleting all non numbers /\D/ to prevent sql injection

    echo "<h2 class='w3-brown w3-text-white'>UPDATE SERVICE LISTING</h2>";

    // read the record.  "business" = business name primary key
    $sql = "Select * from business where id=$id";
    $result = $myconn->query($sql);

    // Check the password
    if($result->num_rows == 0) {
        echo "<br/>ERROR: The business '" . $business . "' does not exist.<br/>";
        exit();
    }

    // Check the password
    $row = $result->fetch_assoc(); // get first row
    if($password != $row['password']) {
        echo "<br/><b>ERROR: Invalid password.</b> <br/>If you forgot your password, send email to <a href=\"mailto:support@anderson-island.org\">support@anderson-island.org</a><br/>";
        exit();
    }

    // generate the html for the edit form;
    $s=<<<HEREDOC
       <p>
     To update your business listing, simply make changes on this form and click the UPDATE button.
    Your changes will appear immediately.
        </p>
<form class="w3-container" name="business" method="post" action="dbupdate.php">
    <label class="w3-label">Business Name: (You may change your business name here. This name must be unique. ) </label><input class="w3-input w3-border" type="text" name="bname"  required="required" maxlength="40" value="{$row['business']}"/> <br/>
    <label class="w3-label">DELETE Business: (Check to DELETE your listing. This removes your listing.) </label><input class="w3-check w3-border" type="checkbox" name="deleteme" /> <br/><br/>
    <label class="w3-label">Password: (Fill in only to CHANGE your password.) </label><input class="w3-input w3-border" type="text" name="password"  size="20" maxlength="20" /> <br/>
    <label class="w3-label">Business Category: (Required. One Category. Example: Landscaping)</label><input class="w3-input w3-border" type="text" name="category" required="required" maxlength="40" value="{$row['category']}" /><br/>
    <label class="w3-label">Optional 2nd Category: (Optional)</label><input class="w3-input w3-border" type="text" name="category2" maxlength="40" value="{$row['category2']}" /><br/>
    <label class="w3-label">Owner: (Required)</label><input class="w3-input w3-border" type="text" name="owner"  required="required" size="50" maxlength="40" value="{$row['owner']}"/><br/>
    <label class="w3-label">Services: (Required. A list of one or more services. Example: lawnmowing, trimming)</label><input class="w3-input w3-border" type="text" name="services"  required="required" size="50"  maxlength="100" value="{$row['services']}"/><br/>
    <label class="w3-label">Anderson Island Address: (Required.)</label><input class="w3-input w3-border" type="text"  name="address"  required="required" size="50" maxlength="50" value="{$row['address']}" /><br/>
    <label class="w3-label">Phone Number: (Required, 10 digits with area code)</label><input class="w3-input w3-border" type="tel"  name="phone"  required="required" value="{$row['phone']}"/><br/>
    <label class="w3-label">CellPhone Number:(optional, 10 digits with area code)</label><input class="w3-input w3-border" type="tel"  name="phone2" value="{$row['phone2']}" /><br/>
    <label class="w3-label">Email: (required, must be a valid email address)</label><input class="w3-input w3-border" type="email" name="email"  required="required" value="{$row['email']}"/><br/>
    <label class="w3-label">Contractor #: (optional)</label><input class="w3-input w3-border" type="text" name="contractor"  maxlength="50" value="{$row['contractor']}"/><br/>
    <label class="w3-label">Web Site: (optional url)</label><input class="w3-input w3-border" type="url" name="website"  value="{$row['website']}" /><br/>
    <!--<label class="w3-label">Additional Information:</label><input class="w3-input w3-border" type="text"  name="notes" size="255"/><br/>-->
    <label class="w3-label">Notes: (optional, not displayed to customers)</label><textarea class="w3-input w3-border" name="notes" rows="12">{$row['notes']}</textarea><br/>
    <input type="hidden" name="oldpassword" value="$password">
    <input type="hidden" name="id" value="$id">
    <input type="hidden" name="oldbusiness" value="{$row['business']}">


    <input type="submit" value="UPDATE"/>
</form>
HEREDOC;
    echo $s;
        ?>
</div>
</body>
</html>
