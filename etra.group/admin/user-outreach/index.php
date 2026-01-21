<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

include  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


if (!empty($_POST['submit'])) {

	$id = addslashes($_POST['id']);

	$updateQ = mysql_query("UPDATE user_outreach SET done = 1 WHERE id = $id");


	if ($updateQ) $reviewmessage = '<div class="emailsuccess">Marked done for id: ' . $id . '</div>';
}

$query = "SELECT * from user_outreach WHERE done = 0 AND brand = '$brand'";
$queryRun = mysql_query($query);
$rowData = '';
while ($data = mysql_fetch_array($queryRun)) {

	$rowSnippet = tpl_get('row');
	$rowSnippet = str_replace('{username}', $data['username'],$rowSnippet);
	$rowSnippet = str_replace('{id}', $data['id'],$rowSnippet);
	$rowSnippet = str_replace('{lst_order_id}', $data['last_order'],$rowSnippet);
	$rowSnippet = str_replace('{email}', $data['email'],$rowSnippet);
	$rowSnippet = str_replace('{followers}', formatNumber($data['followers']),$rowSnippet);
	$rowSnippet = str_replace('{brand}', ($data['brand'] == 'sv' ? 'Superviral' : 'Tikoid'), $rowSnippet);
	$rowSnippet = str_replace('{socialmedia}', ($data['socialmedia'] == 'ig' ? 'Instagram' : 'Tiktok'), $rowSnippet);
	$rowData .= $rowSnippet;
}

$tpl = str_replace('{reviewmessage}', $reviewmessage, $tpl);
$tpl = tpl_replace('row', $rowData, $tpl);


output($tpl, $options);


function formatNumber($number) {
    $suffix = '';
    if ($number >= 1000 && $number < 1000000) {
        $number = number_format($number / 1000, 1);
        $suffix = 'k';
    } elseif ($number >= 1000000 && $number < 1000000000) {
        $number = number_format($number / 1000000, 1);
        $suffix = 'M';
    } elseif ($number >= 1000000000) {
        $number = number_format($number / 1000000000, 1);
        $suffix = 'B';
    }

    // Remove trailing ".0" if present
    $number = rtrim($number, '.0');

    return $number . $suffix;
}