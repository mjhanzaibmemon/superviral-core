<?php
	
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; // SQS function
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/common.php'; // common confguration


$res = retriveQueue($query_queueUrl);

if(!empty($res)){
    $query = $res[0]['body'];
// echo $query;die;
if(!empty($query)){
    
    $run = mysql_query($query);
    if($run){
        echo 'Done Successfully'. $query . '<br>';
    }else{
        echo 'Error'. $query . '<br>';
    }

}
}else{
    echo 'No data';
}
