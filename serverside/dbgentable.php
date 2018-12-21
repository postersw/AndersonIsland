<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  dbgentable - generates the services table displayed to the user
//  writes the table to file services.txt.
//

if(empty($myconn)) {
    include "dbconnect.php"; // connect to the database.  returns $myconn.
}
{

    echo "Generating table<br/>";

    $sql = "Select * from business order by category, business";
    $result = $myconn->query($sql);
    echo "rows " . $result->num_rows;

    if($result->num_rows == 0) {
        echo "No businesses.<br/>";
        exit();
    }
    $cat = "";
    $t = "<table>";
    while($row = $result->fetch_assoc()) {
        if($row["category"] != $cat) {
            $cat = $row["category"];
            $t = $t . "<tr><td> </td></tr><tr><td class='w3-brown w3-text-white' ><b>" . $cat . "</b></td></tr>";
        }
        $t = $t . "<tr><td><b>" . $row["business"] . "</b><br/>" . $row["services"] . "<br/>" . $row["owner"] . "<br/>" .
            $row["phone"] . ", " . $row["phone2"] . "</td></tr>";
    }
    $t = $t . "</table>";

    echo $t;
}
?>