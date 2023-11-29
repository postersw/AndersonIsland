<?php
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  copypictfiles - copy picture files from A day to B holiday
//  copies all files <XDL><AS><a><hhmm>.<jpg|txt>  to <XDL><AS><b><hhmm>.<jpg|txt>
//  Changes <a> to <b> and does the copy.  e.g. XA71230.jpg -> XAC1230.jpg
//  i.e. it changes the day 1-7 to a holiday letter
//
// calls: copypictfiles.php?a=<day>&b=<holiday letter>, e.g. a=7&b=C
// operates in the Overflow subdirectory
//
//  rfb. 11/28/23. Used to copy the initial picture files to THanksgiving S,T,U days.
//

chdir("/home/postersw/public_html/Overflow");
$files = scandir("/home/postersw/public_html/Overflow");
$nf = count($files); // number of files
echo "$nf files in Overflow.<br>";
$a = $_GET['a'];
$b = $_GET['b'];
echo "CopyFiles: a=$a, b=$b<br>";
if(strlen($a) != 1) die("invalid a parameter, should be a=7&b=x");
if(strlen($b) != 1) die("invalid b parameter, should be a=7&b=x");

$n = 0;
foreach($files as $f){
    if(preg_match("/[XDL][AS]$a/", $f)) {
        $d = substr($f, 0, 2) . $b . substr($f, 3);
        copy($f, $d);
        $n++;
        echo "$n Copied $f to $d<br>";
    }
}
echo "Copied $n files.";
exit(0);


?>