<?php

include('../sm-db.php');



/*$gpackageid = addslashes($_GET['pid']);

if(!empty($gpackageid))die('nope');

$q = mysql_query("SELECT `deliverytime`,`fulfilled`,`id`,`added` FROM `orders` WHERE `deliverytime` = '0' AND `fulfilled` != '0' ORDER BY `id` ASC LIMIT 5000");

if(mysql_num_rows($q)=='0')die('all done');

while($info = mysql_fetch_array($q)){



  $deliverytime = $info['fulfilled'] - $info['added'];

  $updateq = mysql_query("UPDATE `orders` SET `deliverytime` = '$deliverytime' WHERE `id` = '{$info['id']}' LIMIT 1");

  if($updateq)echo $info['id'].' - '.$deliverytime.'<hr>';

}

//echo '<meta http-equiv="refresh" content="1">';

die;*/





function remove_outliers($dataset, $magnitude = 1) {

  $count = count($dataset);
  $mean = array_sum($dataset) / $count; // Calculate the mean
  $deviation = sqrt(array_sum(array_map("sd_square", $dataset, array_fill(0, $count, $mean))) / $count) * $magnitude; // Calculate standard deviation and times by magnitude

  return array_filter($dataset, function($x) use ($mean, $deviation) { return ($x <= $mean + $deviation && $x >= $mean - $deviation); }); // Return filtered array of values that lie within $mean +- $deviation.
}

function sd_square($x, $mean) {
  return pow($x - $mean, 2);
} 






$gpackageid = addslashes($_GET['pid']);

if((empty($gpackageid))&&($gpackageid!=='0')){die('ASD: No ID');}

$q = mysql_query("SELECT * FROM `orders` WHERE `fulfilled` != '0' AND `deliverytime` = '0' ORDER BY `id` DESC LIMIT 200");
$rowsfound = mysql_num_rows($q);



$findpackage = mysql_query("SELECT * FROM `packages` WHERE `premium` = '0' LIMIT $gpackageid, 1");
$packageinfo = mysql_fetch_array($findpackage);

if(empty($packageinfo['id']))die('No package selected');


$packageamount = $packageinfo['amount'];
$packageamountupsell = $packageinfo['amount'] + ($packageinfo['amount'] / 2);
$thispackageid = $packageinfo['id'];


echo '<h1>'.$packageinfo['id'].' - '.$packageamount.' '.$packageinfo['type'].'</h1>';

echo 'Packages:<br>'.$packageamount.' '.$packageinfo['type'].'<br>';
echo $packageamountupsell.' '.$packageinfo['type'].'<hr>';

$now = time();
$twodaysago = $now - (6 * 86400);//5 days

$findorders = mysql_query("SELECT * FROM `orders` WHERE `packageid` = '$thispackageid' AND `deliverytime` != '0' AND `added` BETWEEN '$twodaysago' AND '$now' ORDER BY `id` DESC LIMIT 300");

//IF LESS THAN 10
if(mysql_num_rows($findorders)<='10')$findorders = mysql_query("SELECT * FROM `orders` WHERE `packageid` = '$thispackageid' AND `deliverytime` != '0' ORDER BY `id` DESC LIMIT 50");


echo mysql_num_rows($findorders).'<br>';

while($finfo = mysql_fetch_array($findorders)){

$alltimes[] = $finfo['deliverytime'];

}



$alltimes = array_filter($alltimes);
$refined = remove_outliers($alltimes);


echo 'UNREFINED:<br><pre>';

print_r($alltimes);

echo '</pre>';

echo '<hr>REFINED:<br><pre>';

print_r($alltimes);

echo '</pre>';





$unrefined = array_sum($alltimes)/count($alltimes);
$refined = round(trim(array_sum($refined)/count($refined)));
$refined = round($refined * 1.1);


echo 'Unrefined: '.$unrefined.'<br>';
echo 'Refined: '.$refined.'<hr>';

echo 'Unrefined: '.gmdate("H:i:s", $unrefined).'<br>';
echo 'Refined: '.gmdate("H:i:s", $refined).'<br>';

$updateq1 = mysql_query("UPDATE `packages` SET `delivtime` = '$refined' WHERE `id` = '{$packageinfo['id']}' LIMIT 1");



if($updateq1)echo '<br>Updated: '.$packageinfo['id'];

?>