<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('../header.php');


// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}


$id = addslashes($_GET['id']);
$hash = addslashes($_GET['id']);
$username = addslashes($_POST['username']);
$username = str_replace('@','',$username);
$contactnumber = addslashes($_POST['input']);



$tpl = file_get_contents('tt-freetrial.html');

//CHECK IF ID AND SESSION EXISTS IN DATABASE
if(!empty($id)){

$validq = mysql_query("SELECT * FROM `freetrial` WHERE `md5` = '$id' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");

if(mysql_num_rows($validq)=='0'){die('Invalid Session');}


$info = mysql_fetch_array($validq);

mysql_query("UPDATE `freetrial` SET `views` = `views` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");


}else{


die('Try clicking on the link from the email again.');

}





//IF SUBMITTED, SUBMIT AND FULFILL
if((!empty($username))&&($info['done']=='0')){

		//PREVENT DUPLICATE INSERTS
		$updatefulfill = mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `id` = '{$info['id']}' ORDER BY `id` DESC LIMIT 1");


		//POST VARIABLES
		$id = addslashes($_POST['id']);
		$hash = addslashes($_POST['hash']);

		$checkq = mysql_query("SELECT * FROM `packages` WHERE `type` = 'freetrial' AND socialmedia = 'tt' LIMIT 1");
        $packages = mysql_fetch_array($checkq);
        $pid = $packages['id'];
		//EMULATE ORDERFULFILL
		$info['packageid'] = $pid;
		$info['igusername'] = $username;


		$cta = 'https://superviral.io/'.$loclinkforward.'track-my-order/'.$id;

		include('../emailfulfill2.php');



		if(!empty($contactnumber)){


		if(substr($contactnumber, 0, 2 ) == "07")$contactnumber = preg_replace('/^(0*44|(?!\+0*44)0*)/', '+44', $contactnumber);


			$contactnumberupdate = ", `contactnumber` = '$contactnumber' ";

		}

		if(!empty($contactnumber)){$askednumber = " `askednumber` = '2', ";}



		$added = time();
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];

		$insertfulfill = mysql_query("INSERT INTO `orders` SET 
			`brand` = 'sv',
			`packagetype` = 'freefollowers',
			`packageid` = '$pid',
			`country` = '{$locas[$loc]['sdb']}',
			`order_session` = '$id',
			`amount` = '30',
			`added` = '$added',
			`price` = '0.00',
			`emailaddress` = '{$info['emailaddress']}',
			$askednumber
			`next_fulfill_attempt` = '$added',
			`contactnumber` = '$contactnumber',
			`ipaddress` = '$ipaddress',
			`socialmedia` = 'tt',
			`igusername` = '$username'");

		$uniqueorderinsertid = mysql_insert_id();

		$updatefulfill = mysql_query("UPDATE `freetrial` SET `done` = '1',`orderid`='$uniqueorderinsertid',`username` = '$username' $contactnumberupdate  WHERE `md5` = '$id' AND `brand`='sv' LIMIT 1");

        mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand`='sv' LIMIT 1");

		$updateuser = mysql_query("UPDATE `users` SET `freetrialclaimed` = '1' $contactnumberupdate WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand`='sv' LIMIT 1");

		//include('orderfulfill.php');

		$info['done'] = 1;

		

}


if(empty($error))$error = '<div class="label labelcontact">Enter your Tiktok username and that\'s it!</div>';











if($info['done'] == 1){

	header('Location: /'.$loclinkforward.'free-package/processing/?freefollowers='.$id);
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
$tpl = str_replace('{contentlanguage}', $locas[$loc]['contentlanguage'], $tpl);
$tpl = str_replace('{ordersession}', $hash, $tpl);
$tpl = str_replace('{ordersession_id}', $info['id'], $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND `country` = '{$locas[$loc]['sdb']}'");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}
if($cinfo['name']=='canonical'){$htmlcanonical = $cinfo['content'];}





echo $tpl;
?>