<?php



function ago($time)
{
  $periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
  $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
  $now = time();
  $difference     = $now - $time;
  $tense         = 'ago';
  for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
    $difference /= $lengths[$j];
  }
  $difference = round($difference);
  if ($difference != 1) {
    $periods[$j] .= "s";
  }
  return "$difference $periods[$j] ago";
}




include('adminheader.php');


$daysago = 7;

$beginofdaysago = strtotime("today", (time() - ($daysago * 86400)));

$beginoftoday = strtotime("today", time());


//TEST
$beginofdaysago = $beginofdaysago + 86400;
$beginoftoday = $beginoftoday + 86400;

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

$searchforsolvedinforesults = mysql_num_rows($searchforsolveq);

if ($searchforsolvedinforesults > 0) {
  $ids = [];
 
  while ($data = mysql_fetch_array($searchforsolveq)) {
    $ids[] = $data['id'];
  }

  $ids = implode(',', $ids);

  $queryRun = mysql_query("UPDATE `ig_api_stats` SET `dontcheck` = '1' WHERE `id` IN ($ids)");
} //they resolved it - dont add

$q = mysql_query("SELECT * FROM `ig_api_stats` WHERE `dontcheck` = '0' AND  `added` BETWEEN '$beginofdaysago' and '$beginoftoday' ORDER BY `id` DESC");

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



$getallpackagesinfoq = mysql_query("SELECT * FROM `packages`");

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

$q = mysql_query("SELECT * FROM `orders` WHERE `refundtime` BETWEEN '$refundtimesnowago' and '$refundtimesnow' ORDER BY `refundtime` DESC");

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
  where DATE(FROM_UNIXTIME(added)) BETWEEN date_add(curdate(), interval -6 day) AND curdate()
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


$q1 = mysql_query("SELECT * FROM `blacklist_attempts` WHERE `added` BETWEEN '$timesnowago' and '$timesnow' ORDER BY `id` DESC");

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

$totalcount2 =0;
$q1 = mysql_query("select dates, COUNT
from
(
  select DATE(FROM_UNIXTIME(added)) AS dates, COUNT(*) AS count
  from orders
  WHERE defect = 0  AND fulfilled > 0 AND DATE(FROM_UNIXTIME(added)) BETWEEN date_add(curdate(), interval -6 day) AND curdate()
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
  WHERE defect > 0 AND fulfilled = 0 AND DATE(FROM_UNIXTIME(added)) BETWEEN date_add(curdate(), interval -6 day) AND curdate()
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

$q1 = mysql_query("SELECT * FROM `orders` WHERE `defect` != '0' AND `added` BETWEEN '$timesnowago' and '$timesnow' ORDER BY `id` DESC");


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


?>
<!DOCTYPE html>

<head>
  <title>API STATS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="/favicon.ico" />
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/css/style.css">
  <link rel="stylesheet" type="text/css" href="/css/orderform.css">

  <style type="text/css">
    .btn {
      width: 100px;
      text-align: center;
    }

    .alignthis {
      padding: 55px 0;
    }


    .box23 {
      margin: 66px auto;
      width: 950px;
      background: #fff;
      border-radius: 5px;
      text-align: left;
      padding: 15px;
    }
  </style>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.6.0/chart.min.js"></script>
</head>

<body>


  <?= $header ?>
  <div class="alignthis" align="center">
    <h1 style="color:unset;">Superviral Overall Status 📊</h1>
  </div>

  <div class="alignthis" align="center">
    <h2>API Success/Failures</h2>
    <canvas id="myChart" style="max-height:300px;margin:25px;"></canvas>
  </div>


  <div class="alignthis" align="center">
    <h2>Most Refunded Services in the last <?= $howmuchtogoback ?>-days</h2>

    <div class="box23">

      <table style="width:100%">
        <?= $refundedservice ?>
        <tr>

          <td><b>Total</b></td>
          <td><b><?= $totalcount ?></b></td>
          <td><b>£<?= $totalloss ?></b></td>

        </tr>
      </table>

    </div>



    <div class="box23">

      <table style="width:100%"><?= $refunddata ?></table>

    </div>
  </div>
  <!--////////////////////// Blacklist attempts code here ////////////////////////////////////-->


  <div class="alignthis" align="center">
    <h2>Blacklist attempts</h2>
    <canvas id="myChart1" style="max-height:300px;margin:25px;"></canvas>
  </div>


  <div class="alignthis" align="center">
    <h2>Blacklist attempts in the last <?= $howmuchtogoback1 ?>-days</h2>

    <div class="box23">

      <table style="width:100%">
        <?= $refundedservice ?>
        <tr>

          <td><b>Total</b></td>
          <td><b><?= $totalcount1 ?></b></td>

        </tr>
      </table>

    </div>



    <div class="box23">

      <table style="width:100%"><?= $refunddata1 ?></table>

    </div>

  </div>

  <!-- Defect order code here -->

  <div class="alignthis" align="center">
    <h2>Order Defect</h2>
    <canvas id="myChart2" style="max-height:300px;margin:25px;"></canvas>
  </div>


  <div class="alignthis" align="center">
    <h2>Most Defect orders in the last <?= $howmuchtogoback1 ?>-days</h2>

    <div class="box23">

      <table style="width:100%">
        <?= $refundedservice ?>
        <tr>

          <td><b>Total</b></td>
          <td><b><?= $totalcount2 ?></b></td>

        </tr>
      </table>

    </div>



    <div class="box23">

      <table style="width:100%"><?= $refunddata2 ?></table>

    </div>
  </div>



  <script>

    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: [<?= $labelsbackend ?>],
        datasets: [{
            label: 'Thumbs loaded',
            data: [<?= $posdaydata ?>],
            borderColor: '#50C878',
            backgroundColor: '#50C878',
          },
          {
            label: 'Thumbs not found',
            data: [<?= $negdaydata ?>],
            borderColor: '#E52A01',
            backgroundColor: '#E52A01',
          }
        ]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    const ctx1 = document.getElementById('myChart1').getContext('2d');
    const myChart1 = new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: [<?= $labelsbackend1 ?>],
        datasets: [{
          label: 'Blacklist attempts found',
          data: [<?= $postdaydata1 ?>],
          borderColor: '#E52A01',
          backgroundColor: '#E52A01',
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    const ctx2 = document.getElementById('myChart2').getContext('2d');
    const myChart2 = new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: [<?= $labelsbackend2 ?>],
        datasets: [{
            label: 'Successfully processed',
            data: [<?= $postdaydata2 ?>],
            borderColor: '#50C878',
            backgroundColor: '#50C878',
          },
          {
            label: 'Defect',
            data: [<?= $negdaydata2 ?>],
            borderColor: '#E52A01',
            backgroundColor: '#E52A01',
          }
        ]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

  </script>


</body>

</html>