<?php

require_once '../sm-db.php';


// Schedule this file at every 1/2 hour 


echo "======================================START========================================================<br><br>";

$QueryRun = mysql_query("SELECT *
                        FROM admin_statistics 
                        WHERE `type` = 'free_tools_service'");

while($dataQueryRun = mysql_fetch_array($QueryRun)){

    $brand = $dataQueryRun['brand'];
    $count= $dataQueryRun['metric'];
    $sendSms = $dataQueryRun['send_sms'];

    switch($brand){
        case 'sv':
            $msg = "SUPERVIRAL";
            break;
        case 'to':
            $msg = "TIKOID";
            break;    
        case 'fb':
            $msg = "FEEDBUZZ";
            break;
        case 'tp':
            $msg = "TOPKPOP";
            break;
        case 'sz':
            $msg = "SWIZZY";
            break;    

    }

    if($count > 30 && $sendSms == 0){
    
        //TEXT ME
            include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/messagebird/autoload.php'; 
    
            $MessageBird = new \MessageBird\Client($messagebirdclient);
            $Message = new \MessageBird\Objects\Message();
            $Message->originator = +447451272012;
            $Message->recipients = array($rfcontactnumber);
            $Message->body = $msg.' EXCESS free tools attempts: '.$dataQueryRun['metric'].'. ReCaptcha is on.';
    
            $MessageBird->messages->create($Message);
    
            $UpdateQueryRun = mysql_query("UPDATE admin_statistics 
                                                        SET `send_sms` = 1
                                                        WHERE `type` = 'free_tools_service' 
                                                        AND brand = '$brand'
                                                        limit 1");

            echo 'Message Sent!! <br><br>';
    }else{
       
       $UpdateQueryRun = mysql_query("UPDATE admin_statistics 
        SET `metric` = 0
        WHERE `type` = 'free_tools_service' 
        AND brand = '$brand'
        limit 1");
    
        if($UpdateQueryRun){
    
        echo 'Reset Metric Successfully <br><br>';
    
        }
    }
    
}



echo "======================================END========================================================<br><br>";
