<?php
require_once 'db.php';
global $currencySign;
$currencySign = $currency;

$type = addslashes($_POST['type']);




switch ($type) {
    case 'checkAdminStats':
        checkAdminStats();
        break;
    case 'updatePackage':
        updatePackage();
        break; 
    case 'getInvoices':
        getInvoices();
        break;    
    case 'selectInvoice':
        selectInvoice();
        break;    
    case 'randomAmount':
        randomAmount();
        break;   
    case 'addUpsell':
        addUpsell();
        break; 
    case 'upsellRemove':
        upsellRemove();
        break;     
}



function checkAdminStats() {


/*


    $data = mysql_fetch_array(mysql_query("SELECT * FROM `admin_statistics` where `type` = 'manualselect'"));
    echo $data['metric'];die;



*/

    $data = mysql_fetch_array(mysql_query("SELECT * FROM `admin_statistics` where `type` = 'apierror' LIMIT 1"));

    if($data['metric'] >= 10){echo '1';}else{echo '0';}





	die;

}

function updatePackage() {	
    $package = addslashes($_POST['package']);	
    $ordersession = addslashes($_POST['orderSession']);	
    mysql_query("UPDATE `order_session` SET 	
                                    `packageid` = '$package'	
                                    WHERE `order_session` = '$ordersession' LIMIT 1");	
    $checkq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$ordersession}' AND `packageid` = '$package' LIMIT 1");	
    $info = mysql_fetch_array($checkq);		
    $packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));   	
    $maxamount = $packageinfo['amount'];	
    $postlimit = $packageinfo['postlimit'];	
    $packagedesc = ucwords($packageinfo['amount'].' '.$packageinfo['type'].' package ');	
    	
    $selectedlist = "";	
    if(!empty($info['chooseposts'])){	
        $chooseposts = explode('~~~', $info['chooseposts']);	
        $postURL = "";	
        foreach($chooseposts as $posts1){	
        	
        $postURL .= $posts1 . ",";	
        	
        if(empty($posts1))continue;	
        	
        	
        $posts2 = explode('###', $posts1);	
        	
        $selectedlist .= '"'.$posts2[0].'###'.$posts2[1].'":"'.$posts2[0].'###'.$posts2[1].'"'.',';}	
        	
        	
        $selectedlist = rtrim($selectedlist,',');	
        	
        $selectedlist = '{'.$selectedlist.'}';	
        	
        $postURL = rtrim($postURL,',');	
        	
        	
        }else{	
        	
        $selectedlist = '{}';	
        	
        }	
        $data = [	
                    "postlimit"     => $postlimit,	
                    "packagedesc"   => $packagedesc,	
                    "maxamount"     => $maxamount,	
                    "selectedlist"  => $selectedlist,	
                    "postURL"       => $postURL	
        ];	
    echo json_encode($data);	
    die;	
}	


