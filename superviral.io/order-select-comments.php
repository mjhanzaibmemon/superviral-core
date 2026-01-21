<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();
header('Content-type: text/html; charset=utf-8');

$db = 1;
include('header.php');



include('ordercontrol.php');

//IF SUBMITTED

$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));
$packagetitle = $packageinfo['amount'] . ' ' . ucwords($packageinfo['type']) . ' Package';

$hidevideos = $packageinfo['type'];
$packagetype = $packageinfo['type'];


$maxamount = $packageinfo['amount'];
$postlimit = $packageinfo['postlimit'];
$packagedesc = ucwords($packageinfo['amount'] . '{' . $packageinfo['type'] . ' package}');
/////////

$ordersession_details = mysql_fetch_array(mysql_query("SELECT id FROM `order_session` WHERE `order_session` = '{$ordersession}' LIMIT 1"));

if (!empty($_POST['comments_selected'])) {
	$submitted_values = json_decode($_POST['comments_selected'], true);
	$submitted_values_tags = json_decode($_POST['comments_selected_tags'], true);

	$i = 0;
	foreach ($submitted_values as $value) {

		$value = str_replace("'", "\'", $value);
		$res =  mysql_query("INSERT INTO `order_comments` SET 
	  														`order_session_id` = '{$ordersession_details['id']}', 
	  														`comment` = '{$value}',
	  														`tags` = '{$submitted_values_tags[$i]}'");

		if ($res) {
			$values .= mysql_insert_id() . '~~~';
		}

		$i++;
	}

	echo $values;


	mysql_query("UPDATE `order_session` SET 
			   `choose_comments` = '{$values}'
			  WHERE `order_session` = '$ordersession' LIMIT 1");


	header('Location: /' . $loclinkforward . $locas[$loc]['order'] . '/' . $locas[$loc]['order2'] . '/');

	die;
}


if (($packageinfo['type'] == 'comments')) {



	$back = '/' . $locas[$loc]['order'] . '/' . $locas[$loc]['order1select'] . '/';



	$chooseposts = explode('~~~', $info['chooseposts']);


	$postURL = "";
	foreach ($chooseposts as $posts) {

		if (empty($posts)) continue;

		$postURL .= " " . $posts . " ";

		$posts1 = explode('###', $posts);



		$profilepicture .= '<img src="' . $posts1[1] . '" alt="">';
	}

}

// ig comments categories
$queriesRun = mysql_query("SELECT * FROM `ig_comments_categories`");

$igCommentsCategories = '';

while ($categoriesData = mysql_fetch_array($queriesRun)) {

	$igCommentsCategories .= '<div class="theme" data-id="' . $categoriesData['id'] . '"><img src="/imgs/categories/'. $categoriesData['icons'] .'"><span>' . $categoriesData['name'] . '</span></div>';
}

if (!empty($_COOKIE['discount'])) {
	include('detectdiscount.php');
}


if ($loggedin == true) {
	$displayaccountbtn = 'displayaccountbtn';
}

$tpl = file_get_contents('order-template.html');
$body = file_get_contents('order-select-comments.html');

$tpl = str_replace('{body}', $body, $tpl);

$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);
$tpl = str_replace('{back}', '/' . $loclinkforward . $locas[$loc]['order'] . '/' . $locas[$loc]['order1'] . '/', $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{postlimit}', $postlimit, $tpl);
$tpl = str_replace('{maxamount}', $maxamount, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{username}', $info['igusername'], $tpl);
$tpl = str_replace('{ordersession}', $info['order_session'], $tpl);
$tpl = str_replace('{packagedesc}', $packagedesc, $tpl);
$tpl = str_replace('{changepackagehref}', '/' . $loclinkforward . $locas[$loc]['order'] . '/' . $locas[$loc]['order1'] . '/', $tpl);
$tpl = str_replace('{profilepicture}', $profilepicture, $tpl);
$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);
$tpl = str_replace('{igCommentsCategories}', $igCommentsCategories, $tpl);
$tpl = str_replace('{packagetitle}', $packagetitle, $tpl);

if ($packagetype == 'likes') $tpl = str_replace('{divboxpckg}', '{divboxlikespckg}', $tpl);
if ($packagetype == 'views') $tpl = str_replace('{divboxpckg}', '{divboxviewspckg}', $tpl);
$tpl = str_replace('{postURL}', $postURL, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order1-select') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while ($cinfo = mysql_fetch_array($contentq)) {

	$foundcontent = 0;


	if ($cinfo['name'] == 'packagedesc') {

		$cinfo['content'] = str_replace('$packageinfo[\'amount\']', $packageinfo['amount'], $cinfo['content']);
		$tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
		$foundcontent = 1;
	}



	if ($foundcontent == 0) $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
}


echo $tpl;
