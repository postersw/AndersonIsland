<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  servicedetail - generates the services table detail for 1 business, displayed to the user
//
//  entry   servicedetail.php?id=id # of business
//  exit    the EDIT button calls serviceeditauth.php to get the password for the update.
//          EDIT -> serviceeditauth.php -> serviceupdate.php -> dbupdate.php -> dbgentable2.php
//
//  RFB. 1/3/2019.

if(empty($myconn)) {
    include "dbconnect.php"; // connect to the database.  returns $myconn.
}
//$businessnamehtml = $_GET["business"];
//$id = $myconn->real_escape_string($_GET["id"]);  // will this work???
$id = preg_replace('/\D/', '', $_GET["id"]);// allow only numbers by deleting all non numbers /\D/ to prevent sql injection

//echo "Generating detail for $businessname<br/>";

$sql = "Select * from business where id=" . $id ;
$result = $myconn->query($sql);
//echo "rows " . $result->num_rows;

if($result->num_rows == 0) {
    echo "Business ID $id does not exist.<br/>";
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
echo "<h2 class='w3-brown w3-text-white'> {$row['business']} </h2>";
echo "<b>Category: {$row['category']}</b><br/>";
if($row['category2'] != "") echo "<b>Category 2: {$row['category2']}</b><br/>";
if($row['category3'] != "") echo "<b>Category 3: {$row['category3']}</b><br/>";
echo "Services: {$row['services']}<br/>Owner: {$row['owner']}<br/>Contractor number: {$row['contractor']}<br/>";
echo "Phone numbers: " . FPhone($row["phone"]) . ", " . FPhone($row['phone2']);
echo "<br/>Email: <a href='mailto:{$row['email']}'>{$row['email']}</a><br/>";
if($row['website'] != "") echo "<a href='" . $row["website"] . "'>Website: {$row['website']}</a><br>";
echo "Address:  {$row['address']} on Anderson Island<br/><br/>";
echo $row["notes"];
echo "<br/>Updated: {$row['updated']}<br/><br/>";
//$businessnameencoded = htmlspecialchars($businessname);
echo "<button onclick=\"window.open('https://www.anderson-island.org/services/serviceeditauth.php?id=" . urlencode($id) . "&business=" . urlencode($row['business']) . "', '_system');\">EDIT LISTING</button>";  // edit button


$ed= <<<'END'
    <br/><p style="font-size:x-small">This information has been provided by this business, and its accuracy, veracity, dependability, or reliability cannot be guaranteed by
    the Anderson Island Assistant or Poster Software, LLC.<br/>
</p>
</div>
</body>
</html>
END;
echo $ed;

////////////////////////////////////////////////////////////////////////////////
//  FPhone - format phone number and return the formatted string (nnn) nnn-nnnn
function FPhone($pn) {
    if($pn==null) return "";
    if($pn=="") return "";
    return "<a href='tel:$pn'>(" . substr($pn, 0,3) . ") " . substr($pn, 3, 3) . "-" . substr($pn, 6). "</a>";
}
?>


