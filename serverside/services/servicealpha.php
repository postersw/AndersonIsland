<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  servicealpha - generates the services table displayed alphabetically to the user
//  RFB.  1/13/19.
//
//  Entry:
//  Exit: displays from  the database
//

if(empty($myconn)) {
    include "dbconnect.php"; // connect to the database.  returns $myconn.
}
$nolog = 1;  // suppress debugging

if($nolog==0) echo "Generating table<br/>";

// Query the database for all businesses
$sql = "SELECT `business`, `category`, category2,  `owner`, `phone`, `email`, `phone2`, `services`, `id` FROM business WHERE ok=1 " .
    "ORDER BY business";
$result = $myconn->query($sql);
if($nolog==0) echo "rows " . $result->num_rows;

if($result->num_rows == 0) {
    echo "No businesses.<br/>";
    exit();
}

// loop through database and generate html
$t = "<table>";
while($row = $result->fetch_assoc()) {
    $t = $t . "<tr><td onclick=\"window.open('http://www.anderson-island.org/servicedetail.php?id=" . urlencode ($row["id"]) . "', '_system');\">" .
        "<b>{$row["business"]}</b> ({$row["category"]} {$row["category2"]})<br/>{$row["services"]}<br/>{$row["owner"]}<br/>" .
        FPhone($row["phone"]) . ", " . FPhone($row["phone2"]) . "  <a href='mailto:" . $row["email"] . "'>" . $row["email"] . "</a></td></tr>\n";
}

$t = $t . "</table>\n";

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
        td {border-style: solid; border-width: thin;border-color: black; padding:6px;font-size:small }
        .cat {background-color: lightgray}
        p {font-size:small}
    </style>
    </head>
    <body>
        <div class="w3-container" style="background-color:antiquewhite">
    <h2 class="w3-brown w3-text-white">Local AI Services</h2>
Tap on a business for more info.</p>
AAA;

// this is the services html trailer
$ed=<<<'END'
    <p >
    This page lists <strong>only Anderson-Island-based</strong> local services, such as plumbing, yard maintenance, music lessons, etc.
</p>
    <p><a href="servicesignup.html">To list yourself or your business here for free, click on this link.</a>
   <br/>

    <button onclick="window.open('http://www.anderson-island.org/services.html')">Show by Category</button>
    <p style="font-size:x-small">This information has been provided by each business, and its accuracy, dependability, or reliability cannot be guaranteed by
    the Anderson Island Assistant or Poster Software, LLC.<br/>
END;

//date_default_timezone_set("America/Los_Angeles"); // set PDT

// write to user

echo $hd; 
echo $t;
echo $ed;

////////////////////////////////////////////////////////////////////////////////
//  FPhone - format phone number and return the formatted string (nnn) nnn-nnnn
function FPhone($pn) {
    if($pn==null) return "";
    if($pn=="") return "";
    return "<a href='tel:$pn'>(" . substr($pn, 0,3) . ") " . substr($pn, 3, 3) . "-" . substr($pn, 6). "</a>";
}
?>
