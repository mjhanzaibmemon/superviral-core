<?php


include('../sm-db.php');

//AUTHORISED IP ADDRESSES
$ips = array(
	"198.27.83.222", 
	"192.99.21.124", 
	"167.114.64.88", 
	"167.114.64.21",
	"2607:5300:60:24de::",
	"2607:5300:60:467c::",
	"2607:5300:60:6558::",
	"2607:5300:60:6515::",
	"212.159.178.222"
	);

//KILL IF THIS USER ISNT AN AUROTHISED IP
if (!in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $ips)) {die("NO ACCESS");}




$team = array(
	'monday' => 'abu',
	'tuesday' => 'abu',
	'wednesday' => 'abu',
	'thursday' => 'abu',
	'friday' => 'naeem',
	'saturday' => 'naeem',
	'sunday' => 'naeem'
	);




$coverup = array();

$coverup['abu'] = array(
	'number' => $abcontactnumber,
	'alt' => 'naeem',
	'altnumber' => $nacontactnumber
	);



$coverup['naeem'] = array(
	'number' => $nacontactnumber,
	'alt' => 'abu',
	'altnumber' => $abcontactnumber
	);




$thedaytoday = strtolower(date('l')); //THE DAY TODAY IS E.g. Tuesday

if($thedaytoday=='friday')die('Day Off');

$activerep = $team[$thedaytoday];


$todaystart = strtotime("today", time());


$deadline = $todaystart + 68400;


//$q = mysql_query("SELECT * FROM `admin_notifications` WHERE `admin_name` = '$activerep' AND `added` BETWEEN '$todaystart' AND '$deadline'");
$q = mysql_query("SELECT * FROM `email_support_replies` WHERE `dateAdded` BETWEEN '$todaystart' AND '$deadline'");


$reportamount = mysql_num_rows($q);

if($reportamount!==0){die('Reports done by '.$activerep);}else{$notdonefortoday = 1;}

if($notdonefortoday==1){//BOTH CHECKS DONE, pass it over now to the alternate customer service rep! and prevent access to todays active rep!


//TEXT ME
include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/messagebird/autoload.php';  


echo 'NOT DONE';



$dateofabsence = date('d-m-Y',time());
$now = time();

//INSERT INTO TABLE that the active rep has missed today and insert it with the formated date and NOT unix
mysql_query("INSERT INTO `staff_absence`

	SET 
	`staff` = '$activerep', 
	`date_of_absence` = '$dateofabsence', 
	`added` = '$now',
	`type` = 'missed'

	");


//SEND OUT TEXT MESSAGE to active rep, saying they've been deducted

$to = $coverup[$activerep]['number'];
$messagethis = 'Support deadline missed: Please stop support as wage deducted for today & resume your next working day.';


$MessageBird = new \MessageBird\Client($messagebirdclient);
$Message = new \MessageBird\Objects\Message();
$Message->originator = 'SUPERVIRAL';
$Message->recipients = array($to);
$Message->body = $messagethis;

$MessageBird->messages->create($Message);




//SEND OUT TEXT MESSAGE to alternate - tell them to also copy and paste the text to me if they take the offer up

$to = $coverup[$activerep]['altnumber'];
$messagethis = 'DOUBLE pay opportunity: cover support for '.$activerep.'. Copy & paste this txt and send to Rabban to accept. Thanks!';


$MessageBird = new \MessageBird\Client($messagebirdclient);
$Message = new \MessageBird\Objects\Message();
$Message->originator = 'SUPERVIRAL';
$Message->recipients = array($to);
$Message->body = $messagethis;

$MessageBird->messages->create($Message);



//SEND OUT TEXT MESSAGE TO LOOK OUT for alternate response


$to = '+447872545933';
$messagethis = 'No reports done';



$MessageBird = new \MessageBird\Client($messagebirdclient);
$Message = new \MessageBird\Objects\Message();
$Message->originator = 'SUPERVIRAL';
$Message->recipients = array($to);
$Message->body = $messagethis;

$MessageBird->messages->create($Message);

}

?>