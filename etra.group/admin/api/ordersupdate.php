<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$orderid = addslashes($_POST['id']);
$orderfulfillid = addslashes($_POST['orderid']);
$update = addslashes($_POST['update']);
$now = time();
$type = addslashes($_POST['pagefrom']);
$defectpagefrom = addslashes($_POST['defectpage']);

if (!empty($defectpagefrom)) {
	$defectpagefrom = 'defect';
}


if (empty($type)) $type = 'defect';

//JUST SAVE, DONT SET IT AS NO DEFECT
if ($update == 'save') {
	if (empty($orderfulfillid)) die('No order number');
	if (!preg_match('/^[0-9 ]*$/', $orderfulfillid)) die('Not proper number');
	$updateorder = mysql_query("UPDATE `orders` SET `fulfill_id` = '$orderfulfillid',`defect` = '0' WHERE `id` = '$orderid' LIMIT 1");
}

//IGNORE THIS ORDER IS DEFECTIVE PERMANENTLY, WAIT UNTIL USER CONTACTS US
if ($update == 'ignore') {
	$updateorder = mysql_query("UPDATE `orders` SET `defect` = '5',`fulfilled` = '$now',`norefill` = '1' WHERE `id` = '$orderid' LIMIT 1");
}

echo $update . '<br>';

if ($updateorder) {

	switch ($type) {
		case "missing":
			$page = 'missing-orders';
			break;
		case "reported":
			$page = 'reports';
			break;
	}


	if ($update == 'ignore') {
		// header('Location: /admin/' . $page . '/?type=' . $type . '&message=updatetrue&theid=' . $orderid);
		echo '<script>
			window.location.href= "/admin/' . $page . '/?type=' . $type . '&message=updatetrue&theid=' . $orderid .'"
			</script>';
	} else {
		// echo $type;
		echo '<script>
			window.location.href= "/admin/' . $type . '-orders/?type=' . $type . '&message=updatetrue&theid=' . $orderid .'"
			</script>';
		// header('Location: /admin/' . $type . '-orders/?type=' . $type . '&message=updatetrue&theid=' . $orderid);
	}
} else {
	die('Failed to update order, tell Rabban');
}
