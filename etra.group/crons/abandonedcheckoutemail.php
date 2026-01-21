<?php

function ago($time)
{$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
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

function secondsToTime($seconds) {
      $dtF = new \DateTime('@0');
      $dtT = new \DateTime("@$seconds");
      return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}



include('../sm-db.php');
include('emailer.php');

//from 6-days ago
$from = time() - (86400 * 6);


//to 2-days ago
$to = time() - (86400 * 1);


    $q = mysql_query("SELECT * FROM `users` WHERE `source` = 'cart' AND `abandonedcheckoutemail` = '0' AND `added` BETWEEN '$from' and '$to' ORDER BY `added` DESC LIMIT 10");

    // if(mysql_num_rows($q)==0)die('Done for now, all are complete');

    $i = 1;
    while($info = mysql_fetch_array($q)){

            $brand = $info['brand'];

            switch($brand){
              case 'sv':
                  $domain = 'superviral.io';
                  $website = 'Superviral';
              break;
              case 'to':
                  $domain = 'tikoid.com';
                  $website = 'Tikoid';
              break;
              case 'fb':
                  $domain = 'feedbuzz.io';
                  $website = 'Feedbuzz';
              break;
              case 'tp':
                  $domain = 'tokpop.com';
                  $website = 'Tokpop';
              break;
              case 'sz':
                  $domain = 'swizzy.io';
                  $website = 'Swizzy';
              break;
          }


            if (strpos($info['emailaddress'], '@') == false){mysql_query("UPDATE `users` SET `abandonedcheckoutemail` = '1' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");}

              if (strpos($info['emailaddress'], '@') == false) continue;


              $searchforordersessionq = mysql_query("SELECT * FROM `order_session` WHERE `emailaddress` LIKE '%{$info['emailaddress']}%' AND `igusername` != '' AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");

              $getorderinfo = mysql_fetch_array($searchforordersessionq);

              if(mysql_num_rows($searchforordersessionq)==0)$searchforordersessionq = mysql_query("SELECT * FROM `order_session` WHERE `emailaddress` LIKE '%{$info['emailaddress']}%' AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");


              $packageinfoq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$getorderinfo['packageid']}' AND brand = '$brand' LIMIT 1");

              $packageinfo = mysql_fetch_array($packageinfoq);

              mysql_query("UPDATE `users` SET `abandonedcheckoutemail` = '2' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");

              $loc2=$getorderinfo['country'];

              if(empty($loc2))$loc2 = 'uk';

              if(!empty($loc2))$loc2 = $loc2.'.';
              if($loc2=='ww.' || $loc2=='us.')$loc2 = '';
        
              if(empty($getorderinfo['igusername']) || empty($getorderinfo['order_session'])) continue;

              if ($brand == 'sv'){

                $tpl = '
                        Hi there,
                        <br><br>
                        I can see that you didn\'t finish your order for your Instagram <b>@'.$getorderinfo['igusername'].'
                        </b><br><br>
                        By any chance, are there any questions you have about your order for <b>'.$packageinfo['amount']. ' Instagram '. $packageinfo['type'].'</b>?
                        <br><br>
                        I would be more than happy to help!
                        <br><br>
                        If you want to continue growing your Instagram, you can <a href="https://'.$loc2.'superviral.io/order/choose/?setorder='.$getorderinfo['order_session'].'&utm_medium=email&utm_source=newsletter&utm_campaign=abandoned_checkout">click here to finish your order</a>.
                        <br><br>
                        Kind regards,
                        <br><br>
                        James Harris<br><br>

                        E: customer-care@superviral.io<br>
                        T: 0203 856 3786<br>
                        <br>
                        107-111 Fleet Street<br>
                        London<br>
                        EC4A 2AB<br>
                        United Kingdom<br><br>

                        <img src="https://superviral.io/imgs/jharrissig.png">
                        ';


              }

              if ($brand == 'to'){

                $tpl = '
                        Hi there,
                        <br><br>
                        I can see that you didn\'t finish your order for your Tikoid <b>@'.$getorderinfo['igusername'].'
                        </b><br><br>
                        By any chance, are there any questions you have about your order for <b>'.$packageinfo['amount']. ' Tikoid '. $packageinfo['type'].'</b>?
                        <br><br>
                        I would be more than happy to help!
                        <br><br>
                        If you want to continue growing your Tikoid, you can <a href="https://tikoid.com/order/choose/?setorder='.$getorderinfo['order_session'].'&utm_medium=email&utm_source=newsletter&utm_campaign=abandoned_checkout">click here to finish your order</a>.
                        <br><br>
                        Kind regards,
                        <br><br>
                        James Harris<br><br>
                        
                        E: customer-care@tikoid.com<br>
                        T: 0203 856 3786<br>
                        <br>
                        107-111 Fleet Street<br>
                        London<br>
                        EC4A 2AB<br>
                        United Kingdom<br><br>
                        
                        <img src="https://tikoid.com/imgs/jharrissig.png">
                        ';


              }

              if ($brand == 'fb'){

                $tpl = '
                        Hi there,
                        <br><br>
                        I can see that you didn\'t finish your order for your Instagram <b>@'.$getorderinfo['igusername'].'
                        </b><br><br>
                        By any chance, are there any questions you have about your order for <b>'.$packageinfo['amount']. ' Instagram '. $packageinfo['type'].'</b>?
                        <br><br>
                        I would be more than happy to help!
                        <br><br>
                        If you want to continue growing your Instagram, you can <a href="https://feedbuzz.io/order/choose/?setorder='.$getorderinfo['order_session'].'&utm_medium=email&utm_source=newsletter&utm_campaign=abandoned_checkout">click here to finish your order</a>.
                        <br><br>
                        Kind regards,
                        <br><br>
                        James Harris<br><br>
                        
                        E: customer-care@feedbuzz.io<br>
                        T: 0203 856 3786<br>
                        <br>
                        107-111 Fleet Street<br>
                        London<br>
                        EC4A 2AB<br>
                        United Kingdom<br><br>
                        
                        <img src="https://feedbuzz.io/imgs/jharrissig.png">
                        ';


              }
              
              echo $i.'. '.$info['emailaddress'].' - '.ago($info['added']).'<br><br><br><br>';
              echo $tpl.'<hr>';

              $subject = '@'.$getorderinfo['igusername'].': Get '.$packageinfo['amount'].' '.ucfirst($packageinfo['type']);
              $email = $info['emailaddress'];

              //emailnow($email,'Superviral','no-reply@superviral.io','🐙 '.$subject,$tpl);
              emailnow($email,'James Harris','support@' . $domain,'🐙 '.$subject,$tpl);

              email_stat_insert('Abandon Checkout Email', $email, addslashes($tpl), $brand);


              unset($email);
              unset($packageinfoq);
              unset($subject);
              unset($tpl);
              unset($packageinfo);
              unset($loc2);
              unset($getorderinfo);
              unset($searchforordersessionq);


              $i++;

    }
?>