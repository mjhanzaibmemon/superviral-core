<?php

$dbmain=1;
include('adminheader.php');
$now = time();

date_default_timezone_set('Europe/London');

$orderids = addslashes($_POST['orderids']);

if(!empty($orderids)){



$orderids = explode(' ', $orderids);

foreach($orderids as $perid){
    $perid = str_replace(' ','',$perid);
    //if(empty($perid))continue;
	if(!ctype_digit($perid))continue;


    $now = time();

    $perid = trim($perid);

$insertadminq =	mysql_query("INSERT INTO `admin_failed_orders` SET 
		`orderid` = '$perid',
		`added` = '$now'
		");

$getorderupdateq = mysql_query("SELECT * FROM `orders` WHERE `id` = '$perid' LIMIT 1");
$getorderupgradeinfo = mysql_fetch_array($getorderupdateq);

$oldupdate = $getorderupgradeinfo['order_response'];
$newupdate = $oldupdate.'~~~'.$now.'###Order has experienced some delays. Rest assured, our fulfillment team are manually delivering your order ASAP with algorithm #53 (high priority).###0.2';

$updateorderq = mysql_query("UPDATE `orders` SET `order_response` = '$newupdate' WHERE `id` = '$perid' LIMIT 1");


}


if($insertadminq){$message = '<div class="message" style="margin-bottom:20px;">System updated for these slow orders!</div>';}

}

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

class Api
{


    public function setApiKey( $value ){$this->api_key = $value;}
    public function setApiUrl( $value ){$this->api_url = $value;}

    public function order($data) { // add order
        $post = array_merge(array('key' => $this->api_key, 'action' => 'add'), $data);
        return json_decode($this->connect($post));
    }

    public function status($order_id) { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'status',
            'order' => $order_id
        )));
    }

    public function multiStatus($order_ids) { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'status',
            'orders' => implode(",", (array)$order_ids)
        )));
    }

    public function services() { // get services
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'services',
        )));
    }

    public function balance() { // get balance
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'balance',
        )));
    }


    private function connect($post) {
        $_post = Array();
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name.'='.urlencode($value);
            }
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (is_array($post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        curl_close($ch);
        return $result;
    }
}


$api = new Api();

$api->setApiKey($fulfillment_api_key);
$api->setApiUrl($fulfillment_url);


$now = time();

//LOAD EXISTING ADMIN FAILED ORDERS DATA
$twodaysago = $now - 259200;

$failedordersq = mysql_query("SELECT * FROM `admin_failed_orders` WHERE `added` BETWEEN '$twodaysago' AND '$now'");

if(mysql_num_rows($failedordersq)!=='0'){


$i = 0;
while($failoredorerdshistory = mysql_fetch_array($failedordersq)){

$orderid = $failoredorerdshistory['orderid'];

//LOAD INTO ARRAY
$failedorderarray[$orderid] = '1';
$i++;

}

}

//SHOW NO DEFECT ORDERS AND HAS TO HAVE A FULFILL ID
$slowq = mysql_query("SELECT * FROM `orders` WHERE `fulfilled` = '0' AND `defect` = '0' AND `fulfill_id` REGEXP '[0-9]' AND `added`  < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 3 DAY)) ORDER BY `id` DESC LIMIT 1500");

$i = 1;
$t = 1;
if(mysql_num_rows($slowq)!=='0'){

		while($slowinfo = mysql_fetch_array($slowq)){

		//IF ORDER IS LONGER THAN 2 DAYS, THEN SHOW IT, IF NOT THEN CONTINUE

            //THIS IS FOR CROSS REFERENCING WITH OUR ARRAY
            $slowinfoid2 = $slowinfo['id'];

            if($failedorderarray[$slowinfoid2]=='1')continue;

			$groupdate = date("l j/n/Y",$slowinfo['added']);

			$sloworders[$i]['info'] = '<div>#'.$slowinfo['id'].' - Added: '.date("l j/n/Y",$slowinfo['added']).' at '.date("G:i a",$slowinfo['added']).' - Supplier Fulfill ID:'.$slowinfo['fulfill_id'].' - Fulfilled: '.$slowinfo['fulfill_id'].'</div>';

			$sloworders[$i]['fulfill_id'] = $slowinfo['fulfill_id'];
			$sloworders[$i]['id'] = $slowinfo['id'];

			$sloworders[$i]['groupdate'] = $groupdate;
			$sloworders[$i]['added'] = $slowinfo['added'];


			$i++;

			//$sloworders[$groupdate]['fulfill_id'] = $slowinfo['fulfill_id'];

			$slowordergroups[] = $groupdate;




		}

}

