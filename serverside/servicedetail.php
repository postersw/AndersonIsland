<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  servicedetail - generates the services table detail for 1 business, displayed to the user
//
//  entry   servicedetail.php?business=business name encoded
//

if(empty($myconn)) {
    include "dbconnect.php"; // connect to the database.  returns $myconn.
}
$businessname = $_GET["business"];
$businessname = htmlspecialchars_decode($businessname);  // will this work???
echo "2Generating detail for $businessname<br/>";

$sql = "Select * from business where business='" . $businessname . "'";
$result = $myconn->query($sql);
echo "rows " . $result->num_rows;

if($result->num_rows == 0) {
    echo "Business $business does not exist.<br/>";
    exit();
}
$row = $result->fetch_assoc();


// this is the services.html header
$hd=<<<'DOC'
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
    <p >
DOC;

echo $hd; // send the header
echo "<h1 class='w3-brown w3-text-white'>" . $row["business"] . "</h1><br/><b>Category: " . $row["category"] . "</b><br/>Services: ". $row["services"] . "<br/>Owner: " . $row["owner"] . "<br/>Contractor number: " . $row["contractor"] .
    "<br/>Phone numbers: " . $row["phone"] . ", " . $row["phone2"] . "<br/>Email:  <a href='mailto:" . $row["email"] . "'>" . $row["email"] . "</a>" .
    "<br/>Address: " . $row["address"] . " on Anderson Island<br/><br/>";
echo $row["notes"];


$ed= <<<'END'
    <p style="font-size:x-small">This information has been provided by each individual, and its accuracy, dependability, or reliability cannot be guaranteed by
    the Anderson Island Assistant or Poster Software, LLC.<br/>
    Updated 12/21/2018</p>
</div>
</body>
</html>
END;
echo $ed;
?>


