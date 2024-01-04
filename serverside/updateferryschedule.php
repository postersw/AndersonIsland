<?php
///////////////////////////////////////////////////////////////////////////////////
// updateferryschedule.php  -  updates ferryscheduleinclude.txt with the contents of the post
//
//  editferrytimes.html -> POST -> updateferryschedule.php
//  $_POST:  pw=password, newschedule=new value for file
//      All lines in 'newschedule' replace the contents of ferryscheduleinclude.txt.
//      The former contents of ferryscheduleinclude.txt is renamed ferryscheduleinclude_yymmddhhmmss.txt.
//  
//  RFB. 12/28/23

// here document for header
?>
<!DOCTYPE html>
<html>
<head>
	<title>Update ferryschedule</title>
	<meta charset="utf-8" />
</head>
<body>
    <div>

<?php
    //$filename = "testferryscheduleinclude.txt";  // test file name
    $filename = "ferryscheduleinclude.txt";  // file name
    echo "<h1>UPDATE $filename</h1>";
    date_default_timezone_set('America/Los_Angeles');
    chdir("/home/postersw/public_html");  // move to web root

    // check parameters
    $pw = $_POST["pw"];
    $checkpw = str_replace("\n", "", (file_get_contents("../ferryauth.php")));  // get password
    if($pw!="farkel") exit("Invalid password");
    $new = $_POST["newschedule"];
    $new = str_replace("\r", "", $new);  // remove all carriage returns which are windows only
    $lnew = strlen($new);
    if($lnew<100) exit("New string of $lnew bytes is too small");
    if(substr($new, 0, 27) != "// ferryscheduleinclude.txt") exit("invalid first line");

    // save existing file in old file
    $old = file_get_contents($filename);
    $lold = strlen($old);
    if($lold == 0) exit("could not read old value of $filename");
    if(abs($lold-$lnew) > (0.25*$lold)) exit("ERROR: old file is $lold bytes but new one is $lnew which is more than 25 pct different.");
    $ds = date("ymdHis");
    $dt = date("m/d/y H:i:s");
    $oldfilename = $filename . "_" . "$ds.txt";
    file_put_contents($oldfilename, $old);
    echo "Saved former file as $oldfilename, $lold bytes.<br>";

    // display new value
    $new = $new . "//Edited $dt\n";
    $lnew = strlen($new);
    file_put_contents($filename, $new);
    echo "<br>Wrote $lnew bytes to $filename<br>";
    echo "$dt: NEW VALUE:<br>";
    $new = str_replace("\n", "<br>", $new); // add line breaks
    echo $new;
    echo "<br>-----------------------------------------------------------------------<br>";
    $new = str_replace(";0", "<br>0", $new); // add line breaks
    $new = str_replace(";1", "<br>1", $new); // add line breaks
    $new = str_replace(";2", "<br>2", $new); // add line breaks
    $new = str_replace(";3", "<br>3", $new); // add line breaks
    $new = str_replace(";4", "<br>4", $new); // add line breaks
    $new = str_replace(";5", "<br>5", $new); // add line breaks
    $new = str_replace(";6", "<br>6", $new); // add line breaks
    $new = str_replace(";7", "<br>5", $new); // add line breaks
    $new = str_replace(";8", "<br>8", $new); // add line breaks
    $new = str_replace(";9", "<br>9", $new); // add line breaks
    $new = str_replace(";", ";<br>----", $new); // add line breaks
    echo $new;
    echo "<br><br>";

    echo "</body></html>"
?>