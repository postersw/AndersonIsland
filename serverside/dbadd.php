<?php
//////////////////////////////////////////////////////////////////////////////////
//  dbadd - add a business to the database.
//  I'm sure this is overkill for our little business database, but it's fun and interesting.
//  rfb 12/20/18.

include "dbconnect.php"; // connect to the database.  returns $myconn.
{

    echo "Adding Business<br/>";

    // unpack request
    $business = trim($_POST['business']);
    $category = trim($_POST['category']);
    $services = trim($_POST['services']);
    $owner= trim($_POST['owner']);
    $address = trim($_POST['address']);
    $city = "AI";//$_POST['city'];
    $state = "WA";//$_POST['state'];
    $zip = "98303";//$_POST['zip'];
    $phone = trim($_POST['phone']);
    $phone2 = trim($_POST['phone2']);
    $email = trim($_POST['email']);
    $website = trim($_POST['website']);
    $contractor = trim($_POST['contractor']);
    $notes = trim($_POST['notes']);
    echo "Business request: <br/>" . $business ."<br/>Category: " . $category . "<br/>Services:" . $services . "<br/>Owner:" . $owner . "<br/>Address: " . $address .
    "<br/>City: " . $city . ", State:" . $state . ", Zip: ". $zip . "<br/>Phone: ". $phone . ", phone2: ". $phone2 ."<br/>Email:" . $email . "<br/>Website :" . $website .
    "<br/>Contractor: " . $contractor .  "<br/>Notes: " . $notes . "<br.>";

    // validate request
    if(ValidateRequest() == false) {
        echo "invalid request<br/>";
        exit;
    };

    // add it
    $sql = "INSERT INTO business (business,category,services,owner,address,city,state,zip,phone,phone2,email,website,contractor,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $myconn->prepare($sql);
    if(!$stmt) {
        echo 'Error: '.$myconn->error;
    }

    /* Bind parameters */
    $stmt->bind_param('ssssssssssssss',$business,$category,$services,$owner,$address,$city,$state,$zip,$phone,$phone2,$email,$website,$contractor,$notes);
    $business = $_POST['business'];
    /* Execute statement */
    $stmt->execute();
    echo " insert id=" . $stmt->insert_id . ", rows=" . $stmt->affected_rows . "<br/>";
    $stmt->close();

    // reply
    echo "<br/>Business added.";
}

////////////////////////////////////////////////////////
function UnpackRequest() {
}

//////////////////////////////////////////////////////////
function ValidateRequest() {
    global $business;
    global $myconn;
    if($business = "") {
        echo "invalid business name.";
        return false;
    }
    $sql = "SELECT business FROM business WHERE business='" . $business . "'";
    $result = $myconn->query($sql);
    if($result->num_rows > 0) {
        echo "Business " . $business . " already exists. Your business name must be unique.<br/>";
        return false;
    }
    return true;
}

?>