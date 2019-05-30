<?php
//////////////////////////////////////////////////////////////////////////////////
//  dbupdate - update an existing business in the database.
//  I'm sure this is overkill for our little business database, but it's fun and interesting.
//  rfb 1/3/19.
//
//  Entry: called by serviceupdate.php
//          $_POST = all the modified database values  (oldpassword = original pw. id = business id.).
//  Exit: Runs an SQL Update statement to update all changed fields.
//        Will also update password and change business name if necessary.
//        calls: dbgentable2.php to update services.html.


include "dbconnect.php"; // connect to the database.  returns $myconn.
{

// this is the services.html header
$hd=<<<'DOC'
<!DOCTYPE html>
    <html lang="en" xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, height=device-height, target-densitydpi=medium-dpi" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <title>Update Anderson Island Services</title>
    <link rel="stylesheet" href="lib/w3.css">
    <style>
        tr {border-style: solid; border-width: thin;border-color: gray; padding:6px; }
        .cat {background-color: lightgray}
        p {font-size:small}
    </style>
    </head>
    <body>
        <div class="w3-container" style="background-color:antiquewhite">
    <p >
DOC;

    echo $hd; // send the header
    $deleteme = false;

    // unpack request
    $id = preg_replace('/\D/', '', $_POST["id"]);// allow only numbers by deleting all non numbers /\D/ to prevent sql injection
    $oldbusiness = $myconn->real_escape_string($_POST['oldbusiness']);
    $oldpassword = $_POST['oldpassword'];
    $business = $myconn->real_escape_string(trim($_POST['bname']));
    $password = trim($_POST['password']);
    $category = strtoupper (trim($_POST['category']));  // force upper case category
    $category2 = strtoupper (trim($_POST['category2']));  // force upper case category2
    $services = trim($_POST['services']);
    $owner= trim($_POST['owner']);
    $address = trim($_POST['address']);
    $city = "AI";//$_POST['city'];
    $state = "WA";//$_POST['state'];
    $zip = "98303";//$_POST['zip'];
    $phone = preg_replace("/[^0-9]/", "", ($_POST['phone']));  // all numeric phone number
    $phone2 = preg_replace("/[^0-9]/", "", ($_POST['phone2']));
    $email = trim($_POST['email']);
    $website = trim($_POST['website']);
    $contractor = trim($_POST['contractor']);
    $notes = trim($_POST['notes']);
    // clean info
    $business = preg_replace("/[^\w\.\,\ \&\(\)\']/", "", $business); // remove all non an, allow ., &()'
    $approval = false; // set true if this is an approval

    echo "<h2 class='w3-brown w3-text-white'>Update $oldbusiness</h2>";

    //echo "reading $oldbusiness<br/>";
    // read the existing record for the business
    $sql = "Select * from business where id=$id";
    $result = $myconn->query($sql);
    if($result->num_rows != 1) {
        echo "<br/><br/>ERROR: The business '" . $oldbusiness . "' does not exist.<br/>";
        exit();
    }

    // Check the password
    $row = $result->fetch_assoc(); // get first row
    if($oldpassword != $row['password'] && $oldpassword != $adminpw) {
        echo "<br/><br/>ERROR: Invalid password. <br/>If you forgot your password, send email to support@anderson-island.org.";
        exit();
    }
    // special case for approval
    $ok = $row['ok'];
    if($ok == 0 && $password == "APPROVAL") {
        $password = "";
        $ok = 1; // mark as approved
        $approved = true;
    }

    // delete special case
   if($_POST['deleteme']=='yes') {
        $deleteme = true;
        $sql = "DELETE from business   ";
        $msg = "$business permanently deleted.<br/>";

    } else {

        // now build the change request for each field
        $sql = "UPDATE business SET ";
        $updatecount = 0; // field count
        $msg = "";
        UpD("business", $business);
        UpD("category", $category);
        UpD("category2", $category2);
        if($password!="") UpD("password", $password);  // update password only if changed
        UpD("services", $services);
        UpD("owner", $owner);
        UpD("address", $address);
        UpD("phone", $phone);
        UpD("phone2", $phone2);
        UpD("email", $email);
        UpD("website", $website);
        UpD("notes", $notes);
        UpD("contractor", $contractor);
        UpD("ok", $ok);  // approval flag
        if($updatecount == 0) {
            echo "No fields have changed. The business will not be updated.<br/>";
            exit();
        }
    }
    // display info
    $emailbody= "AIA Business Listing update request for: <br/><b>" . $oldbusiness .":</b><br/>The following changes have been made:<br/>" . $msg . "<br/>";
    $emailaddr = "postersw@comcast.net,robertbedoll@gmail.com," . $email;
    $headers = "From: support@anderson-island.org\r\nMime-Version: 1.0\r\nContent-type: text/html; charset=\"iso-8859-1\"";

    // validate request
    if($business != $oldbusiness) {
        if(ValidateRequest() == false) {
            echo "<br>Business could not be updated.<br/>";
            exit;
        }
    };

    // update it or delete it

    $sql = substr($sql, 0, strlen($sql)-1) . " WHERE id=$id";
    //echo "<br/>" . $sql . "<br/>"; exit(0);

    if ($myconn->query($sql) === TRUE) {  // update successful
        echo $emailbody;
        echo "Record updated successfully<br/>";
        echo "<a href='https://www.anderson-island.org/services/servicedetail.php?id=$id'>Click here to see updated listing.</a><br/>";
        $emailbody = $emailbody . "<a href='https://www.anderson-island.org/services/servicedetail.php?id=$id'>Click here to see your updated listing.</a><br/>" .

        // add to log file
        $fhl = fopen("../private/servicesignuplog.log", 'a');
        fwrite($fhl, date("Y/m/d H:i:s") . "|" . $emailbody . $sql . "\n");
        fclose($fhl);


        // send message to us and client.  Client gets unique message for approval.
        if($approved) $emailbody = "$owner,<br/>Your service business listing for $business has been approved and is now available.<br/>." .
            "<a href='https://www.anderson-island.org/services/servicedetail.php?id=$id'>Click here to see updated listing.</a><br/>" .
            "You may edit your listing at any time. Tap on your listing, then tap on the EDIT button.<br/><br/>Thanks,<br/>Bob Bedoll<br/>Anderson Island Assistant, Poster Software, LLC.";
        // mail it
        $r = mail($emailaddr, "AIA Business Listing update",$emailbody,$headers);
        if($r == false) {
            echo "Your service update failed. Contact support@anderson-island.org";
            exit(0);
        }

        // regenerate services.html
        if($approved == false) $nolog = 1;  // suppress the log
        include "dbgentable2.php"; // regenerate services.html

    } else {  // update failed
        echo "Error updating record: " . $myconn->error;
        $r = mail("support@postersw.com", "AIA Business Listing update FAILURE","UPDATE FAILURE: " . $emailbody,$headers);
        // add to log file
        $fhl = fopen("../private/servicesignuplog.log", 'a');
        fwrite($fhl, date("Y/m/d H:i:s") . "| UPDATE FAILED:" . $emailbody . $sql . "\n");
        fclose($fhl);

    }

    // final html
    echo date("Y/m/d H:i:s");
    echo "</div></body><html>";
}