else{





}

$o = 0;

$slowordergroups = array_unique($slowordergroups);

//HIGHLIGHT FIRST
$highlight = 0;

foreach($slowordergroups as $value){

$c = 0;

$highlight++;

foreach($sloworders as $sloworderkey => $slowordervalue){

	if($sloworders[$sloworderkey]['groupdate']==$value){//IF THERES A MATCH FOR THIS GROUPED DATE THEN ASSIGN IT TO THIS GROUP

        //OLDER THAN 5-DAYS -> THEN GROUP IT
        if(($now - 432000) > $sloworders[$sloworderkey]['added']){ 

        $sloworderresults1 .= $sloworders[$sloworderkey]['info'].'<br>';
        $showfulfill_id1 .= $sloworders[$sloworderkey]['fulfill_id'].' ';
        $slowordersadded1 = $sloworders[$sloworderkey]['added'];
        $slowordersids1 .= $sloworders[$sloworderkey]['id'].' ';
        $o++;
        }

        else{//PUT I IN GROUP AS ITS LESS THAN 5-DAYS

		$sloworderresults .= $sloworders[$sloworderkey]['info'].'<br>';
		$showfulfill_id .= $sloworders[$sloworderkey]['fulfill_id'].' ';
		$slowordersadded = $sloworders[$sloworderkey]['added'];
		$slowordersids .= $sloworders[$sloworderkey]['id'].' ';
		
        $c++;
        }


	}

}

$slowordersids = str_replace('  ',' ',$slowordersids);

if($highlight==1){$highlightthis = 'highlightthis';}

if($c==0)continue;//IF theres nothing in this group, then continue

$slowordershow .= '<div class="groupbydate '.$highlightthis.'"><a class="openresults" class="">Orders from: <b>'.$value.'</b> ('.$c.') <font style="font-size:15px;float:right;">(open/show)</font></a><hr>
<div class="showorderresults">'.$sloworderresults.'</div>
<div class="showorderfulfill_id">Needs to be reported to supplier:

<div class="foo" >
<textarea class="language-less">'.str_replace('  ',' ',$showfulfill_id).'</textarea>
<button class="btn btn3 report copy-button">Copy fulfill Ids</button>
</div>

<div class="foo" >
<textarea class="language-less">Please speed up these orders. The orders were made '.ago($slowordersadded).' and customers are complaining.</textarea>
<button class="btn btn3 report copy-button">Copy message for supplier</button>
</div>

<form method="POST">
<input type="hidden" name="orderids" value="'.$slowordersids.'">
<input type="submit" onclick="return confirm(\'Are you sure youve sent this message off?\');" name="submit" value="✔️ Ive Sent This Message" class="btn btn3 report" style="right:0;position:absolute;bottom:-5px;bottom: 7px;"></form>

		</div>

</div>';

unset($sloworderresults);
unset($showfulfill_id);
unset($slowordersids);
unset($c);


if($highlight==1){unset($highlightthis);}
}


if($o!==0){

$slowordershow .= '<div class="groupbydate highlightthis"><a class="openresults" class="">Orders from <b>over 5-days ago</b> ('.$o.') <font style="font-size:15px;float:right;">(open/show)</font></a><hr>
<div class="showorderresults">'.$sloworderresults1.'</div>
<div class="showorderfulfill_id">Needs to be reported to supplier:

<div class="foo" >
<textarea class="language-less">'.str_replace('  ',' ',$showfulfill_id1).'</textarea>
<button class="btn btn3 report copy-button">Copy fulfill Ids</button>
</div>

<div class="foo" >
<textarea class="language-less">Please speed up these orders. The orders were made '.ago($slowordersadded1).' and customers are complaining.</textarea>
<button class="btn btn3 report copy-button">Copy message for supplier</button>
</div>

<form method="POST">
<input type="hidden" name="orderids" value="'.$slowordersids1.'">
<input type="submit" onclick="return confirm(\'Are you sure youve sent this message off?\');" name="submit" value="✔️ Ive Sent This Message" class="btn btn3 report" style="right:0;position:absolute;bottom:-5px;bottom: 7px;"></form>

        </div>

</div>';

}

?>
<!DOCTYPE html>
<head>
<title>Slow Orders</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">

