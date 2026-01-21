<?php

include 'adminheader.php';

$thisstaffmember = addslashes($_SESSION['admin_user']);
$date_of_absence = date('d-m-Y', time());

$checkstaffq = mysql_query("SELECT * FROM `staff_absence` WHERE `staff` = '$thisstaffmember' AND `date_of_absence` = '$date_of_absence' LIMIT 1");

if (mysql_num_rows($checkstaffq) == 1) {

    $dontdosupportcss = '

	body{background:#fbb!important;}

	.box23{display:none!important;}

	';

    $dontdosupportdiv = '<div style="    width: 100%;
    text-align: center;
    padding-top: 15px;
    font-size: 28px;">Unfortunately, the time limit to start support was missed today,<br>
    please resume support on your next working day.</div>';

}

date_default_timezone_set('Europe/London');

function ago($time)
{$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
    $now = time();
    $difference = $now - $time;
    $tense = 'ago';
    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
        $difference /= $lengths[$j];
    }
    $difference = round($difference);
    if ($difference != 1) {
        $periods[$j] .= "s";
    }
    return "$difference $periods[$j] ago";}

$user = addslashes(trim($_POST['user']));
$unsubscribeID = addslashes($_POST['unsubscribeID']);
$Message = "";
// if(empty($orderid))$orderid = addslashes(trim($_GET['orderid']));
if (empty($user)) {
    $user = addslashes(trim($_GET['user']));
}

if (!empty($unsubscribeID)) {
    mysql_query("UPDATE users
    SET unsubscribe= CASE
       WHEN unsubscribe=1 THEN 0
       WHEN unsubscribe =0 THEN 1
    END
    where id ='". $unsubscribeID ."' LIMIT 1"); 
    $Message = '<div class="emailsuccess">Done Successfully</div>';
}

if (!empty($user)) {$q = mysql_query("SELECT * FROM `users` WHERE `emailaddress` LIKE '%$user%' ORDER BY `id` DESC");
    $field = '<input type="hidden" name="user" value="' . $user . '">';}


if ($q) { 

    while ($info = mysql_fetch_array($q)) {

        $results .= '

            <div class="box23">

            <table id="mailList' . $info['id'] . '" class="perorder">

                <tr><td>Mail list #' . $info['id'] . '</td><td></td></tr>
                <tr><td>#️⃣ Mail List ID: </td><td>' . $info['id'] . '</td></tr>
                <tr><td>📧 Email address: </td><td><a href=' . $siteDomain . '/admin/check-mail-list.php?user=' . $info['emailaddress'] . '>' . $info['emailaddress'] . '</a></td></tr>
                <tr><td>📦 Source: </td><td>' . $info['source'] . '</td></tr>
                <tr><td>⌚ Date Added: </td><td>' . date('l jS \of F Y H:i:s ', $info['added']) . '</td></tr>
                <tr><td></td>
                <td> ';
if($info["unsubscribe"] == 0){
  $results .=  '<form href="' . $siteDomain . '/admin/check-mail-list.php" method ="POST"><input type="hidden" name="unsubscribeID" value="'. $info['id'] .'"><button class="btn btn3 report"  onclick="return confirm(\'Are you sure you want to unsubscribe?\');" >Unsubscribe</button></form>';
}else{
    $results .= '<form href="' . $siteDomain . '/admin/check-mail-list.php" method ="POST"><input type="hidden" name="unsubscribeID" value="'. $info['id'] .'"><button class="btn btn3 report"  onclick="return confirm(\'Are you sure you want to subscribe?\');" >Subscribe</button></form>';
}
                
         $results .= '</td></tr>
            </table>

            </div>';

    }

    if (!empty($results)) {
        $results = '' . $results . '';
    }

}
    if(!empty($user) && mysqli_num_rows($q) < 1)
    $Message = '<div class="emailsuccess emailfailed">No Mail List Found</div>';


?>
<!DOCTYPE html>
<head>
<title>Check Mail List</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<style type="text/css">

<?=$dontdosupportcss?>

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
.articles tr td{border-bottom:1px solid #f1f1f1;padding: 19px 10px;vertical-align: top}
.articles tr td:first-child{    font-size: 19px;
    width: 34%;
    vertical-align: middle;}
.articles tr:last-child td{border-bottom: 0;}

.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;}

    .btn{margin: 0!important;}


.adminmenu{display:inline-block;background-color:white;border-top:1px solid #ccc;width:100%;}
.adminmenu a{float:left;padding:15px;}

.perorder{width:100%;}
.perorder tr:first-child td{background-color:#ccc;font-weight: bold;font-size:20px;}
.perorder tr td:first-child{width:30%;vertical-align: top;}
.perorder tr td{padding:14px 5px;border-bottom:1px solid #e0e0e0;}
.perorder tr.grey td{color:grey;}

.perorder a{text-decoration: underline;color:blue;}

.trackinginfo{border-bottom: 1px dashed #e8e8e8;
    margin-bottom: 2px;
    padding: 11px;
    font-size: 14px;
    color: grey;}
   .trackinginfo .trackingheader{font-weight:bold;}

.report{float: left;
    width: initial;
    margin-right: 10px!important;border:1px solid black!important;color:black!important;text-decoration:none!important;}

 .reportmessage{float: left;
    width: 100%;
    height: 120px;box-sizing:border-box;
    margin: 0px;
    margin-bottom: 20px;
    resize: vertical;padding:10px;font-family:'Open Sans';}

.adminnotif{    font-size: 15px;
    padding: 11px;margin-bottom:10px;}

.language-less{width:1px;height:1px;resize: none;}

.foo{    display: inline-block;
    width: 100%;
    margin-bottom: 18px;}

.rectifyinput{width: 181px;
    float: left;
    margin-top: 0;
    margin-right: 10px;}

.summarytbl{font-size:14px;}
.summarytbl tr:hover{background-color:#e4fbff;}
.summarytbl tr td{    border-bottom: 1px solid #dadada;
    padding: 7px;}

.searchspan{    font-size: 13px;
    color: #4747bf;
    line-height: 22px;
    display: block;}

<?=$styles?>

</style>
</head>

	<body>


		<?=$header?>


		<h1 style="text-align:center;margin-top:35px;">Check Mail List 📦</h1>


		<?=$dontdosupportdiv?>

		<div class="box23">


            <?php echo $Message  ?>
			<form method="POST" action="#">
			<table class="articles">

				<tr>

					<td>👨👩 Mail List:<span class="searchspan">Search by:<br>- Email address</span></td>
					<td><input name="user" class="input" value="<?=$user?>" autocomplete="off"></td>

				</tr>

				<tr>

					<td></td>
					<td><input style="float:left;" type="submit" name="submit" class="btn color3" value="Search"><a href='<?=$siteDomain?>/admin/check-mail-list.php' class="btn btn3 report" style="float:right;">Reset Search</a>

				</tr>

			</table>

			</form>




		</div>

<?=$summaryresults?>
<?=$results?>


	</body>
</html>