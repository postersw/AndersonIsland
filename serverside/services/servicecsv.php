<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  servicecsv - generates the services table as a csv file to the user
//  RFB.  1/8/21.
//
//  Entry:
//  Exit: displays from  the database
//

if(empty($myconn)) {
    include "dbconnect.php"; // connect to the database.  returns $myconn.
}
$nolog = 1;  // suppress debugging

if($nolog==0) echo "Generating file";

// Query the database for all businesses
$sql = "SELECT * FROM business WHERE ok=1 ORDER BY business";
$result = $myconn->query($sql);
if($nolog==0) echo "rows " . $result->num_rows;

if($result->num_rows == 0) {
    echo "No businesses.<br/>";
    exit();
}
header('Content-Type: application/csv');
    // tell the browser we want to save it instead of displaying it
header('Content-Disposition: attachment; filename="services.csv";');
    // make php send the generated csv lines to the browser
    //fpassthru($f);
// loop through database and generate html
echo "id,business,category,category2,services,owner,phone,phone2,email,address,contractor,website,notes,updated\n";
while($row = $result->fetch_assoc()) {
    echo "{$row['id']},\"{$row['business']}\",{$row['category']},{$row['category2']},\"{$row['services']}\",{$row['owner']}," . 
    "{$row['phone']},{$row['phone2']},{$row['email']},\"{$row['address']}\",{$row['contractor']},\"{$row['website']}\",\"{$row['notes']}\",{$row['updated']}\n";
}


//date_default_timezone_set("America/Los_Angeles"); // set PDT

// write to user


////////////////////////////////////////////////////////////////////////////////
//  FPhone - format phone number and return the formatted string (nnn) nnn-nnnn
function FPhone($pn) {
    if($pn==null) return "";
    if($pn=="") return "";
    return "<a href='tel:$pn'>(" . substr($pn, 0,3) . ") " . substr($pn, 3, 3) . "-" . substr($pn, 6). "</a>";
}
?>
