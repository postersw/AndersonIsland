<?php
/////////////////////////////////////////////////////////////
//  getemailtest - test framework
//

    chdir("/home/postersw/public_html");  // move to web root
    date_default_timezone_set("America/Los_Angeles"); // set PDT
    $yourEmail = "alerts@anderson-island.org";
    $yourEmailPassword = "alerts";
    echo ($yourEmail);

    // read mailbox
    $mailbox = imap_open("{mail.anderson-island.org:993/imap/ssl/novalidate-cert}", $yourEmail, $yourEmailPassword) or die("can't connect: " . imap_last_error());
    $emailnumberarray = imap_search($mailbox, "ALL") or die("no mail returned");  // return array
    rsort($emailnumberarray); // put the newest emails first
    $emailnum = max($emailnumberarray);  // get the highest email
    echo ("highest emailnum=$emailnum \n");
    // read the newest email
    //foreach($emailnumberarray as $emailnum) {
        $email_headers = imap_headerinfo($mailbox, $emailnum); // read the header
        $subject = $email_headers->subject;
        $from = trim($email_headers->fromaddress);
        $date = $email_headers->date;
        echo ("emailnum=$emailnum, subject=$subject, from=$from, date=$date\n ");
        $talert=strtotime($date); // covert to timestamp
        echo (" converted date=". date("m/d/y H:i:s", $talert) . "|");
        if($from!='"listserv@civicplus.com" <listserv@civicplus.com>') Bailout("first email is from $from, not listserv@civicplus.com <listserv@civicplus.com>");
        //$body = imap_fetchbody($mailbox, $emailnum, "2");
        $body = imap_body($mailbox, $emailnum);
        $body = imap_qprint($body);  // decode quoted printables like =
        echo ("body= $body");
        $i = stripos($body, "https://www.piercecountywa.gov/");
        $iend = stripos($body, "\r", $i);  // find end of link
        //echo (" link found at position $i to $iend ");
        //echo (" at $iend, char=" . substr($body, $iend, 1) . " code=" . ord(substr($body, $iend, 1)) ); 
        $link = substr($body, $i, $iend-$i); // link
        echo ("link=$link|");
        //break;
        //imap_setflag_full($mailbox, $mail[0], "\\Seen \\Flagged");
    //}
    imap_close($mailbox);
    exit(0);

    ////////////////////////////////////////////////////////////
    // Bailout - send error message and delete file and exit
    function Bailout($s) {
        echo "Error: " . $s;
        //unlink($burnbanfile);
        exit;
    }




?>