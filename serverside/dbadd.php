<?php
//////////////////////////////////////////////////////////////////////////////////
//  dbadd - add a business to the database.
//  I'm sure this is overkill for our little business database, but it's fun and interesting.
//  rfb 12/20/18.

include "dbconnect.php"; // connect to the database.  returns $myconn.
{

    echo "adding business<br/>";

    // unpack request
    $business = $_POST['business'];
    $category = $_POST['category'];
    $owner= $_POST['owner'];
    $address = $_POST['address'];
    $city = "AI";//$_POST['city'];
    $state = "WA";//$_POST['state'];
    $zip = "98303";//$_POST['zip'];
    $phone = $_POST['phone'];
    $phone2 = $_POST['phone2'];
    $email = $_POST['email'];
    $website = $_POST['website'];
    $contractor = $_POST['contractor'];
    $notes = $_POST['notes'];
    echo "Business request: " . $business .", category: " . $category . ", owner:" . $owner . ",address: " . $address .
    ", city: " . $city . ", state:" . $state . ", zip: ". $zip . ", phone: ". $phone . ", phone2: ". $phone2 .", email:" . $email . ", website :" . $website .
        ",contractor: " . $contractor .  ", notes: " . $notes . "<br.>";

    // validate request
    if(ValidateRequest() == false) {
        echo "invalid request<br/>";
        exit;
    };

    // add it
    $sql = "INSERT INTO businesses (business,category,owner,address,city,state,zip,phone,phone2,email,website,contractor,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $myconn->prepare($sql);
    if(!$stmt) {
        echo 'Error: '.$myconn->error;
    }

    /* Bind parameters */
    $stmt->bind_param('sssssssssssis',$business,$category,$owner,$address,$city,$state,$zip,$phone,$phone2,$email,$website,$contractor,$notes);
    $business = $_POST['business'];
    /* Execute statement */
    $stmt->execute();
    echo " insert id=" . $stmt->insert_id . ", rows=" . $stmt->affected_rows . "<br/>";
    $stmt->close();

    // reply
    echo "business added";
}

////////////////////////////////////////////////////////
function UnpackRequest() {
}

//////////////////////////////////////////////////////////
function ValidateRequest() {
    global $business;
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