//////////////////////////////////////////////////////////
//  ValidateRequest - check for a NEW business name
function ValidateRequest() {
    global $business;
    global $myconn;
    if($business == "") {
        echo "<br/><br/>ERROR: invalid business name.<br/>";
        return false;
    }
    $sql = "SELECT business FROM business WHERE business='$business'";
    $result = $myconn->query($sql);
    if($result->num_rows > 0) {
        echo "<br/><br/>ERROR: The business '" . $business . "' already exists. Your business name must be unique.<br/>";
        return false;
    }
    return true;
}

//////////////////////////////////////////////////////////////
//  UpD - update
//  Entry: fieldname = text name of field, newvalue = new value in form
//  Exit: adds to $sql and $msg
function UpD($fieldname, $newvalue) {
    global $myconn;
    global $row;
    global $sql;
    global $msg, $updatecount;
    //echo $row[$fieldname] . " vs " . $newvalue . "<br/>";
    if($row[$fieldname] == $newvalue) return; // if no delta
    //echo $row[$fieldname] . "!=" . $newvalue . "<br/>";
    $sql = $sql . $fieldname . "='" . $myconn->real_escape_string($newvalue) . "',";
    $msg = $msg . $fieldname . ": " . $newvalue . "<br/>";
    $updatecount++;
}


?>