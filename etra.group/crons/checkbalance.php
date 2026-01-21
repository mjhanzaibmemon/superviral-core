<?php

include('../sm-db.php');


class Api
{
    
    public function setApiKey( $value ){$this->api_key = $value;}
    public function setApiUrl( $value ){$this->api_url = $value;}



    public function balance() { // get balance
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'balance',
        )));
    }


    private function connect($post) {
        $_post = Array();
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name.'='.urlencode($value);
            }
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (is_array($post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        curl_close($ch);
        return $result;
    }
}

// Examples

$api = new Api();

$api->setApiKey($fulfillment_api_key);
$api->setApiUrl($fulfillment_url);


$balance = $api->balance();

$checkthis = $balance->balance;


if($checkthis < $notifbelow){


echo 'BELOW '.$notifbelow.'<br>'.$checkthis.'<hr>';

        //TEXT ME
        require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/messagebird/autoload.php';


        $MessageBird = new \MessageBird\Client($messagebirdclient);
        $Message = new \MessageBird\Objects\Message();
        $Message->originator = 'SUPERVIRAL';
        $Message->recipients = array($rfcontactnumber);
        $Message->body = 'Needs top up. Balance is currently $'.round($checkthis).': '.$fulfillmentsite.'/addfunds';

        $MessageBird->messages->create($Message);

}

else{


echo 'ABOVE '.$notifbelow.'<br>'.$checkthis.'<hr>';


}





?>