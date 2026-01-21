<?php

$db=1;
include('header.php');

include('orderfulfillraw.php');

/////////////////////////// REFRESH + SEND GA EVENT


$sendgaevent = "
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id=G-C18K306XYW\"></script>  
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-41728467-8');
 gtag('config', 'G-C18K306XYW'); 

gtag('event', 'Details', {
  'event_category': 'Account',
  'event_label': 'Signed Up'
});
</script>
";

$therefresh = '<script>window.top.location.reload();</script>';



//////////////////////////


      $pkgType = trim(addslashes($_POST['pkgType']));
      $uName = addslashes($_POST['username']);

      $uName = trim($uName);
      $uName = str_replace('@','',$uName);
      $uName = str_replace(' ','_',$uName);


      $orderId = addslashes($_GET['uid']);
      $hash = addslashes($_GET['hash']);

      if(!empty(addslashes($_POST['submit']))){


        if($pkgType == "followers"){
            if(empty($uName))$msg = 'Please enter your username.';

            if((!empty($uName))){
                    mysql_query("UPDATE orders SET igusername ='$uName' WHERE id = $orderId AND order_session='$hash' LIMIT 1");

            }
        }

        
        $q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderId' AND order_session='$hash' AND `added` > '1667475464' AND defect !=0 AND fixedbyuser = '0' ORDER BY `id` DESC LIMIT 1");
        if(mysql_num_rows($q)=='0')
        {
          $msg = "Order Not Found";
        }else{

        $info = mysql_fetch_array($q);
    
          mysql_query("UPDATE orders SET defect = 0, fixedbyuser = 1 WHERE id = $orderId AND order_session='$hash' limit 1");
    
          /////////////////////////FULFILLMENT CODE

            $packagefetchq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1");
            $packageinfo = mysql_fetch_array($packagefetchq);

            $japid = 'jap1';
        

            if($info['packagetype']=='followers'){


                $orderid = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://instagram.com/'.$uName, 'quantity' => $packageinfo['amount']));

                $orderid = $orderid -> order;

                if($orderid){
                    $updatefulfillid = mysql_query("UPDATE `orders` SET `fulfill_id` = '$orderid',`igusername` = '$uName' WHERE `id` = '{$info['id']}' ORDER BY `id` DESC LIMIT 1");
                }

            }


            if(($info['packagetype']=='likes')||($info['packagetype']=='views')){


                //DETECT FULFILLS OR JUST ONE
                $chooseposts = $info['chooseposts'];
                $fulfillids = $info['fulfill_id'];

                if (strpos($fulfillids, ' ') !== false) 
                        {

                        //SPACE MEANS THERES MORE THAN ONE POST SELECTED FOR THIS ORDER
                        
                        $choosepostsarray = explode(' ', $chooseposts);
                        $choosepostsarray = array_filter($choosepostsarray);
                        $totalposts = count($choosepostsarray);


                        $fulfillidsarray = explode(' ', $fulfillids);
                        $fulfillidsarray = array_filter($fulfillidsarray);

                        $checkfulfillids = $api->multiStatus($fulfillidsarray);

                        $checkfulfillids = json_decode(json_encode($checkfulfillids), True);

                        $i = 0;
                        foreach($checkfulfillids as $key => $order){

                            //FULFILL ONLY THIS FULFILL ID

                            if(($order['status'] == 'Partial')||($order['status'] == 'Canceled')){

                                //echo $i.' - '.$choosepostsarray[$i].' - '.$key.'<br>';

                              $totaladdedamount = $packageinfo['amount'] * 1.3;

                              $multiamount = $totaladdedamount / $totalposts;
                              $multiamount = round($multiamount);

                              $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.instagram.com/p/'.$choosepostsarray[$i].'/', 'quantity' => $multiamount));

                              $orderid = $order1 -> order;

                              $fulfillids = str_replace($key,$orderid,$fulfillids);

                              echo '<hr>';

                            }

                            $i++;
                        
                        }

                      $updateordersfulfillid = mysql_query("UPDATE `orders` SET `fulfill_id` = '$fulfillids' WHERE `id` = '{$info['id']}' LIMIT 1");


                      }else{

                            $orderid = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://instagram.com/p/'.$changeigusername, 'quantity' => $packageinfo['amount']));

                            $orderid = $orderid -> order;

                            if($orderid){
                                $updatefulfillid = mysql_query("UPDATE `orders` SET `fulfill_id` = '$orderid' WHERE `id` = '{$info['id']}' LIMIT 1");
                            }


                    }


 



            }


          /////////////////////////FULFILLMENT CODE
              
          
          $msg = "Successfully resumed - thank you!";
          $changedsuccess = 1;
          
        } 

        if(!empty($msg)){$msg = '<div class="emailsuccess">'.$msg.'</div>';}


      }

   
    $q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderId' AND order_session='$hash' ORDER BY `id` DESC LIMIT 1");
    if(mysql_num_rows($q)=='0')die('No order found');

    $orderInfo = mysql_fetch_array($q);
    $packageType = $orderInfo['packagetype'];
    $userName = $orderInfo['igusername'];

    $htm ="";
    switch($packageType){

        case "followers":
          if($orderInfo['defect'] == 2){//username can be edited here
                $tinymsg = 'Please provide the correct Instagram username in the box below, your profile must be public:';

                $htm .= '<input type ="hidden" value = "followers" name="pkgType">
                        <input type="text" class="form-control" value="'. $userName .'" name="username">';

          }elseif(($orderInfo['defect'] == 1)||$orderInfo['defect'] == 3){
                 $tinymsg = 'Please set your Instagram profile @'.$orderInfo['igusername'].' to public, and then click the button below to resume order:';

                $htm .= '<input type ="hidden" value = "followers" name="pkgType">
                        <input type="text" class="form-control" value="'. $userName .'" name="username" readonly>';

          }
        break;

        case "views":
            $htm .= '<input type ="hidden" value = "views" name="pkgType"><input type="text" class="form-control" value="'. $userName .'" name="username" readonly>';
            break;

        case "likes":
            $htm .= '<input type ="hidden" value = "likes" name="pkgType"><input type="text" class="form-control" value="'. $userName .'" name="username" readonly>';
            break;        

    }

   if(empty($changedsuccess)) $htm .= ' 
             <input class="btn color4" onclick="return confirm(\'Are you sure you have made the correct changes to your Instagram account?\');" type="submit" name="submit" value="Resume Order"   />
             ';



$h1 = 'Resume Order!';

$form = '<form method="POST">
      
      '.$msg.'
      <span class="actionenter">'.$tinymsg.'</span>
      
      '.$htm.'
     
      </form>';



/*
echo $sendgaevent.$therefresh;die;
echo $therefresh;die;
*/

$tpl = file_get_contents('restart-order-popup.html');

$tpl = str_replace('{h1}',$h1,$tpl);
$tpl = str_replace('{form}',$form,$tpl);






echo $tpl;

?>