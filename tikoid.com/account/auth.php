<?php


// check if cookies set or not
if(isset($_COOKIE['plus_id']) && isset($_COOKIE['plus_token'])) {
	
	$plus_id = $_COOKIE['plus_id'];
	$plus_token = $_COOKIE['plus_token'];


	// get logged in user with plus_id and plus_token cookie values
	$result = mysql_query("SELECT * FROM `accounts` WHERE `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' AND `brand` = 'to' LIMIT 1");
	$userinfo = mysql_fetch_array($result);
	$num_rows = mysql_num_rows($result);
    if($num_rows < 1){//no match found, the combination of the email hash and token hash isn't found


		header("location: /login/");
	    exit;
    

    }

}else{

// 	if(

// 		(!empty($_POST['cres']))&&(!empty($_POST['threeDSSessionData']))&&($alcheckout==1)||
// 		((!empty($_POST['PaRes']))&&($alcheckout==1))

// 	){//this is from auto likes session

// 		$onetimetokenpost = addslashes($_GET['onetimetoken']);
// 		$alcheckoutid22 = addslashes($_GET['id']);
// 		// get logged in user with plus_id and plus_token cookie values

// 		$searchontimetokenq = mysql_query("SELECT * FROM `automatic_likes_session` WHERE `payment_onetime_token` = '$onetimetokenpost' AND `order_session` = '$alcheckoutid22' AND `payment_onetime_token_active` = '1' AND `brand` = 'to' LIMIT 1");

// 			if(mysql_num_rows($searchontimetokenq ) == 0){//header("location: /account/checkout/".$alcheckoutid22.'?error=invalidonetimetoken');

// 			echo 'Invalid';

// 			exit;
// 		}

// 		$searchontimetokeninfo = mysql_fetch_array($searchontimetokenq);




// 		$result = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$searchontimetokeninfo['account_id']}' AND `brand` = 'to' LIMIT 1");
// 		$userinfo = mysql_fetch_array($result);
// 		$num_rows = mysql_num_rows($result);
// 	    if($num_rows !== 1){//no match found, the combination of the email hash and token hash isn't found

// 	    /*echo 'ASD2 not found';*/
// 			header("location: /login/");
// 		    die;
	    

// 	    }

// 	}else{

// /*		print_r($_POST);

	    echo 'ASD not found';
			header("location: /login/");
		    die;
    
//     }
}

?>
