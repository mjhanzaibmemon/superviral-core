<?php



include('../sm-db.php');

$q = mysql_query("SELECT * FROM `email_queue` WHERE `from` = 'complaints@email-abuse.amazonses.com' AND `markDone` = '0'  ORDER BY `id` DESC LIMIT 100");

if (mysql_num_rows($q) == '0') die('ALL DONE!');

while ($info = mysql_fetch_array($q)) {

    $brand = $info['brand'];

    $thisemail = $info['email'];

    $thisemail = explode('This is the list of the complaint emails:', $thisemail);

    $theactualemailaddress = trim($thisemail[1]);

    echo 'Complaint: ' . $info['id'] . ': <b>' . $theactualemailaddress . '</b> - ' . $info['to'];


    if (empty($theactualemailaddress)) {
        echo 'Subject:' . $info['subject'] . '<br>Email: ' . $info['email'] . '';
    }


    $updateq = mysql_query("SELECT `id`,`emailaddress` FROM `users` WHERE `emailaddress` = '$theactualemailaddress' and brand = '$brand' LIMIT 1");

    //if((mysql_num_rows($updateq)=='1')&&(!empty($theactualemailaddress))){
    if (mysql_num_rows($updateq) == '1') {


        echo ' - Found a match!';

        $updateinfo = mysql_fetch_array($updateq);

        $updateduser = mysql_query("UPDATE `users` SET `unsubscribe` = '1' WHERE `id` = '{$updateinfo['id']}' and brand = '$brand' LIMIT 1");

        if ($updateduser) echo ' - <font color="green">Updated user ' . $updateinfo['id'] . '!</font>';

        $updatecomplaint = mysql_query("UPDATE `email_queue` SET `markDone` = '1' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");

        unset($theactualemailaddress);
        unset($thisemail);
    }

    echo '<hr>';
}
