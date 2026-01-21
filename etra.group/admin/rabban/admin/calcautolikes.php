<?php



include('adminheader.php');

date_default_timezone_set('Europe/London');

$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `freeautolikes_session` != '' AND `freeautolikesexpiringemail` != '3'");

$activefreeal = mysql_num_rows($q);
$costperpost = 0.05;
$totalrevenue = '0';


$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `al_package_id` != '0' AND `cancelbilling` != '3' AND `billingfailure` = ''");
$totalpayingusing = mysql_num_rows($q);


while($info = mysql_fetch_array($q)){


	$totalrevenue = $totalrevenue + $info['price'];


}


echo 'Free Automatic Like users: '.$activefreeal.' users<br>';
echo 'Current cost per month: £'.round($activefreeal * $costperpost * 2.5 * 14).'<br>';
echo 'Current AL active users: '.$totalpayingusing.'<br>';
echo 'Current AL active users revenue per month: £'.round($totalrevenue * 1).'<br>';
echo 'Current AL active users revenue 3-month average: £'.round($totalrevenue * 3).'<br>';
echo 'Profit: £'.($totalrevenue - round($activefreeal * $costperpost * 2.5 * 14)).'<br>';




?>