<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


$daysago = 7;

$beginofdaysago = strtotime("today", (time() - ($daysago * 86400)));

$beginoftoday = strtotime("today", time());


//TEST
$beginofdaysago = $beginofdaysago + 86400;
$beginoftoday = $beginoftoday + 86400;


if($brand == 'sv' || $brand == 'fb'){

    $searchforsolveq = mysql_query("SELECT ias2.* FROM `ig_api_stats` ias1
                                  JOIN (
                                        SELECT * FROM `ig_api_stats` 
                                        WHERE `dontcheck` = '0' 
                                        AND `added` 
                                        BETWEEN '$beginofdaysago' AND '$beginoftoday'  ORDER BY `id` DESC 
                                      ) AS ias2 
                                  ON ias1.igusername = ias2.igusername
                                  WHERE ias1.`count` != '0' AND ias2.`count` = 0 
                                  GROUP BY ias2.id ORDER BY ias2.id DESC ;"); //THEY RESOLVED IT!

}

if($brand == 'tp' || $brand == 'to'){

    $searchforsolveq = mysql_query("SELECT ias2.* FROM `tt_api_stats` ias1
                                  JOIN (
                                        SELECT * FROM `tt_api_stats` 
                                        WHERE `dontcheck` = '0' 
                                        AND `added` 
                                        BETWEEN '$beginofdaysago' AND '$beginoftoday'  ORDER BY `id` DESC 
                                      ) AS ias2 
                                  ON ias1.ttusername = ias2.ttusername
                                  WHERE ias1.`count` != '0' AND ias2.`count` = 0 
                                  GROUP BY ias2.id ORDER BY ias2.id DESC ;"); //THEY RESOLVED IT!

}


$searchforsolvedinforesults = mysql_num_rows($searchforsolveq);

if ($searchforsolvedinforesults > 0) {
  $ids = [];
 
  while ($data = mysql_fetch_array($searchforsolveq)) {
    $ids[] = $data['id'];
  }

  $ids = implode(',', $ids);

  if($brand == 'sv' || $brand == 'fb'){

        $queryRun = mysql_query("UPDATE `ig_api_stats` SET `dontcheck` = '1' WHERE `id` IN ($ids)");
  }
  
    if($brand == 'tp' || $brand == 'to'){
        $queryRun = mysql_query("UPDATE `tt_api_stats` SET `dontcheck` = '1' WHERE `id` IN ($ids)");
    }
  }
 //they resolved it - dont add

if($brand == 'sv' || $brand == 'fb'){
    $q = mysql_query("SELECT * FROM `ig_api_stats` WHERE `dontcheck` = '0' AND  `added` BETWEEN '$beginofdaysago' and '$beginoftoday' ORDER BY `id` DESC");
}

if($brand == 'tp' || $brand == 'to'){
    $q = mysql_query("SELECT * FROM `tt_api_stats` WHERE `dontcheck` = '0' AND  `added` BETWEEN '$beginofdaysago' and '$beginoftoday' ORDER BY `id` DESC");
}

while ($info = mysql_fetch_array($q)) {


  $day = date("dmY", $info['added']);

  if ($info['count'] !== '0') $positiveresult[$day]++;
  else $negativeresult[$day]++;
}


$daycats = strtotime("today", time()) + 86400;


for ($x = $daysago; $x >= 1; $x--) {

  $newdate = date('dmY', $daycats - (86400 * $x));


  $labels .= $labels[$newdate];
  $labelsbackend .= "'" . date('l - d/m/Y', $daycats - (86400 * $x)) . "',";

  $checkthisdayp = $positiveresult[$newdate];
  if (empty($checkthisdayp)) $checkthisdayp = 0;

  $checkthisdayn = $negativeresult[$newdate];
  if (empty($checkthisdayn)) $checkthisdayn = 0;


  $posdaydata .= $checkthisdayp . ',';
  $negdaydata .= $checkthisdayn . ',';
}






$labels = rtrim($labels, ',');
$labelsbackend = rtrim($labelsbackend, ',');

$negdaydata = rtrim($negdaydata, ',');
$posdaydata = rtrim($posdaydata, ',');




///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////



$getallpackagesinfoq = mysql_query("SELECT * FROM `packages` WHERE brand = '$brand'");

while ($getallpackagesinfo = mysql_fetch_array($getallpackagesinfoq)) {

  $thepid = $getallpackagesinfo['id'];

  $packagesinfo[$thepid] = $getallpackagesinfo;
}

///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////


$howmuchtogoback = 10;
$totalcount = 0;
$totalloss = 0;

$refundtimesnow = time();
$refundtimesnowago = time() - (86400 * $howmuchtogoback);

$q = mysql_query("SELECT * FROM `orders` WHERE `refundtime` BETWEEN '$refundtimesnowago' and '$refundtimesnow' AND brand = '$brand' ORDER BY `refundtime` DESC");

while ($info = mysql_fetch_array($q)) {

  $pid = $info['packageid'];

  $refunddata .= '
  <tr>
    <td>' . $info['id'] . ' - ' . $info['emailaddress'] . '</td>
    <td>' . $info['amount'] . ' ' . $info['packagetype'] . '</td>
    <td>£' . sprintf('%.2f', $info['price'] / 100) . '</td>
    <td>' . ago($info['refundtime']) . '</td>
    <td>' . $info['packageid'] . ' - ' . $packagesinfo[$pid]['jap1'] . '</td>
  </tr>';


  $packagesinfo[$pid]['refundamount']++;
  $packagesinfo[$pid]['refundtotalloss'] = $packagesinfo[$pid]['refundtotalloss'] + sprintf('%.2f', $info['price'] / 100);

  $totalcount++;
  $totalloss = $totalloss + sprintf('%.2f', $info['price'] / 100);
}


foreach ($packagesinfo as $packinfo) {

  if ($packinfo['refundamount'] == '') continue;

  $refundedservice .= '
  <tr>
    <td>' . $packinfo['amount'] . ' ' . $packinfo['type'] . '</td>
    <td><font color="red">' . $packinfo['refundamount'] . '</font></td>
    <td>£' . $packinfo['refundtotalloss'] . '</td>
    <td>' . $packinfo['jap1'] . '</td>
  </tr>';
}

// Blacklist attempts code here //////////////////////////////////////////////////////////////////////////////////////////////////

$howmuchtogoback1 = 7;
$totalcount1 = 0;

$timesnow = time();
$timesnowago = time() - (86400 * $howmuchtogoback1);


$q1 = mysql_query("select dates, COUNT
from
(
  select DATE(FROM_UNIXTIME(added)) AS dates, COUNT(*) AS count
  from blacklist_attempts
  where brand = '$brand' AND DATE(FROM_UNIXTIME(added)) BETWEEN date_add(curdate(), interval -6 day) AND curdate()
  group by DATE(FROM_UNIXTIME(added))
  union all
  select curdate(), 0
  union all
  select date_add(curdate(), interval -1 day), 0
  union all
  select date_add(curdate(), interval -2 day), 0
  union all
  select date_add(curdate(), interval -3 day), 0
  union all
  select date_add(curdate(), interval -4 day), 0
  union all
  select date_add(curdate(), interval -5 day), 0
  union all
  select date_add(curdate(), interval -6 day), 0
) x
group BY x.dates
order BY x.dates ");

while ($info1 = mysql_fetch_array($q1)) {

  $postdaydata1 .= $info1["COUNT"] . ",";
}

$dataCount1 = mysqli_num_rows($q1);

$daycats1 = strtotime("today", time()) + 86400;


for ($x = $howmuchtogoback1; $x >= 1; $x--) {

  $newdate1 = date('dmY', $daycats1 - (86400 * $x));


  $labelsbackend1 .= "'" . date('l - d/m/Y', $daycats1 - (86400 * $x)) . "',";
}


$labelsbackend1 = rtrim($labelsbackend1, ',');

$postdaydata1 = rtrim($postdaydata1, ',');


$q1 = mysql_query("SELECT * FROM `blacklist_attempts` WHERE `added` BETWEEN '$timesnowago' and '$timesnow' AND brand = '$brand' ORDER BY `id` DESC");

while ($info1 = mysql_fetch_array($q1)) {


  $refunddata1 .= '
  <tr>
    <td>' . $info1['id'] . ' - ' . $info1['emailaddress'] . '</td>
    <td>' . $info1['billingname'] . '</td>
    <td>' . $info1['ipaddress'] . '</td>
    <td>' . ago($info1['added']) . '</td>
  </tr>';


  // $packagesinfo[$pid]['refundamount']++;
  // $packagesinfo[$pid]['refundtotalloss'] = $packagesinfo[$pid]['refundtotalloss'] + sprintf('%.2f', $info['price'] / 100);

  $totalcount1++;
}

// //////////////////////////////////////DEFECT ORDER CODE////////////////////////////////////////////////////////////

// Blacklist attempts code here //////////////////////////////////////////////////////////////////////////////////////////////////

$totalcount2 = 0;
$q1 = mysql_query("select dates, COUNT
from
(
  select DATE(FROM_UNIXTIME(added)) AS dates, COUNT(*) AS count
  from orders
  WHERE brand = '$brand' AND defect = 0  AND fulfilled > 0 AND DATE(FROM_UNIXTIME(added)) BETWEEN date_add(curdate(), interval -6 day) AND curdate()
  group by DATE(FROM_UNIXTIME(added))
  union all
  select curdate(), 0
  union all
  select date_add(curdate(), interval -1 day), 0
  union all
  select date_add(curdate(), interval -2 day), 0
  union all
  select date_add(curdate(), interval -3 day), 0
  union all
  select date_add(curdate(), interval -4 day), 0
  union all
  select date_add(curdate(), interval -5 day), 0
  union all
  select date_add(curdate(), interval -6 day), 0
) x
group BY x.dates
order BY x.dates ");

while ($info1 = mysql_fetch_array($q1)) {

  $postdaydata2 .= $info1["COUNT"] . ",";
}

$daycats2 = strtotime("today", time()) + 86400;


for ($x = $howmuchtogoback1; $x >= 1; $x--) {

  $newdate2 = date('dmY', $daycats1 - (86400 * $x));


  $labelsbackend2 .= "'" . date('l - d/m/Y', $daycats2 - (86400 * $x)) . "',";
}


$labelsbackend2 = rtrim($labelsbackend2, ',');

$postdaydata2 = rtrim($postdaydata2, ',');

$q1 = mysql_query("select dates, COUNT
from
(
  select DATE(FROM_UNIXTIME(added)) AS dates, COUNT(*) AS count
  from orders
  WHERE brand = '$brand' AND defect > 0 AND fulfilled = 0 AND DATE(FROM_UNIXTIME(added)) BETWEEN date_add(curdate(), interval -6 day) AND curdate()
  group by DATE(FROM_UNIXTIME(added))
  union all
  select curdate(), 0
  union all
  select date_add(curdate(), interval -1 day), 0
  union all
  select date_add(curdate(), interval -2 day), 0
  union all
  select date_add(curdate(), interval -3 day), 0
  union all
  select date_add(curdate(), interval -4 day), 0
  union all
  select date_add(curdate(), interval -5 day), 0
  union all
  select date_add(curdate(), interval -6 day), 0
) x
group BY x.dates
order BY x.dates ");

while ($info1 = mysql_fetch_array($q1)) {

  $negdaydata2 .= $info1["COUNT"] . ",";
}

$negdaydata2 = rtrim($negdaydata2, ',');

$q1 = mysql_query("SELECT * FROM `orders` WHERE `added` BETWEEN '$timesnowago' and '$timesnow' AND brand = '$brand' ORDER BY `id` DESC");

while ($info1 = mysql_fetch_array($q1)) {


  $refunddata2 .= '
  <tr>
    <td>' . $info1['id'] . ' - ' . $info1['emailaddress'] . '</td>
    <td>' . $info1['payment_billingname'] . '</td>
    <td>' . ago($info1['added']) . '</td>
  </tr>';


  // $packagesinfo[$pid]['refundamount']++;
  // $packagesinfo[$pid]['refundtotalloss'] = $packagesinfo[$pid]['refundtotalloss'] + sprintf('%.2f', $info['price'] / 100);

  $totalcount2++;
}

$tpl = str_replace('{howmuchtogoback}',$howmuchtogoback,$tpl);
$tpl = str_replace('{refundedservice}',$refundedservice,$tpl);
$tpl = str_replace('{totalcount}',$totalcount,$tpl);
$tpl = str_replace('{totalloss}',$totalloss,$tpl);
$tpl = str_replace('{refunddata}',$refunddata,$tpl);
$tpl = str_replace('{howmuchtogoback1}',$howmuchtogoback1,$tpl);
$tpl = str_replace('{refundedservice}',$refundedservice,$tpl);
$tpl = str_replace('{totalcount1}',$totalcount1,$tpl);
$tpl = str_replace('{refunddata1}',$refunddata1,$tpl);
$tpl = str_replace('{totalcount2}',$totalcount2,$tpl);
$tpl = str_replace('{refunddata2}',$refunddata2,$tpl);
$tpl = str_replace('{labelsbackend}',$labelsbackend,$tpl);
$tpl = str_replace('{posdaydata}',$posdaydata,$tpl);
$tpl = str_replace('{negdaydata}',$negdaydata,$tpl);
$tpl = str_replace('{labelsbackend1}',$labelsbackend1,$tpl);
$tpl = str_replace('{postdaydata1}',$postdaydata1,$tpl);
$tpl = str_replace('{labelsbackend2}',$labelsbackend2,$tpl);
$tpl = str_replace('{postdaydata2}',$postdaydata2,$tpl);
$tpl = str_replace('{negdaydata2}',$negdaydata2,$tpl);


output($tpl, $options);
