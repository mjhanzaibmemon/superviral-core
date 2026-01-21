<?php



function ago($time)
{$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");
   $now = time();
       $difference     = $now - $time;
       $tense         = 'ago';
   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }
   $difference = round($difference);
   if($difference != 1) {
       $periods[$j].= "s";
   }   return "$difference $periods[$j] ago";}




include('adminheader.php');
$dbName = $tikoidDB;
mysql_select_db($dbName , $conn);

$daysago = 7;

$beginofdaysago = strtotime("today", (time() - ($daysago * 86400)));

$beginoftoday = strtotime("today", time());


//TEST
$beginofdaysago = $beginofdaysago + 86400;
$beginoftoday = $beginoftoday + 86400;



$q = mysql_query("SELECT * FROM `tt_api_stats` WHERE `dontcheck` = '0' AND  `added` BETWEEN '$beginofdaysago' and '$beginoftoday' ORDER BY `id` DESC");

while($info = mysql_fetch_array($q)){


    $day = date("dmY", $info['added']);
    
    if($info['count']!=='0')$positiveresult[$day]++;
    //

    if($info['count']=='0'){

    	$searchthatbeginofthatday = strtotime("today", $info['added']);
    	$searchthatbeginofthatdayend = $searchthatbeginofthatday + 86400;

    		$searchforsolveq = mysql_query("SELECT * FROM `tt_api_stats` WHERE `ttusername` = '{$info['igusername']}' AND `count` != '0' AND `added` BETWEEN '$searchthatbeginofthatday' and '$searchthatbeginofthatdayend' LIMIT 1");//THEY RESOLVED IT!

			$searchforsolvedinforesults = mysql_num_rows($searchforsolveq);

			if($searchforsolvedinforesults=='1'){

				//DELETE ON MYSQL
				mysql_query("UPDATE `tt_api_stats` SET `dontcheck` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

			}//they resolved it - dont add
			else
			{

				if($info['count']=='0')$negativeresult[$day]++;

			}


    }


}


$daycats = strtotime("today", time()) + 86400;


for ($x = $daysago; $x >= 1; $x--) {

	$newdate = date('dmY', $daycats - (86400 * $x));


  $labels .= $labels[$newdate];
  $labelsbackend .= "'".date('l - d/m/Y', $daycats - (86400 * $x))."',";

  $checkthisdayp = $positiveresult[$newdate];
	if(empty($checkthisdayp))$checkthisdayp = 0;  

  $checkthisdayn = $negativeresult[$newdate];
	if(empty($checkthisdayn))$checkthisdayn = 0;  


  $posdaydata .= $checkthisdayp.',';
  $negdaydata .= $checkthisdayn.',';

}






$labels = rtrim($labels,',');
$labelsbackend = rtrim($labelsbackend,',');

$negdaydata = rtrim($negdaydata,',');
$posdaydata = rtrim($posdaydata,',');




///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////



$getallpackagesinfoq = mysql_query("SELECT * FROM `packages`");

while($getallpackagesinfo = mysql_fetch_array($getallpackagesinfoq)){

  $thepid = $getallpackagesinfo['id'];

  $packagesinfo[$thepid] = $getallpackagesinfo;


}

///////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////


$howmuchtogoback = 10;
$totalcount=0;
$totalloss=0;

$refundtimesnow = time();
$refundtimesnowago = time() - (86400 * $howmuchtogoback);

$q = mysql_query("SELECT * FROM `orders` WHERE `refundtime` BETWEEN '$refundtimesnowago' and '$refundtimesnow' ORDER BY `refundtime` DESC");

while($info = mysql_fetch_array($q)){

  $pid = $info['packageid'];

  $refunddata .= '
  <tr>
    <td>'.$info['id'].' - '.$info['emailaddress'].'</td>
    <td>'.$info['amount'].' '.$info['packagetype'].'</td>
    <td>£'.sprintf('%.2f', $info['price'] / 100).'</td>
    <td>'.ago($info['refundtime']).'</td>
    <td>'.$info['packageid'].' - '.$packagesinfo[$pid]['jap1'].'</td>
  </tr>';


  $packagesinfo[$pid]['refundamount']++;
  $packagesinfo[$pid]['refundtotalloss'] = $packagesinfo[$pid]['refundtotalloss'] + sprintf('%.2f', $info['price'] / 100);

  $totalcount++;
  $totalloss = $totalloss + sprintf('%.2f', $info['price'] / 100);


}


foreach($packagesinfo as $packinfo){

  if($packinfo['refundamount']=='')continue;

  $refundedservice .= '
  <tr>
    <td>'.$packinfo['amount'].' '.$packinfo['type'].'</td>
    <td><font color="red">'.$packinfo['refundamount'].'</font></td>
    <td>£'.$packinfo['refundtotalloss'].'</td>
    <td>'.$packinfo['jap1'].'</td>
  </tr>';


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

.btn{width:100px;text-align:center;}

.alignthis{padding:55px 0;}


.box23{margin: 66px auto;
    width: 950px;
    background: #fff;
    border-radius: 5px;text-align:left;padding:15px;}


</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.6.0/chart.min.js"></script>
</head>

	<body>


		<?=$header?>
    <div class="alignthis" align="center">
      <h1 style="color:unset;">Tikoid Overall Status 📊</h1>
    </div>

    <div class="alignthis" align="center">
      <h2>API Success/Failures</h2>
		<canvas id="myChart" style="max-height:300px;margin:25px;"></canvas>
    </div>


    <div class="alignthis" align="center">
      <h2>Most Refunded Services in the last <?=$howmuchtogoback?>-days</h2>

      <div class="box23">

        <table style="width:100%">
          <?=$refundedservice?>
          <tr>

            <td><b>Total</b></td>
            <td><b><?=$totalcount?></b></td>
            <td><b>£<?=$totalloss?></b></td>

          </tr>
        </table>
      
      </div>



      <div class="box23">

        <table style="width:100%"><?=$refunddata?></table>
      
      </div>

    </div>



		<script>
const ctx = document.getElementById('myChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?=$labelsbackend?>],
        datasets: [  {
      label: 'Thumbs loaded',
      data: [<?=$posdaydata?>],
      borderColor: '#50C878',
      backgroundColor: '#50C878',
    },
    {
      label: 'Thumbs not found',
      data: [<?=$negdaydata?>],
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

		</script>


	</body>
</html>