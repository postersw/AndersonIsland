<?php
////////////////////////////////////////////////////////////////////////////////////////////
//  dbgentable2 - generates the services table displayed to the user
//  writes the table to file services.html.
//  RFB.  12/23/18.
//
//  Entry: $nolog = 1 to suppress log (called from dbupdate.php).
//         $myconn = optional database connection
//  Exit: regenerates services.html from  the database
//

$servicetablefile = "services.html";
if(empty($nolog)) $nolog = 0; // enable log
if(empty($myconn)) {
    include "dbconnect.php"; // connect to the database.  returns $myconn.
}


    if($nolog==0) echo "Generating table<br/>";

    $sql = "Select * from business where ok=1 order by category, business";
    $result = $myconn->query($sql);
    if($nolog==0) echo "rows " . $result->num_rows;

    if($result->num_rows == 0) {
        echo "No businesses.<br/>";
        exit();
    }
	$clist = "clist=[";
    $catn = 0;
    $cat = "";
    $t = '<table style="width:100%;padding:0;border-collapse:collapse">' . "\n";

    while($row = $result->fetch_assoc()) {
        if($row["category"] != $cat) {
            $cat = $row["category"];
			if($catn > 0) $t = $t . "</table></div></td></tr> \n";
			$catn++;
            $catid = "C" . $catn;
			$clist = $clist . '"' . $catid . '",';
            //$t = $t . "<tr><td> </td></tr><tr><td class='w3-brown w3-text-white' ><b>" . $cat . "</b></td></tr>";
			 $t = $t . "<tr><td id='D$catid' class='w3-brown w3-text-white' style='font-size:medium' onclick=\"ShowHide('$catid')\" ><b>" . $cat . " &#9660;</td></tr> \n<tr><td> <div id='$catid' style='display:none;'> <table>";
        }
        $t = $t . "<tr><td onclick=\"window.open('http://www.anderson-island.org/servicedetail.php?id=" . urlencode ($row["id"]) . "', '_system');\"><b>" . $row["business"] .
            "</b><br/>" . $row["services"] . "<br/>" . $row["owner"] . "<br/>" .
            FPhone($row["phone"]) . ", " . FPhone($row["phone2"]) . "  <a href='mailto:" . $row["email"] . "'>" . $row["email"] . "</a>";
        // detail only: if($row["website"] != "") $t = $t . ", <a href='" . $row["website"] . "'>Website</a>";
        $t = $t . "</td></tr>\n";
    }

    $t = $t . "</table></div></td></tr> \n</table>\n";
    if($nolog==0) echo $t; // debug printout of businesses
	$clist = substr($clist, 0, strlen($clist)-1) . "];\n";
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
    <p >
    This page lists <strong>only Anderson-Island-based</strong> local services, such as plumbing, yard maintenance, music lessons, etc.
</p>
    <p><a href="servicesignup.html">To list yourself or your business here for free, click on this link.</a>
   <br/>
Tap on category to show businesses. Tap on a business for more info.</p>
AAA;

    // this is the services html trailer
    $ed=<<<'END'
<button onclick="clist.forEach(ShowHide)">Show All</button>
    <p style="font-size:x-small">This information has been provided by each business, and its accuracy, dependability, or reliability cannot be guaranteed by
    the Anderson Island Assistant or Poster Software, LLC.<br/>
<script>
function ShowHide(x) {
	var y = document.getElementById("D" + x).innerHTML;
   var d = document.getElementById(x).style.display;
   if(d == "none") {
      document.getElementById(x).style.display = "block";
	  document.getElementById("D" + x).innerHTML = y.substr(0, y.length-5) + '&#9650;';
   } else {
      document.getElementById(x).style.display = "none";
	  document.getElementById("D" + x).innerHTML = y.substr(0, y.length-5) + '&#9660;';
   }
}
END;

    date_default_timezone_set("America/Los_Angeles"); // set PDT
    if($nolog==0) echo $ed; // debug

    // write to services.html
    $fh = fopen($servicetablefile, 'w');
    fwrite($fh, $hd);
    fwrite($fh, $t);
    fwrite($fh, $ed);
	fwrite($fh, $clist);
	fwrite($fh, "</script>\n");
	fwrite($fh, "Updated " . date("m/d/Y h:i"));
    fclose($fh);

    // add to log file
    $fhl = fopen("../private/servicesignuplog.log", 'a');
    fwrite($fhl, date("Y/m/d H:i:s") . "|Regenerated services.html\n");
    fclose($fhl);

    ////////////////////////////////////////////////////////////////////////////////
    //  FPhone - format phone number and return the formatted string (nnn) nnn-nnnn
    function FPhone($pn) {
        if($pn==null) return "";
        if($pn=="") return "";
        return "<a href='tel:$pn'>(" . substr($pn, 0,3) . ") " . substr($pn, 3, 3) . "-" . substr($pn, 6). "</a>";
    }
?>
