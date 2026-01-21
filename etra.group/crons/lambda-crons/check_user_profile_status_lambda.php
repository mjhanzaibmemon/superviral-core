<?php

require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/sm-db.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/aws-sdk/aws-autoloader.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core.php'; // lambda function

$query = "SELECT `from` FROM `email_queue` WHERE markDone = '0' GROUP BY `from` ORDER BY id DESC LIMIT 10";
$queryRun = mysql_query($query);

$howmuchleft = mysql_num_rows($queryRun);

if ($howmuchleft == 0) die('Done');

echo '<h1>Email Queue Found: ' . mysql_num_rows($queryRun) . '</h1><hr>';

while ($data = mysql_fetch_array($queryRun)) {

    $orderQuery = "SELECT * FROM orders WHERE 
                                            emailaddress = '{$data['from']}' 
                                        AND 
                                            account_type IS NULL 
                                        GROUP BY 
                                            igusername 
                                        ORDER BY 
                                            id 
                                        DESC";

    $runOrderQuery = mysql_query($orderQuery);
    echo '<h1>Orders Found: ' . mysql_num_rows($runOrderQuery) . ' For '. $data['from'] .'</h1><hr>';
    while($orderData = mysql_fetch_array($runOrderQuery)){

        echo '<h2>' . $orderData['id'] . '. ' . $orderData['igusername'] . '</h2><br><br>';

        $timeStamp = time();
    
        $datatosend = [
            'key' => $superviralsocialscrapekey,
            'username' => $orderData['igusername'],
            'short_code' => '',
            'type' => 'is_private', // for what purpose requesting
            'user_id' => ''
        ];

        if(!empty($orderData['igusername'])){
            $callBack = connectToLambda($serv . '-socialmedia-api-lambda', $datatosend); // $serv coming from sm-db.php
        
            echo '<pre>';
            $response = json_decode($callBack['body']);
            $userId = $response->data->user->pk_id;
            $isprivate = $response->data->user->is_private;

            if (!empty($userId)) {
    
                if(empty($isprivate)){
                    $isprivate = 'Public';
                }else{
                    $isprivate = 'Private';
                }
            
                echo "UPDATE orders SET account_type = '$isprivate' WHERE emailaddress = '{$data['from']}' AND igusername = '{$orderData['igusername']}'";die;
                //mysql_query("UPDATE orders SET account_type = '$isprivate' WHERE emailaddress = '{$data['from']}' AND igusername = '{$orderData['igusername']}'");
        
                echo '<br><hr> done for '. $orderData['igusername'];
        
            } else {
        
                echo 'Unable to get User data (Code 1): Unavailable: 1 '.  $orderData['igusername'] .'<pre>';
    
                if($response->statusCode == 404){
                    echo "UPDATE orders SET account_type = 'User not found' WHERE emailaddress = '{$data['from']}' AND igusername = '{$orderData['igusername']}'";die;
                    //mysql_query("UPDATE orders SET account_type = 'User not found' WHERE emailaddress = '{$data['from']}' AND igusername = '{$orderData['igusername']}'");
                }
                print_r($response);
                echo '</pre><br>';
                continue;
            }
        }else{
            echo 'Empty username';echo '</pre><br>';
        }
        
        
    
      
    
        echo '<hr>';
    
        unset($response);

    }

   
}
echo 'done';
