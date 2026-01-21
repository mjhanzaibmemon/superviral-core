<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

if (!empty($_POST['refillSubmit'])) {

    $checkedValues = addslashes($_POST['checkedValues']);

    header('Location: https://5ejtppumu4.execute-api.us-east-2.amazonaws.com/refill-mass-aj?ids=' . $checkedValues );

    die;

}


$sql = "SELECT * FROM orders where norefill = 1 AND packagetype = 'followers' ORDER BY id DESC limit 30";
$runsql = mysql_query($sql);
$refillData = '';
if (mysql_num_rows($runsql) > 0) {
    $i = 0;
    while ($info = mysql_fetch_array($runsql)) {

        $fulfillsData = "";
        $fulfills = explode(' ', trim($info['fulfill_id']));

		foreach ($fulfills as $fulfillorder) {

			if (empty($fulfillorder)) continue;
			$fulfillsData .= '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $fulfillorder . '">' . $fulfillorder . '</a><br>';

			
		}

        $refillData .= '<tr>
                            <td>'. $fulfillsData .'</td>
                            <td>' . $info['amount'] . ' ' . $info['package'] . '</td>
                            <td>' . $info['emailaddress'] . '</td>
                            <td>' . $info['igusername'] . '</td>
                            <td>' . date('d/m/Y H:i:s ', $info['added']) . '</td></tr>';

        $values[] = $info['id'];
        $allvalues = implode(',', $values);
        $i++;
    }
} else {
    $refillData = 'All Caught Up!';
}

$sql = "SELECT * FROM orders where lastrefilled != '' ORDER BY lastrefilled DESC limit 30";
$runsql = mysql_query($sql);

$i = 0;
while ($info = mysql_fetch_array($runsql)) {

    $pastFulfillsData = "";
    $fulfills = explode(' ', trim($info['fulfill_id']));

    foreach ($fulfills as $fulfillorder) {

        if (empty($fulfillorder)) continue;
        $pastFulfillsData .= '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $fulfillorder . '">' . $fulfillorder . '</a><br>';

        
    }

    $pastRefillData .= '<tr>
                        <td>'. $pastFulfillsData .'</td>
                        <td>' . $info['amount'] . ' ' . $info['package'] . '</td>
                        <td>' . $info['emailaddress'] . '</td>
                        <td>' . $info['igusername'] . '</td>
                        <td>' . date('d/m/Y H:i:s ', $info['lastrefilled']) . '</td></tr>';

    $values2[] = $info['id'];
    $allvalues2 = implode(',', $values2);
    $i++;
}


$tpl = str_replace('{refillData}', $refillData, $tpl);
$tpl = str_replace('{pastRefillData}', $pastRefillData, $tpl);
$tpl = str_replace('{msg}', $msg, $tpl);
$tpl = str_replace('{allvalues}', $allvalues, $tpl);
$tpl = str_replace('{count}', mysql_num_rows($runsql) , $tpl);


output($tpl, $options);
