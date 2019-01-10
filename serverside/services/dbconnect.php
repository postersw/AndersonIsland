<?php
///////////////////////////////////////////////////////////////////////////
//  Connect to the AIA database
$dbhost     = "localhost";
$dbuser     = "posterswaia";
$dbpassword = "BobSueAIA";
$database = "AndersonIsland";
$adminpw = "2538480467";
$myconn = new mysqli($dbhost, $dbuser, $dbpassword, $database);

if ($myconn->connect_errno) {
    echo "Failed to connect to MySQL: " . $myconn->connect_error;
    exit;
}
//echo "successfully connected to database $database<br/>";

?>