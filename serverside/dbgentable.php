<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  dbgentable - generates the services table displayed to the user
//  writes the table to file services.txt.
//
$servicetablefile = "services.html";

if(empty($myconn)) {
    include "dbconnect.php"; // connect to the database.  returns $myconn.
}


    echo "Generating table<br/>";

    $sql = "Select * from business order by category, business";
    $result = $myconn->query($sql);
    echo "rows " . $result->num_rows;

    if($result->num_rows == 0) {
        echo "No businesses.<br/>";
        exit();
    }
    $cat = "";
    $t = '<table style="width:100%;padding:0;border-collapse:collapse">';
    while($row = $result->fetch_assoc()) {
        if($row["category"] != $cat) {
            $cat = $row["category"];
            $t = $t . "<tr><td> </td></tr><tr><td class='w3-brown w3-text-white' ><b>" . $cat . "</b></td></tr>";
        }
        $t = $t . "<tr><td><b>" . $row["business"] . "</b><br/>" . $row["services"] . "<br/>" . $row["owner"] . " " . $row["contractor"] . "<br/>" .
            $row["phone"] . ", " . $row["phone2"] . "  <a href='mailto:" . $row["email"] . "'>" . $row["email"] . "</a></td></tr>\n";
    }
    $t = $t . "</table>";
    echo $t;

    // this is the services.html header
    $hd=<<<'AAA'
<!DOCTYPE html>
    <html lang="en" xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, height=device-height, target-densitydpi=medium-dpi" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <title>Local Anderson Island Services</title>
    <link rel="stylesheet" href="lib/w3.css">
    <style>
        tr {border-style: solid; border-width: thin;border-color: gray; padding:6px; }
        .cat {background-color: lightgray}
        p {font-size:small}
    </style>
    </head>
    <body>
        <div class="w3-container" style="background-color:antiquewhite">
    <h1 class="w3-brown w3-text-white">Local Anderson Island Services</h1>
    <p >
    This page lists <strong>Anderson-Island-based</strong> local services, such as plumbing, electrical, yard maintenance, music lessons, etc.
    (Businesses based on the mainland are NOT listed here.)</p>
    <p><a href="servicesignup.html">To list yourself or your business here, click on this link.</a>
    This is a community information service and is provided free of charge.</p>
AAA;
    // this is the services html trailer
    $ed=<<<'END'
    <p style="font-size:x-small">This information has been provided by each individual, and its accuracy, dependability, or reliability cannot be guaranteed by
    the Anderson Island Assistant or Poster Software, LLC.<br/>
    Updated 12/21/2018</p>
END;

    // write to services.txt
    $fh = fopen($servicetablefile, 'w');
    fwrite($fh, $hd);
    fwrite($fh, $t);
    fwrite($fh, $ed);
    fclose($fh);

?>