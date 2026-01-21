<?php


require_once '../sm-db.php';


// grab duplicate accounts


echo "======================================START========================================================<br><br>";


        $QueryRun = mysql_query("SELECT id, 
                                        GROUP_CONCAT(id) as accounts_id, 
                                        email, COUNT(email) AS `count` 
                                FROM accounts 
                                WHERE checked_duplicate = '0'
                                GROUP BY email 
                                having COUNT(email) > 1 limit 3");

        // if(mysql_num_rows($QueryRun)=='0')die('Done');

        while ($Data = mysql_fetch_array($QueryRun)) {


                $brand = $Data['brand'];
                $account_id = $Data['id'];
                $accounts_id = $Data['accounts_id']; 

                // Update automatic likes table
                $autoLikesQuery = "UPDATE automatic_likes SET account_id = $account_id WHERE account_id in ($accounts_id) AND brand ='$brand' ";
                $autoLikesQueryRun = mysql_query($autoLikesQuery);

                if($autoLikesQueryRun) echo "Done for Automatic likes for account : $account_id <br>"; else echo "Failed for Automatic likes query for: $account_id <br>";

                // Update automatic likes billing table
                $autoLikesBillingQuery = "UPDATE automatic_likes_billing SET account_id = $account_id WHERE account_id in ($accounts_id) AND brand ='$brand'";
                $autoLikesBillingQueryRun = mysql_query($autoLikesBillingQuery);

                if($autoLikesBillingQueryRun) echo "Done for Automatic likes billing for account : $account_id <br>"; else echo "Failed for Automatic likes billing query for: $account_id <br>";

                // Update automatic likes session table
                $autoLikesSessionQuery = "UPDATE automatic_likes_session SET account_id = $account_id WHERE account_id in ($accounts_id) AND brand ='$brand'";
                $autoLikesSessionQueryRun = mysql_query($autoLikesSessionQuery);

                if($autoLikesSessionQueryRun) echo "Done for Automatic likes session for account : $account_id <br>"; else echo "Failed for Automatic likes session query for: $account_id <br>";

                // Update orders table
                $ordersQuery = "UPDATE orders SET account_id = $account_id WHERE account_id in ($accounts_id) AND brand ='$brand'";
                $ordersQueryRun = mysql_query($ordersQuery);

                if($ordersQueryRun) echo "Done for orders for account : $account_id <br>"; else echo "Failed for orders query for: $account_id <br>";

                // Update card details table
                $cardDetailsQuery = "UPDATE card_details SET account_id = $account_id WHERE account_id in ($accounts_id) AND brand ='$brand'";
                $cardDetailsQueryRun = mysql_query($cardDetailsQuery);

                if($cardDetailsQueryRun) echo "Done for Card details for account : $account_id <br>"; else echo "Failed for Card Details query for: $account_id <br>";

                // Update order session table
                $orderSessionQuery = "UPDATE order_session SET account_id = $account_id WHERE account_id in ($accounts_id) AND brand ='$brand'";
                $orderSessionQueryRun = mysql_query($orderSessionQuery);

                if($orderSessionQueryRun) echo "Done for Order Session for account : $account_id <br>"; else echo "Failed for Order session query for: $account_id <br>";

                // Update Payment Logs checkout table
                $paymentLogsQuery = "UPDATE payment_logs_checkout SET account_id = $account_id WHERE account_id in ($accounts_id) AND brand ='$brand'";
                $paymentLogsQueryRun = mysql_query($paymentLogsQuery);

                if($paymentLogsQueryRun) echo "Done for Payment Logs checkout for account : $account_id <br>"; else echo "Failed for Payment Logs checkout query for: $account_id <br>";

                // Update Post Notif table
                $postNotifQuery = "UPDATE post_notif_schedule SET account_id = $account_id WHERE account_id in ($accounts_id) AND brand ='$brand'";
                $postNotifQueryRun = mysql_query($postNotifQuery);

                if($postNotifQueryRun) echo "Done for Post Notif for account : $account_id <br>"; else echo "Failed for Post Notif query for: $account_id <br>";

                /*        // Update save payment details table
                $savePaymentDetailQuery = "UPDATE save_payment_details SET account_id = $account_id WHERE account_id in ($accounts_id)";
                $savePaymentDetailQueryRun = mysql_query($savePaymentDetailQuery);

                if($savePaymentDetailQueryRun) echo "Done for Save Payment Detail for account : $account_id <br>"; else echo "Failed for Save Payment Detail query for: $account_id <br>";*/


                // Now finally delete the duplicate account
        
                $deleteDuplicateQuery = "Delete From accounts Where id <> $account_id and id in ($accounts_id) AND brand ='$brand'";
                $deleteDuplicateQueryRun = mysql_query($deleteDuplicateQuery);

                // Update checked_duplicate

                $deleteDuplicateQuery = "Update accounts SET checked_duplicate = '1' Where id = $account_id AND brand ='$brand'";
                $deleteDuplicateQueryRun = mysql_query($deleteDuplicateQuery);

                echo "======================================================================<br>";

                if($deleteDuplicateQueryRun) echo "Deleted duplicate account : $account_id <br>"; else echo "Failed duplicate delete query for: $account_id <br>";

                echo "<hr>";
        

        }
   

        echo '<meta http-equiv="refresh" content="1">';

?>