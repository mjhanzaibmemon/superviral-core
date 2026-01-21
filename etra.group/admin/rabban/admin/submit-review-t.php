<?php





include('adminheader.php');


$review = addslashes($_POST['review']);
$review = $_POST['review'];

if(!empty($review)){

		$ids = array(

		'1' => '6183',
		'2' => '6182'


		);

		$chooseid = rand(1, 2);


		echo $chooseid;

		include('../orderfulfillraw.php');

		$orderid = $api->order(array('service' => $ids[$chooseid], 'link' => 'https://www.trustpilot.com/review/superviral.io', 'comments' => '$review'));



}

?>
<form method="POST">
	

	<textarea name="review" style="height:300px;width:100%;"></textarea><br>
	<input type="submit" name="submit" value="Submit" style="padding:10px;width:100%">


</form>