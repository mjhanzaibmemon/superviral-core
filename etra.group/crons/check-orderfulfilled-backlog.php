<?php
include('../sm-db.php');
include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/messagebird/autoload.php';

$sendsms = $_GET['sendsms'];

//########## orders

$q = mysql_query("SELECT id, added, country, packagetype, packageid, fulfill_id, fulfill_attempt, order_session, emailaddress, igusername, supplier_errors FROM `orders` WHERE `fulfill_id` != '' AND `fulfilled` = '0' AND `defect` = '0' AND `refund` = '0' AND `added` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 WEEK)) ORDER BY `orders`.`id` ASC");

$count = mysql_num_rows($q);

$tbl = '<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">{header}';
$tbl_header_done=0;
while($row = mysql_fetch_array($q)){
    
    $tbl.='<tr>';
    foreach($row as $k => $v){
        if(!$tbl_header_done){$tbl_header.='<th style="text-align:left;background:black;color:white;padding:5px;">'.$k.'</th>';}

        if($k == 'added'){$v = $dateFormat = date("d M Y H:i:s", $v);}
        $tbl.='<td style="border: 1px solid #ddd;padding:5px;">'.$v.'</td>';
    }
    $tbl.='</tr>';
    $tbl_header_done = 1;
}
$tbl.= '</table>';

$tbl = str_replace('{header}',$tbl_header,$tbl);

