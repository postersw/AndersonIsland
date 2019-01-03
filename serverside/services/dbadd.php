<?php
//////////////////////////////////////////////////////////////////////////////////
//  dbadd - add a business to the database.
//  I'm sure this is overkill for our little business database, but it's fun and interesting.
//  rfb 12/20/18.

include "dbconnect.php"; // connect to the database.  returns $myconn.
{

    echo "Adding Business<br/>";

    // unpack request
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
    // display info
    echo "Business request for: <br/><b>" . $business ."</b><br/>Category: " . $category . "<br/>Services:" . $services . "<br/>Owner:" . $owner . "<br/>Address: " . $address .
    "<br/>City: " . $city . ", State:" . $state . ", Zip: ". $zip . "<br/>Phone: ". $phone . ", phone2: ". $phone2 ."<br/>Email:" . $email . "<br/>Website :" . $website .
    "<br/>Contractor: " . $contractor .  "<br/>Notes: " . $notes . "<br.>";

    // validate request
    if(ValidateRequest() == false) {
        echo "<br>Business could not be added.<br/>";
        exit;
    };

    // add it
    $sql = "INSERT INTO business (business,ok,password,category,services,owner,address,city,state,zip,phone,phone2,email,website,contractor,notes) VALUES (?,0,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    //echo "past sql";
    $stmt = $myconn->prepare($sql);
    //echo " past prepare";
    if(!$stmt) {
        echo 'Error: '.$myconn->error;
    }

    /* Bind parameters */
    $rc = $stmt->bind_param('sssssssssssssss',$business,$password,$category,$services,$owner,$address,$city,$state,$zip,$phone,$phone2,$email,$website,$contractor,$notes);
    //echo " past bind";
    if ( false===$rc ) {
        die('bind() failed: ' . htmlspecialchars($stmt->error));
    }

    /* Execute statement */
    $rc = $stmt->execute();
    if ( false===$rc ) {
        die('execute() failed: ' . htmlspecialchars($stmt->error));
    }
    echo "Added rows=" . $stmt->affected_rows . "<br/>";
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
    if($business == "") {
        echo "<br/><br/>ERROR: invalid business name.<br/>";
        return false;
    }
    $sql = 'SELECT business FROM business WHERE business="' . $business . '"';
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

?>