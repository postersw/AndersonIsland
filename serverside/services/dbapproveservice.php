<!DOCTYPE html>
<html>
<head>
    <title>AIA Approve Service</title>
</head>
<body>
    <div>AIA Approve Service</div>
    <?php
//////////////////////////////////////////////////////////////////////////////////////
//  dbapproveservice.php  approve a service listing
//  dbapproveservice.php/id=nn&pw=nnnn
//
//  1. Sets the OK flag=1
//  2. sends an email to the owner
//  3. makes a log entry
//  4. runs dbgentable2.php to regenerate the listings
//

    // display info
    $emailbody= "AIA Business Listing approval request for: $oldbusiness<br/>";
    $emailaddr = "postersw@comcast.net,robertbedoll@gmail.com,$email";
    $headers = "From: support@anderson-island.org\r\nMime-Version: 1.0\r\nContent-type: text/html; charset=\"iso-8859-1\"";

    $id = preg_replace('/\D/', '', $_GET("id"));// allow only numbers by deleting all non numbers /\D/ to prevent sql injection
    $pw = preg_replace('/\D/', '',  $_GET("pw"));// allow only numbers by deleting all non numbers /\D/ to prevent sql injection
    if($pw != "2538480467") {
        echo "Invalid password<br/>";
        exit("Invalid password<br/>");
    }

    $sql = "UPDATE business SET 'ok' = 1 where 'id'=$id";
    include "dbconnect.php"; // connect to the database.  returns $myconn.

    $r = $myconn->query($sql);
    if ($r === TRUE) {  // update successful
        echo $emailbody;
        echo "Record approved successfully<br/>";
        echo "<a href='http://www.anderson-island.org/servicedetail.php?id=$id'>Click here to see updated listing.</a><br/>";

        // add to log file
        $fhl = fopen("../private/servicesignuplog.log", 'a');
        fwrite($fhl, date("Y/m/d H:i:s") . "|" . $emailbody . $sql . "\n");
        fclose($fhl);
        // mail it
        $r = mail($emailaddr, "AIA Business Listing update",$emailbody,$headers);
        if($r == false) {
            echo "Your service update failed. Contact support@anderson-island.org";
            exit(0);
        }
        // regenerate services.html
        include "dbgentable2.php"; // regenerate the table.

    } else {  // update failed
        echo "Error updating record: " . $myconn->error;
        $r = mail("support@postersw.com", "AIA Business Listing update FAILURE","UPDATE FAILURE: " . $emailbody,$headers);
        // add to log file
        $fhl = fopen("../private/servicesignuplog.log", 'a');
        fwrite($fhl, date("Y/m/d H:i:s") . "| UPDATE FAILED:" . $emailbody . $sql . "\n");
        fclose($fhl);

    }


    ?>
</body>
</html>