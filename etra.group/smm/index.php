<?php

include_once('../sm-db.php');


$data2 = file_get_contents('php://input');


if(empty($data2))die('No data input');



if(strpos($data2, $masked_api_key) !== false){

    //REPLACE MASKED KEY WITH CORRECT KEY
    $data2 = str_replace($masked_api_key,$fulfillment_api_key,$data2);

} else{
    echo "Error 392: Not found";
}


$data3 = explode('&', $data2);


$_post = Array();

foreach($data3 as $value){


    $data4 = explode('=',$value);
    $thekey = $data4[0];
    $thevalue = $data4[1];




    $_post[] = $thekey.'='.urlencode($thevalue);




}





        $ch = curl_init($fulfillment_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        curl_close($ch);
        echo $result;
        






?>