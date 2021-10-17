<?php
/////////////////////////////////////////////////////////////
//  getemailtest - test framework
//

    chdir("/home/postersw/public_html");  // move to web root

    $yourEmail = "alerts@anderson-island.org";
    $yourEmailPassword = "alerts";
    echo ($yourEmail);
    $mailbox = imap_open("{mail.anderson-island.org:993/imap/ssl/novalidate-cert}", $yourEmail, $yourEmailPassword) or die("can't connect: " . imap_last_error());
    $emailnumberarray = imap_search($mailbox, "ALL") or die("no mail returned");  // return array
    rsort($emailnumberarray); // put the newest emails first
    foreach($emailnumberarray as $emailnum) {
        $email_headers = imap_headerinfo($mailbox, $emailnum); // read the header
        $subject = $email_headers->subject;
        $from = $email_headers->fromaddress;
        echo ("emailnum=$emailnum, subject=$subject, from=$from, ");
        //$body = imap_fetchbody($mailbox, $emailnum, "2");
        $body = imap_body($mailbox, $emailnum);
        echo ("body= $body");
        break;
        //imap_setflag_full($mailbox, $mail[0], "\\Seen \\Flagged");
    }
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