function getInvoices(){

    $id = addslashes($_POST['id']);

    $query = mysql_query("SELECT alb.added,al.id,alb.id as billid FROM automatic_likes al 
                                                            INNER JOIN automatic_likes_billing alb 
                                                            ON al.id = alb.auto_likes_id 
                                                            WHERE al.id = $id AND al.brand = 'sv'");

 
    if(mysql_num_rows($query) > 0){
        while($data = mysql_fetch_array($query)){

            $dataArr[] = $data;

        }
    }else{
        echo json_encode(['message' => 'Invoices not found, please contact us']);
        die;
    }
   
    echo json_encode(['data' => $dataArr, 'message' => 'success']);
    die;
}

function selectInvoice(){

    $id = addslashes($_POST['id']);
    $billid = addslashes($_POST['billid']);

    $query = mysql_query("SELECT alb.*,al.payment_billingname_crdi,al.emailaddress,al.country,al.id as orderid FROM automatic_likes al 
                                                            INNER JOIN automatic_likes_billing alb 
                                                            ON al.id = alb.auto_likes_id 
                                                            WHERE alb.auto_likes_id = $id AND alb.id = $billid AND al.brand = 'sv' LIMIT 1");

 
    if(mysql_num_rows($query) > 0){
      $data = mysql_fetch_array($query);
    }else{
        echo json_encode(['message' => 'Invoices not found, please contact us']);
        die;
    }
   
    echo json_encode(['data' => $data, 'message' => 'success']);
    die;

}
function randomAmount()
{

    $min = 25;
    $max = 50;
    $randomNumber = rand($min, $max);
    $formattedNumber = str_pad($randomNumber, 4, '0', STR_PAD_LEFT);

    $id = addslashes($_POST['id']);

    if(!empty($id)){

        $validq = mysql_query("SELECT * FROM `freetrial` WHERE `md5` = '$id' LIMIT 1");
        
        if(mysql_num_rows($validq)=='0'){
            echo json_encode(['message' => 'Invalid Session']);
            die;
        }
        
        $q = mysql_query("UPDATE `freetrial` SET `amount` = '$formattedNumber' WHERE `md5` = '$id' and brand ='sv' LIMIT 1");
        if($q){
            echo json_encode(['data' => $formattedNumber, 'message' => 'success']);
            die;
        }else{
            echo json_encode(['message' => 'Technical Error!!']);
            die;
        }

    }else{
        
         echo json_encode(['message' => 'Try clicking on the link from the email again.']);
         die;
        
    }
}

function addUpsell()
{

    include('ordercontrol.php');
    global $currencySign;
    $upsell_type = addslashes($_POST['upsell_type']);

    $packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$info['packageid']}' LIMIT 1"));
    $discountamount = round($packageinfo['amount'] * 0.50);

    $discountoriginal = number_format(round($packageinfo['price'] * 0.50,2),2);
    $discountactual = number_format(round($discountoriginal * 0.75,2),2);
    $discountamount_follower = round($packageinfo['amount'] * 0.50);

    $discountoriginal_follower = number_format(round($packageinfo['price'] * 0.50,2),2);

    $discountactual_follower = number_format(round($discountoriginal_follower * 0.75,2),2);
    switch ($upsell_type){
        case 'like':
           

            $upselladd = $discountamount.'###'.$discountactual;
            $query = mysql_query("UPDATE `order_session` SET `upsell` = '$upselladd' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");
            if ($query) {

                $discounttitle = "Add <b>$discountamount ". ucwords($packageinfo['type']) ." </b> and <b style='color:#80bd29'>save 25%</b>";

                $discounttitle .= '<div class="tickadded"><span class="tick"><img src="/imgs/check.svg" alt="check"></span><span style="">Added - ready for delivery</span></div><div class="loaderadded"><span class="loader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="30" height="30" style="shape-rendering: auto; display: block; background: transparent;" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-linecap="round" fill="none" stroke-dasharray="39.269908169872416 39.269908169872416" stroke="#fb5343" stroke-width="10" r="25" cy="50" cx="50">
                <animateTransform values="0 50 50;360 50 50" keyTimes="0;1" dur="0.7092198581560283s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
              </circle><g></g></g></svg></span><span>Hang tight, we\'re getting your order ready..</span></div>';
        
                $discountbtn = $currencySign.$discountactual.'<br><a class="remove" onclick="upsellRemove(\'like\')" href="javascript:void(0);">Remove</a>';
        
        
                $summaryupsell1 = '<span class="package ups" style="display: block;margin-top: 13px;color:#008000!important">Additional ' .$discountamount .' '. ucwords($packageinfo['type']) .'</span>';
        
                $summaryupsell2 = '<div class="ups1"> + '. $currencySign.$discountactual .'<a onclick="upsellRemove(\'like\')" href="javascript:void(0);" style="    position: absolute;right: -35px;bottom: 9px;">
        
                <svg id="Capa_1" enable-background="new 0 0 386.667 386.667" viewBox="0 0 386.667 386.667" width="15" height="15" xmlns="http://www.w3.org/2000/svg"><path d="m386.667 45.564-45.564-45.564-147.77 147.769-147.769-147.769-45.564 45.564 147.769 147.769-147.769 147.77 45.564 45.564 147.769-147.769 147.769 147.769 45.564-45.564-147.768-147.77z"/></svg></a></div>';
                sendCloudwatchData('Superviral', 'upsell-'.$upsell_type, 'OrderReview', 'order-review-upsell-'. $upsell_type .'-function', 1);
        
                echo json_encode(['message' => 'success', 'summary1' => $summaryupsell1, 'summary2' => $summaryupsell2, 'discounttitle' => $discounttitle, 'discountbtn' => $discountbtn, 'discountactual' => $discountactual]);
                die;
            } else {
                echo json_encode(['message' => 'Error, try again']);
                die;
            }
        break;
        case 'follower':

            $upselladdfollower = $discountamount_follower.'###'.$discountactual_follower;
        	$query = mysql_query("UPDATE `order_session` SET `upsell_all` = '$upselladdfollower' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");
	
            if($query){

	            $upsellReadyDeliveryMsg = '<div class="tickadded"><span class="tick"><img src="/imgs/check.svg" alt="check"></span><span style="">Added - ready for delivery</span></div><div class="loaderadded"><span class="loader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="30" height="30" style="shape-rendering: auto; display: block; background: transparent;" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-linecap="round" fill="none" stroke-dasharray="39.269908169872416 39.269908169872416" stroke="#fb5343" stroke-width="10" r="25" cy="50" cx="50">
                    <animateTransform values="0 50 50;360 50 50" keyTimes="0;1" dur="0.7092198581560283s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
                    </circle><g></g></g></svg></span><span>Hang tight, we\'re getting your order ready..</span></div>';

                $upsellFollowerBtn = $currencySign.$discountactual_follower.'<br><a class="remove" onclick="upsellRemove(\'follower\')" href="javascript:void(0);">Remove</a>';

                $upsellSubTotal = '<div class="thewidthleft" style="padding-top: 0px;"><span class="package ups" style="display: block;color:#008000!important">Additional '. $discountamount_follower .' Followers</span></div>

                <div class="thewidthright" style="padding-top: 0px;">
                <div class="ups1" style="    padding-top: 0px;">+ '. $currencySign .$discountactual_follower.'<a onclick="upsellRemove(\'follower\')" href="javascript:void(0);"
                style="    position: absolute;right: -35px;bottom: 9px;">

                <svg id="Capa_1" enable-background="new 0 0 386.667 386.667" viewBox="0 0 386.667 386.667" width="15"
                height="15" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="m386.667 45.564-45.564-45.564-147.77 147.769-147.769-147.769-45.564 45.564 147.769 147.769-147.769 147.77 45.564 45.564 147.769-147.769 147.769 147.769 45.564-45.564-147.768-147.77z">
                </path>
                </svg></a>
                </div>
                </div>';
                sendCloudwatchData('Superviral', 'upsell-'.$upsell_type, 'OrderReview', 'order-review-upsell-'. $upsell_type .'-function', 1);

                echo json_encode(['message' => 'success', 'upsellReadyDeliveryMsg' => $upsellReadyDeliveryMsg, 'upsellFollowerBtn' => $upsellFollowerBtn, 'upsellSubTotal' => $upsellSubTotal, 'discountactual_follower' => $discountactual_follower]);
                die;

            }else{

            }
        break;
        // case 'autolike':

        //     $upselladd_autolikes = $discountamount2.'###'.$discountactual2.'###'.$auto_likes['likes_per_post'].'###'.$auto_likes['max_per_day'].'###'.$auto_likes['price'].'###'.$auto_likes['original_price'].'###'.$auto_likes['save'];
        //     mysql_query("UPDATE `order_session` SET `upsell_autolikes` = '$upselladd_autolikes' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");

        // break;
    }

  
}

function upsellRemove()
{

    include('ordercontrol.php');
    global $currencySign;
    $upsell_type = addslashes($_POST['upsell_type']);

    $packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$info['packageid']}' LIMIT 1"));
    $discountamount = round($packageinfo['amount'] * 0.50);

    $discountoriginal = number_format(round($packageinfo['price'] * 0.50,2),2);
    $discountactual = number_format(round($discountoriginal * 0.75,2),2);
    $discountamount_follower = round($packageinfo['amount'] * 0.50);

    $discountoriginal_follower = number_format(round($packageinfo['price'] * 0.50,2),2);

    $discountactual_follower = number_format(round($discountoriginal_follower * 0.75,2),2);

    switch ($upsell_type){
        case 'like':

            $query = mysql_query("UPDATE `order_session` SET `upsell` = '' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");
           
            if ($query) {

                $discounttitle = "Add <b>$discountamount ". ucwords($packageinfo['type']) ." </b> and <b style='color:#80bd29'>save 25%</b>";

	            $discountbtn = '<a class="btn-upsell btn greenbtn gtm-click" onclick="addUpsell(\'like\')" href="javascript:void(0);" data-click-name="upsell add likes"> + Add for '. $currencySign .$discountactual . ' <strike>' . $currencySign. $discountoriginal .'</strike></a>';
        
        
                echo json_encode(['message' => 'success', 'discounttitle' => $discounttitle, 'discountbtn' => $discountbtn, 'discountactual' => $discountactual]);
                die;
            } else {
                echo json_encode(['message' => 'Error, try again']);
                die;
            }
        break;
        case 'follower':

        	$query = mysql_query("UPDATE `order_session` SET `upsell_all` = '' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");
            if ($query) {

                $discounttitle = "Add <b>$discountamount_follower Followers </b> and <b style='color:#80bd29'>save 25%</b>";

                $upsellFollowerBtn = '<a class="btn greenbtn gtm-click" data-click-name="upsell add followers" onclick="addUpsell(\'follower\')" href="javascript:void(0);">+ Add for '.$currency.$discountactual_follower.' <strike>$'. $discountoriginal_follower .'</strike></a>';

                echo json_encode(['message' => 'success', 'discounttitle' => $discounttitle, 'upsellFollowerBtn' => $upsellFollowerBtn, 'discountactual_follower' => $discountactual_follower]);
                die;
            } else {
                echo json_encode(['message' => 'Error, try again']);
                die;
            }
        break;
        // case 'autolike':

        //     $upselladd_autolikes = $discountamount2.'###'.$discountactual2.'###'.$auto_likes['likes_per_post'].'###'.$auto_likes['max_per_day'].'###'.$auto_likes['price'].'###'.$auto_likes['original_price'].'###'.$auto_likes['save'];
        //     mysql_query("UPDATE `order_session` SET `upsell_autolikes` = '$upselladd_autolikes' WHERE `order_session` = '{$info['order_session']}' AND `brand`='sv' LIMIT 1");

        // break;
    }

 
    if ($query) {
        echo json_encode(['message' => 'success']);
        die;
    } else {
        echo json_encode(['message' => 'Error, try again']);
        die;
    }

    
}

?>	


