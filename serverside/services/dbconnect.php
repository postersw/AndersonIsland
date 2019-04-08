<?php
///////////////////////////////////////////////////////////////////////////
//  Connect to the AIA database
require("../../private/dbconnectconfig.php");
$myconn = new mysqli($dbhost, $dbuser, $dbpassword, $database);

if ($myconn->connect_errno) {
    echo "Failed to connect to MySQL: " . $myconn->connect_error;
    exit;
}
//echo "successfully connected to database $database<br/>";

?>