<style type="text/css">



.articles a{text-decoration: underline;color:blue;}

.box23{margin: 66px auto;
    width: 950px;
    background: #fff;
    border-radius: 5px;text-align:left;padding:15px;}

h1{text-align: left;max-width:100%;}

h2{font-size:22px;}

.label{margin-top:35px;}

.container div input, .selectric, .input, .btn {padding: 13px;font-size: 14px;}

.btn{width:100px;text-align:center;}

html{overflow-x: hidden;}

.cke_reset_all{background:#f7f7f7!important;}

.articles{width:100%;}
.articles tr td{border-right:1px solid #ccc;border-bottom:1px solid #000;padding: 30px 10px;vertical-align: top;}
.articles tr:first-child td{background:#f1f1f1;font-weight:bold;padding:10px;}

.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;}

    .btn{margin: 0!important;}


.adminmenu{display:inline-block;background-color:white;border-top:1px solid #ccc;width:100%;}
.adminmenu a{float:left;padding:15px;}

 .reportmessage{
    width: 100%;
    height: 120px;box-sizing:border-box;
    margin: 0px;
    margin-bottom: 20px;
    padding:10px;font-family:'Open Sans';

    overflow: hidden;
    outline: none;
    resize:none;
}

   .btn{width: initial;}

   .defectorder{}

   .defectorder a{text-decoration: underline;color:blue;}

   .defectorder .sdiv{    margin-bottom: 15px;
    padding: 9px;
    background-color: #f5f7fe;
    border-radius: 5px;}

    .nlbtn{    float: left;
    margin-right: 20px!important;
    width: 176px!important;
    margin-bottom: 10px!important;}

    .showorderresults{display:none;}

    .showslow .showorderresults{display: block;
    background-color: #f2f5ff;
    padding: 15px;    font-size: 14px;}

.foo{    display: inline-block;
    width: 100%;
    margin-bottom: 18px;}

.language-less{width:1px;height:1px;resize: none;}

.report {
    width: initial;
    margin-right: 10px!important;
    border: 1px solid black!important;
}

.showorderfulfill_id{position:relative;}

    .message{padding: 9px;
    text-align: center;
    background-color: #3ed45c;
    color: #fff;}

.groupbydate{border:1px solid grey;padding:10px;margin-bottom:20px;opacity:0.5;}
.highlightthis{border:2px solid black!important;opacity:1;}

<?=$styles?>

</style>
</head>

	<body onload="init();">


		<?=$header?>

		<div class="box23">

			<?=$message?>

			<h1>Slow Orders</h1>
			<span style="display:block;margin-bottom:15px;">These are non failed orders that are taking too long to fulfill</span>

			<?=$slowordershow?>

		</div>


		 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		 <script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>

        <script type="text/javascript">

        $(".openresults").click(function() {
  
  var $target = $(this).parent().toggleClass("showslow");
  
  $(".faq").not($target).removeClass('showslow');

});


 (function(){

	// Get the elements.
	// - the 'pre' element.

	
	var pre = document.getElementsByClassName('foo');
	

	// Add a copy button in the 'pre' element.
	// which only has the className of 'language-'.
	
	for (var i = 0; i < pre.length; i++) {
		var isLanguage = pre[i].children[0].className.indexOf('language-');
		
		/*
		if ( isLanguage === 0 ) {
			var button           = document.createElement('button');
					button.className = 'copy-button';
					button.textContent = 'Copy';

					pre[i].appendChild(button);
		}*/
	};
	
	// Run Clipboard
	
	var copyCode = new Clipboard('.copy-button', {
		target: function(trigger) {
			return trigger.previousElementSibling;
    }
	});

	// On success:
	// - Change the "Copy" text to "Copied".
	// - Swap it to "Copy" in 2s.
	// - Lead user to the "contenteditable" area with Velocity scroll.
	
	copyCode.on('success', function(event) {
		event.clearSelection();
		event.trigger.textContent = 'Copied';
		window.setTimeout(function() {
			event.trigger.textContent = 'Copy';
		}, 10000);

	});

	// On error (Safari):
	// - Change the  "Press Ctrl+C to copy"
	// - Swap it to "Copy" in 2s.
	
	copyCode.on('error', function(event) { 
		event.trigger.textContent = 'Press "Ctrl + C" to copy';
		window.setTimeout(function() {
			event.trigger.textContent = 'Copy';
		}, 5000);
	});

})();


        </script>


	</body>
</html>