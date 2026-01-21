<?php
include  'db.php';
// echo $_GET["id"];die;
if($_GET["id"] != ""){

	$id = base64_decode($_GET['id']);
    $password = $_GET['key'];
	$result = mysql_query("SELECT * FROM `accounts` WHERE `id` ='$id' LIMIT 1");
    $info = mysql_fetch_array($result);
    $password_hashed = $info['password'];
	$num_rows = mysql_num_rows($result);

    if(mysql_num_rows($result) > 0 && $password == $password_hashed){

          // set cookies for 30 days and login
          $cookie_name1 = 'plus_id';
          $cookie_value1= $info['email_hash'];
          $cookie_name2 = 'plus_token';
          $cookie_value2 = $info['token_hash'];
  
          // set plus_id cookie
          setcookie($cookie_name1, $cookie_value1, time() + (86400 * 365), "/",".superviral.io"); // 86400 = 1 day
          // set plus_token cookie
          setcookie($cookie_name2, $cookie_value2, time() + (86400 * 365), "/",".superviral.io"); // 86400 = 1 day
  
          header("location: https://superviral.io/account/");
    }else{
        echo 'not found';
    }
      
}



?>