<?php

error_reporting(E_ERROR | E_PARSE);

require_once __DIR__. '/vendor/autoload.php';





Eden::DECORATOR;



// 'anujjaiswalcse0020@gmail.com', 

//     'AnujFaltu&00', 

$imap = eden('mail')->imap(

    'imap.gmail.com', 

    'anuj@etra.group', 

    'gzssinkzdcknuemr', 

    993, 

    true);

    $mailboxes = $imap->getMailboxes(); 

    $imap->setActiveMailbox('INBOX')->getActiveMailbox();

    

    // $emails = $imap->getEmails(0, 10); 

    

    $count = $imap->getEmailTotal(); 

    // echo $count;

    

    $email = $imap->getUniqueEmails(161, true); 

    

    // $MultipleEmails = $imap->getUniqueEmails(array(166, 165), true);

    // print_r($MultipleEmails);

    

    // $cleaner_input = strip_tags($email['body']['text/html']);



    echo $email['body']['text/html'] ;

    // echo '<pre>';

    // print_r($emails);   

    // $email = $imap->getUniqueEmails(165, true); 

    // echo $email['body']['text/html'];





    $imap->disconnect(); 

?>