<?php


header('Access-Control-Allow-Origin: https://' . $loc . 'superviral.io');
include('db.php');

switch ($_POST['page']) {
    case 'order_details':

        $username = strtolower(addslashes($_POST['username']));
        $emailaddress = strtolower(addslashes($_POST['emailaddress']));
        $checkalfreeq = mysql_query("SELECT * FROM `automatic_likes_free` WHERE `brand`='sv' AND (`igusername` LIKE '%{$username}%' OR `emailaddress` LIKE '%{$emailaddress}%') LIMIT 1");
        $num_rows = mysql_num_rows($checkalfreeq);

        if ($redis->exists('al_' .$emailaddress .'_'. $username .'_count')) {
			// $userData = json_decode($redis->get("or_igsearchbyusername_{$info['id']}"), true);
            echo json_encode(array(
                'status' => 'success',
                'num_rows' => 'alreadyset',
            ));
		} else {
            // reddis set
            
            $redis->setex('al_' .$emailaddress .'_'. $username .'_count' ,600, $num_rows);
            echo json_encode(array(
                'status' => 'success',
                'num_rows' => $num_rows,
            ));
        }

       
        die;
    break;

}