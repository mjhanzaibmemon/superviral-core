<?php

include('../sm-db.php');

$now = time();
$twominsago = time() - (60 * 3);

$q = mysql_query("SELECT * FROM `orders` WHERE 
	`id` > '73663' AND 
	`fulfill_id` = '' AND 
	`fulfill_attempt` != '0' AND 
	`defect` = '0' AND 
	`refund` = '0' AND 
	`disputed` = '0' AND
	`added` NOT BETWEEN '$twominsago' AND '$now'
	ORDER BY `packagetype` ASC LIMIT 100");
	
$count = mysql_num_rows($q);

while($info = mysql_fetch_array($q)){

$orderids .= $info['id'].' ';

}


if($count!==0){


		//TEXT ME
		include_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/messagebird/autoload.php';  

		$MessageBird = new \MessageBird\Client($messagebirdclient);
		$Message = new \MessageBird\Objects\Message();
		$Message->originator = 'SUPERVIRAL';
		$Message->recipients = array($hacontactnumber);
		$Message->body = $count.' orders need to be fulfilled. Tell Rabban to check logs on EasyCron';

		$MessageBird->messages->create($Message);

		echo 'Needs looking at right now: '.$count.' orders not fulfilled. Order IDs: '.$orderids;

}else{


	echo 'All done for today!';


}

?>