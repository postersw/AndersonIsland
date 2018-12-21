<?php
///////////////////////////////////////////////////////////////////////////
//  Connect to the AIA database
$host     = "localhost";
$user     = "posterswaia";
$password = "BobSueAIA";
$database = "AndersonIsland";
$myconn = new mysqli($host, $user, $password, $database);

if ($myconn->connect_errno) {
    echo "Failed to connect to MySQL: " . $myconn->connect_error;
    exit;
}
echo "successfully connected to database $database<br/>";

?>