$qSum = mysql_query("SELECT packagetype, COUNT(*) AS count  FROM `orders` 
                        WHERE `fulfill_id` != '' AND `fulfilled` = '0' 
                        AND `defect` = '0' AND `refund` = '0' GROUP BY packagetype");

$tblSumry = '<br><table style="width: 25%; border-collapse: collapse; border: 1px solid #ddd;">
<tr><th style="text-align:left;background:black;color:white;padding:5px;">PackageType</th><th style="text-align:left;background:black;color:white;padding:5px;">Count</th></tr>';
while($row = mysql_fetch_array($qSum)){
    
    $tblSumry.='<tr>';
    foreach($row as $k => $v){

        $tblSumry.='<td style="border: 1px solid #ddd;padding:5px;">'.$v.'</td>';
    }
    $tblSumry.='</tr>';
}
$tblSumry.= '</table>';


if(($count > 1200)&&($sendsms=='true')){

    $MessageBird = new \MessageBird\Client($messagebirdclient);
    $Message = new \MessageBird\Objects\Message();
    $Message->originator = 'SUPERVIRAL';
    $Message->recipients = array($hacontactnumber); // number need to check

    $Message->body = 'Too much backlog on Orders table. Emails and texts are not being sent. View Backlog: https://etra.group/crons/check-orderfulfilled-backlog.php https://etra.group/crons/check-orderfulfilled.php?speedrun=true';

    $MessageBird->messages->create($Message);

    if($MessageBird){echo 'Text Message Sent to '.$hacontactnumber.' !<br>';}


}

// orders free:

$orderfree = mysql_query("SELECT id,added,country,packagetype,packageid,fulfill_id,fulfill_attempt,igusername,supplier_errors FROM `orders_free` WHERE `fulfill_id` = '' AND `added` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 WEEK))  LIMIT 100");

$of_count = mysql_num_rows($orderfree);

$oftbl = '<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">{header}';
$tbl_header_done=0;
$tbl_header = "";
while($row = mysql_fetch_array($orderfree)){
    
    $oftbl.='<tr>';
    foreach($row as $k => $v){
        if(!$tbl_header_done){$tbl_header.='<th style="text-align:left;background:black;color:white;padding:5px;">'.$k.'</th>';}

        if($k == 'added'){$v = $dateFormat = date("d M Y H:i:s", $v);}
        $oftbl.='<td style="border: 1px solid #ddd;padding:5px;">'.$v.'</td>';
    }
    $oftbl.='</tr>';
    $tbl_header_done = 1;
}
$oftbl.= '</table>';

$oftbl = str_replace('{header}',$tbl_header,$oftbl);

$ofSum = mysql_query("SELECT packagetype, COUNT(*) AS count FROM `orders_free` WHERE `fulfill_id` = '' GROUP BY packagetype");

$oftblSumry = '<br><table style="width: 25%; border-collapse: collapse; border: 1px solid #ddd;">
<tr><th style="text-align:left;background:black;color:white;padding:5px;">PackageType</th><th style="text-align:left;background:black;color:white;padding:5px;">Count</th></tr>';
while($row = mysql_fetch_array($ofSum)){
    
    $oftblSumry.='<tr>';
    foreach($row as $k => $v){

        $oftblSumry.='<td style="border: 1px solid #ddd;padding:5px;">'.$v.'</td>';
    }
    $oftblSumry.='</tr>';
}
$oftblSumry.= '</table>';


if(($of_count > 1200)&&($sendsms=='true')){

    $MessageBird = new \MessageBird\Client($messagebirdclient);
    $Message = new \MessageBird\Objects\Message();
    $Message->originator = 'SUPERVIRAL';
    $Message->recipients = array($hacontactnumber); // number need to check

    $Message->body = 'Too much backlog on Orders Free table. Emails and texts are not being sent. View Backlog: https://etra.group/crons/check-orderfulfilled-backlog.php https://etra.group/crons/check-orderfulfilled.php?speedrun=true';

    $MessageBird->messages->create($Message);

    if($MessageBird){echo 'Text Message Sent to '.$hacontactnumber.' !<br>';}


}



//########## automatic likes

$q2 = mysql_query("SELECT id,account_id,emailaddress,igusername,payment_id,expires,last_updated,lastbilled,nextbilled,cardexpiringtime,expiredemail FROM `automatic_likes` WHERE 
      `cancelbilling` != '3' AND 
      `nextbilled` < ".time()." AND 
      `nextbilled` != '0' AND
      `recurring` = '1' AND
      `billingfailure` = ''
      ORDER BY last_updated ASC
");

$count2 = mysql_num_rows($q2);

$tbl2 = '<style>tr:hover{background:#ececec;}</style><table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">{header}';
$tbl2_header_done=0;
while($row = mysql_fetch_array($q2)){
    
    $tbl2.='<tr>';
    foreach($row as $k => $v){
        if(!$tbl2_header_done){$tbl2_header.='<th style="text-align:left;background:black;color:white;padding:5px;">'.$k.'</th>';}

        if(in_array($k,['expires','last_updated','lastbilled','nextbilled','cardexpiringtime'])){$v = $dateFormat = date("d M Y H:i:s", $v);}
        $tbl2.='<td style="border: 1px solid #ddd;padding:5px;white-space:nowrap">'.$v.'</td>';
    }
    $tbl2.='</tr>';
    $tbl2_header_done = 1;
}
$tbl2.= '</table>';
$tbl2 = str_replace('{header}',$tbl2_header,$tbl2);

if(($count2 > 100)&&($sendsms=='true')){

    $MessageBird = new \MessageBird\Client($messagebirdclient);
    $Message = new \MessageBird\Objects\Message();
    $Message->originator = 'SUPERVIRAL';
    $Message->recipients = array($hacontactnumber); // number need to check

    $Message->body = 'Too much backlog on Auomatic Likes table. Recurring payment may not be working properly. View backlog: https://etra.group/crons/check-orderfulfilled-backlog.php';

    $MessageBird->messages->create($Message);

    if($MessageBird){echo 'Text Message Sent to '.$hacontactnumber.' !<br>';}


}


//##### tracking update checkorderfulfilled
$time = time();
$timeafterhours = time() - (6000);
$q3 = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` != '' AND `fulfilled` = '0' AND `defect` = '0' AND `refund` = '0' AND `lastchecked` < $timeafterhours");

$count3 = mysql_num_rows($q3);

$tbl3 = '<style>tr:hover{background:#ececec;}</style><table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">{header}';
$tbl3_header_done=0;
while($row = mysql_fetch_array($q2)){
    
    $tbl3.='<tr>';
    foreach($row as $k => $v){
        if(!$tbl3_header_done){$tbl3_header.='<th style="text-align:left;background:black;color:white;padding:5px;">'.$k.'</th>';}

        if(in_array($k,['expires','last_updated','lastbilled','nextbilled','cardexpiringtime'])){$v = $dateFormat = date("d M Y H:i:s", $v);}
        $tbl2.='<td style="border: 1px solid #ddd;padding:5px;white-space:nowrap">'.$v.'</td>';
    }
    $tbl3.='</tr>';
    $tbl3_header_done = 1;
}
$tbl3.= '</table>';
$tbl3 = str_replace('{header}',$tbl3_header,$tbl3);

if(($count3 > 100)&&($sendsms=='true')){

    $MessageBird = new \MessageBird\Client($messagebirdclient);
    $Message = new \MessageBird\Objects\Message();
    $Message->originator = 'SUPERVIRAL';
    $Message->recipients = array($hacontactnumber); // number need to check

    $Message->body = 'Too much backlog for tracking. Tracking may not be working properly. Run tracking manually: https://etra.group/crons/checkorderfulfilled.php?speedrun=true';

    $MessageBird->messages->create($Message);

    if($MessageBird){echo 'Text Message Sent to '.$hacontactnumber.' !<br>';}


}


//########## DISPLAY TABLES

echo "
    <style>tr:hover{background:#ececec;}th{position:sticky;top:0;}</style>

    <div style='font-size:30px'>
    <a href='#orders'>Orders Count:</a> " .$count."<hr>
    <a href='#orders_free'>Orders Free Count:</a> " .$of_count."<hr>
    <a href='#al'>AL Count:</a> " .$count2."<hr>
    <a href='#tracking'>Tracking Count:</a> " .$count2."<hr>
    </div>

    <h1 id='orders'>Orders Data from last 2-weeks</h1>".$tbl. $tblSumry ."
    <br><br>
    <h1 id='orders_free'>Orders Free Data</h1>".$oftbl. $oftblSumry ."
    <br><br>
    <h1 id='al'>AL Data</h1>".$tbl2."
    <br><br>
    <h1 id='tracking'>Tracking Data</h1>".$tbl3."

";


?>
