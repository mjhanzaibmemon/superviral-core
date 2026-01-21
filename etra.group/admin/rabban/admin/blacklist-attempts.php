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

$howmuchtogoback = 7;
$totalcount = 0;
$totalloss = 0;

$timesnow = time();
$timesnowago = time() - (86400 * $howmuchtogoback);


$q = mysql_query("select dates, COUNT
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

while ($info = mysql_fetch_array($q)) {

    $postdaydata .= $info["COUNT"] . ",";
}

$dataCount = mysqli_num_rows($q);

$daycats = strtotime("today", time()) + 86400;


for ($x = $howmuchtogoback; $x >= 1; $x--) {

    $newdate = date('dmY', $daycats - (86400 * $x));


    $labelsbackend .= "'" . date('l - d/m/Y', $daycats - (86400 * $x)) . "',";
}


$labelsbackend = rtrim($labelsbackend, ',');

$postdaydata = rtrim($postdaydata, ',');


$q = mysql_query("SELECT * FROM `blacklist_attempts` WHERE `added` BETWEEN '$timesnowago' and '$timesnow' ORDER BY `id` DESC");

while ($info = mysql_fetch_array($q)) {


    $refunddata .= '
  <tr>
    <td>' . $info['id'] . ' - ' . $info['emailaddress'] . '</td>
    <td>' . $info['billingname'] . '</td>
    <td>' . $info['ipaddress'] . '</td>
    <td>' . ago($info['added']) . '</td>
  </tr>';


    $packagesinfo[$pid]['refundamount']++;
    $packagesinfo[$pid]['refundtotalloss'] = $packagesinfo[$pid]['refundtotalloss'] + sprintf('%.2f', $info['price'] / 100);

    $totalcount++;
}



?>
<!DOCTYPE html>

<head>
    <title>Blacklist Attempts</title>
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
        <h2>Blacklist attempts</h2>
        <canvas id="myChart" style="max-height:300px;margin:25px;"></canvas>
    </div>


    <div class="alignthis" align="center">
        <h2>Blacklist attempts in the last <?= $howmuchtogoback ?>-days</h2>

        <div class="box23">

            <table style="width:100%">
                <?= $refundedservice ?>
                <tr>

                    <td><b>Total</b></td>
                    <td><b><?= $totalcount ?></b></td>

                </tr>
            </table>

        </div>



        <div class="box23">

            <table style="width:100%"><?= $refunddata ?></table>

        </div>

    </div>



    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?= $labelsbackend ?>],
                datasets: [{
                    label: 'Blacklist attempts found',
                    data: [<?= $postdaydata ?>],
                    borderColor: '#E52A01',
                    backgroundColor: '#E52A01',
                    //   borderColor: '#50C878',
                    //   backgroundColor: '#50C878',
                }, ]
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