<?php


include('adminheader.php');


date_default_timezone_set('Europe/London');

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


$reportdone = addslashes($_POST['reportdone']);


if(!empty($reportdone)){

$nowmarkoff = time();

mysql_query("UPDATE `admin_notifications` SET `done` = '2', `respondedtocustomer` = '$nowmarkoff' WHERE `id` = '$reportdone' LIMIT 1");

}


$q = mysql_query("SELECT * FROM `admin_notifications` WHERE `done` = '1' ORDER BY `response` ASC");
$field = '<input type="hidden" name="orderid" value="'.$orderid.'">';

$q2 = mysql_query("SELECT * FROM `admin_notifications` WHERE `done` = '1'");
$totalleft = mysql_num_rows($q2).' Reports Remaining';

while($info = mysql_fetch_array($q)){


			$msgs2 .= '


				<tr>
				<td>'.$info['emailaddress'].'<br>#️⃣ Order ID: <a target="_BLANK" href="https://superviral.io/admin/check-user.php?orderid='.$info['orderid'].'#order'.$info['orderid'].'">#'.$info['orderid'].'</a></td>
				<td>
				<span class="reporter">'.ucwords($info['admin_name']).', '.ago($info['added']).', '.date("d/m/Y H:i:s",$info['added']).'</span>
				<div class="reportermsg">'.$info['message'].'</div>

				<span class="reporter adminrep">Management directions, '.ago($info['response']).', '.date("d/m/Y H:i:s",$info['response']).'</span>
				<div class="reportermsg adminmsg">'.$info['directions'].'</div>

				</td>

				<td><form method="POST"><input type="hidden" name="reportdone" value="'.$info['id'].'"><input type="submit" onclick="return confirm(\'    '.$info['emailaddress'].': Are you sure youve sent the email off?\');" class="btn btn3 report" style="width:initial;" value="✔️ Ive Sent Email"></form></td>
				</tr>

		';


}



?>
<!DOCTYPE html>
<head>
<title>Admin Assistance</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">

<style type="text/css">

.box23{margin: 66px auto;
    width: 950px;
    background: #fff;
    border-radius: 5px;text-align:left;padding:15px;}

h1{text-align: left;max-width:100%;}

.label{margin-top:35px;}

.container div input, .selectric, .input, .btn {padding: 13px;font-size: 14px;}

.btn{width:100px;text-align:center;}

html{overflow-x: hidden;}

.cke_reset_all{background:#f7f7f7!important;}

.articles{width:100%;}
.articles tr td{border-right:1px solid #ccc;border-bottom:1px solid #000;padding:50px 10px;vertical-align: top}
.articles tr:first-child td{background:#f1f1f1;font-weight:bold;padding:15px 10px;}

.articles a{text-decoration: underline;color:blue;}


.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;}

    .btn{margin: 0!important;}


.adminmenu{display:inline-block;background-color:white;border-top:1px solid #ccc;width:100%;}
.adminmenu a{float:left;padding:15px;}

.reporter{font-size: 14px;}

.reportermsg{padding: 10px;
    background-color: #f5f5f5;color:grey;font-size: 14px;}

.adminrep{margin-top: 28px;
    display: block;}

.adminmsg{background-color:#d2ffb0;color:black;font-size: 17px;}

.report {
    float: left;
    width: initial;
    margin-right: 10px!important;
    border: 1px solid black!important;
}

<?=$styles?>

</style>
</head>

	<body>


		<?=$header?>

		<div class="box23" style="color: grey;margin-bottom: -10px!important;"><span><?=$totalleft?></span></div>

			<div class="box23">

				

				<table class="articles">

				<tr>

					<td>Order</td>
					<td>Initial report + Admin directions</td>
					<td>Sent email?</td>
				</tr>

				<?=$msgs2?>

			</table>
				
			</div>


	</body>
</html>