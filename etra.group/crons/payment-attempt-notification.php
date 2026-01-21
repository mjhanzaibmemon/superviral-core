<?php

require_once '../sm-db.php';


// Schedule this file at every 1/2 hour 


echo "======================================START========================================================<br><br>";

$QueryRun = mysql_query("SELECT *
                        FROM admin_statistics 
                        WHERE `type` = 'payment_attempts_per_day'");

while($dataQueryRun = mysql_fetch_array($QueryRun)){

    $count= $dataQueryRun['metric'];
    $sendSms = $dataQueryRun['send_sms'];


    if($count > 0){
    
        //TEXT ME
            include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/messagebird/autoload.php'; 
    
            $MessageBird = new \MessageBird\Client($messagebirdclient);
            $Message = new \MessageBird\Objects\Message();
            $Message->originator = +447451272012;
            $Message->recipients = array($rfcontactnumber);
            $Message->body = $msg.' Excess payment attempts in last 24hr. Logs: https://etra.group/admin/payment-logs/ . Reset: https://etra.group/admin/reset-payment-attempt/';
    
            $MessageBird->messages->create($Message);
    
            echo 'Message Sent!! <br><br>';
    }
}



echo "======================================END========================================================<br><br>";
