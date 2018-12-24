<?php
//////////////////////////////////////////////////////////////////////////////////
//  dbupdate - update an existing business in the database.
//  I'm sure this is overkill for our little business database, but it's fun and interesting.
//  rfb 12/23/18.

include "dbconnect.php"; // connect to the database.  returns $myconn.
{

    echo "Updating Business<br/>";

    // unpack request
    $oldbusiness = $myconn->real_escape_string($_POST['oldbusiness']);
    $oldpassword = $_POST['oldpassword'];
    $business = trim($_POST['bname']);
    $password = trim($_POST['password']);
    $category = strtoupper (trim($_POST['category']));  // force upper case category
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
    $business = preg_replace("/[^\w\.\,\ \&\(\)]/", "", $business); // remove all non an, allow ., &()

    // read the existing record for the business
    $sql = "Select * from business where business='" . $oldbusiness . "'";
    $result = $myconn->query($sql);
    if($result->num_rows = 0) {
        echo "<br/><br/>ERROR: The business '" . $business . "' does not exist.<br/>";
        exit();
    }

    // Check the password
    $row = $result->fetch_assoc(); // get first row
    if($oldpassword != $row['password']) {
        echo "<br/><br/>ERROR: Invalid password. If you forgot your password, send email to support@anderson-island.org.";
        exit();
    }

    // now build the change request for each field
    $sql = "UPDATE business SET ";
    $updatecount = 0; // field count
    $msg = "";
    UpD("business", $business);
    UpD("category", $category);
    UpD("password", $password);
    UpD("services", $services);
    UpD("owner", $owner);
    UpD("address", $address);
    UpD("phone", $phone);
    UpD("phone2", $phone2);
    UpD("email", $email);
    UpD("website", $website);
    UpD("notes", $notes);
    UpD("contractor", $contractor);
    if($updatecount == 0) {
        echo "No fields have changed. The business will not be updated.<br/>";
        exit();
    }

    // display info

    echo "Business update request for: <br/><b>" . $oldbusiness ."</b>" . $msg . "<br/>";

    // validate request
    if($business != $oldbusiness) {
        if(ValidateRequest() == false) {
            echo "<br>Business could not be updated.<br/>";
            exit;
        }
    };

    // update it

    $sql = substr($sql, 0, strlen($sql)-1) . " WHERE business='$oldbusiness'";
    echo "<br/>" . $sql . "<br/>";
    if ($myconn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $myconn->error;
    }
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
    //echo "<br/>" . $sql ."<br/>";
    //$stmt = $myconn->prepare($sql);
    //$rc = $stmt->bind_param('s',$business);
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
    if($row[$fieldname] == $newvalue) return; // if no delta
    $sql = $sql . $fieldname . "='" . $myconn->real_escape_string($newvalue) . "',";
    $msg = $fieldname . ": " . $newvalue . "<br/>";
    $updatecount++;
}

?>