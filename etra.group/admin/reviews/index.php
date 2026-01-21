<?php


$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


if(!empty($_POST['submit'])){

	$name = addslashes($_POST['name']);
	$country = addslashes($_POST['country']);
	$type = addslashes($_POST['type']);
	$brand = addslashes($_POST['company']);
	$subject = addslashes($_POST['subject']);
	$review = addslashes($_POST['review']);
	$time = time();$time = rand($time - '604800',$time);


if(empty($failed)){

$submitted = '<div style="background-color:#ccc;padding:10px;">


Name: '.$name.'<br>
Subject: '.$subject.'<br>
Name: '.$review.'<br>

</div>';

$stars = '5';

	$insertq = mysql_query("INSERT INTO `reviews`
		SET 
		`country` = '$country', 
		`type` = '$type', 
		`name` = '$name', 
		`title` = '$subject', 
		`review` = '$review', 
		`timeo` = '$time', 
		`location` = '$city', 
		`approved` = '1',
        brand = '$brand'
		");


	if($insertq)$reviewmessage = '<div class="emailsuccess">Your review has been submitted. Thank you!</div>';

	$tpl = str_replace('<option value="'.$type.'">','<option value="'.$type.'" selected="selected">',$tpl);
	$tpl = str_replace('<option value="'.$country.'">','<option value="'.$country.'" selected="selected">',$tpl);


}

}

$tpl = str_replace('{reviewmessage}',$reviewmessage,$tpl);
$tpl = str_replace('{submitted}',$submitted,$tpl);


output($tpl, $options);
