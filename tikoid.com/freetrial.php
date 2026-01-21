<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');

$id = addslashes($_GET['id']);
$hash = addslashes($_GET['id']);
$username = addslashes($_POST['username']);
$username = str_replace('@','',$username);
$contactnumber = addslashes($_POST['input']);



$tpl = file_get_contents('freetrial.html');

//CHECK IF ID AND SESSION EXISTS IN DATABASE
if(!empty($id)){

$validq = mysql_query("SELECT * FROM `freetrial` WHERE `md5` = '$id' AND `brand` = 'to' LIMIT 1");

if(mysql_num_rows($validq)=='0'){


        mysql_select_db ($dbName , $conn);

		$validq = mysql_query("SELECT * FROM `freetrial` WHERE `md5` = '$id' LIMIT 1");

		if(mysql_num_rows($validq)=='1'){

			$getsuperviraldbinfo = mysql_fetch_array($validq);

        //INSERT TO TIKOID DB
        $insertq = mysql_query("INSERT INTO `freetrial` SET 
		    `brand` = 'to',
            `md5` = '{$id}',
            `emailaddress`='{$getsuperviraldbinfo['emailaddress']}',
            `type`='1'
            ");

         echo ' <meta http-equiv="refresh" content="0">';

        die;

        }


      


}



if(mysql_num_rows($validq)=='0'){die('Invalid Session');}






mysql_query("UPDATE `freetrial` SET `views` = `views` + 1 WHERE `brand` = 'to' AND `md5` = '$id' LIMIT 1");


}else{


die('Try clicking on the link from the email again.');

}


$info = mysql_fetch_array($validq);


//IF SUBMITTED, SUBMIT AND FULFILL
if((!empty($username))&&($info['done']=='0')){

		//PREVENT DUPLICATE INSERTS
		$updatefulfill = mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `md5` = '$id' ORDER BY `id` DESC LIMIT 1");


		//POST VARIABLES
		$id = addslashes($_POST['id']);
		$hash = addslashes($_POST['hash']);

		//EMULATE ORDERFULFILL
		$info['packageid'] = '124';
		$info['igusername'] = $username;


		$loc2 = $loc;
		if(empty($loc2))$loc2=$info['country'];
		if(!empty($loc2))$loc2 = $loc2.'.';
		if($loc2=='ww.')$loc2 = '';

		$cta = 'https://tikoid.com/track-my-order/'.$id;

		include('emailfulfill2.php');



		if(!empty($contactnumber)){


		if(substr($contactnumber, 0, 2 ) == "07")$contactnumber = preg_replace('/^(0*44|(?!\+0*44)0*)/', '+44', $contactnumber);


			$contactnumberupdate = ", `contactnumber` = '$contactnumber' ";

		}

		if(!empty($contactnumber)){$askednumber = " `askednumber` = '2', ";}



		$added = time();
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];

		$insertfulfill = mysql_query("INSERT INTO `orders` SET 
			`brand` = 'to',
			`socialmedia` = 'tt',
			`packagetype` = 'freefollowers',
			`packageid` = '124',
			`country` = 'us',
			`order_session` = '$id',
			`amount` = '30',
			`added` = '$added',
			`price` = '0.00',
			`emailaddress` = '{$info['emailaddress']}',
			$askednumber
			`next_fulfill_attempt` = '$added',
			`contactnumber` = '$contactnumber',
			`ipaddress` = '$ipaddress',
			`igusername` = '$username'");

		$uniqueorderinsertid = mysql_insert_id();

		$updatefulfill = mysql_query("UPDATE `freetrial` SET `done` = '1',`orderid`='$uniqueorderinsertid',`username` = '$username' $contactnumberupdate  WHERE `md5` = '$id' LIMIT 1");

        mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `emailaddress` = '{$info['emailaddress']}'");

		$updateuser = mysql_query("UPDATE `users` SET `freetrialclaimed` = '1' $contactnumberupdate WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1");

		include('orderfulfill.php');

		$info['done'] = 1;

		

}


if(empty($error))$error = '<div class="label labelcontact">Enter your TikTok username and that\'s it!</div>';











if($info['done'] == 1){

header('Location: /buy-tiktok-followers/?freefollowers='.$id);

}



$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{error}', $error, $tpl);
$tpl = str_replace('{id}', $id, $tpl);
$tpl = str_replace('{hash}', $hash, $tpl);
$tpl = str_replace('{reviewnumrows}', $reviewnumrows, $tpl);
$tpl = str_replace('{reviewmessage}', $reviewmessage, $tpl);
$tpl = str_replace('{reviews}', $reviews, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{igusername}', $info['username'], $tpl);



echo $tpl;
?>