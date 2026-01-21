<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once '../sm-db.php';

function ago($time)
{
    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
    $now = time();
    $difference     = $now - $time;
    $tense         = 'ago';
    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
        $difference /= $lengths[$j];
    }
    $difference = round($difference);
    if ($difference != 1) {
        $periods[$j] .= "s";
    }
    return "$difference $periods[$j] ago";
}

// supervrial code

$checkUserQueryRun = mysql_query(
    "SELECT * 
                                            FROM 
                                                `users` 
                                            WHERE 
                                                source = 'order' 
                                            AND 
                                                unsubscribe = 0 
                                            AND 
                                                added <= UNIX_TIMESTAMP(NOW() - INTERVAL 4 MONTH)
                                            ORDER BY 
                                                    `id` DESC 
                                            LIMIT 10"
);
if (mysql_num_rows($checkUserQueryRun) > 0) {
    while ($checkUserData = mysql_fetch_array($checkUserQueryRun)) {

        $brand = $checkUserData['brand'];

        $queryOrdersCheck = mysql_query("SELECT * from orders where emailaddress = '{$checkUserData['emailaddress']}' order by id desc limit 3");
        $countOrders = mysql_num_rows($queryOrdersCheck);
        $lastOrderData = mysql_fetch_array($queryOrdersCheck);

        if ($countOrders <= 4) {  // Order count <= 4

            $sixMonthsAgo = strtotime('-6 months');
            if (isset($lastOrderData['added']) && $lastOrderData['added'] < $sixMonthsAgo) { // Last order is older than 6 months.

                $queryAccCheck = mysql_query("SELECT * from accounts where email = '{$checkUserData['emailaddress']}' LIMIT 1");
                $accCheckData = mysql_fetch_array($queryAccCheck);
        
                if (isset($accCheckData['added']) && $accCheckData['added'] < $sixMonthsAgo) { // Account is older than 6 months.

                    $queryUpdate = "UPDATE users SET unsubscribe = 1 WHERE id = " . $checkUserData['id'] . " AND brand = '$brand' LIMIT 1";
                    $run = mysql_query($queryUpdate);

                    if ($run) {
                        echo '<br/>';
                        echo '==============================Successfully Unsubscribed for ' . $checkUserData['emailaddress'] . ' - ' . ago($checkUserData['added']) . ' - ' . date('H:i d/m/Y', $checkUserData['added']) . '======================';
                        echo '<br/>';
                    } else {
                        echo '<br/>';
                        echo '==============================Failed to Unsubscribe for ' . $checkUserData['emailaddress'] . '======================';
                        echo '<br/>';
                    }
                }
            }
        }
    }
} else {
    echo '<br/>';
    echo '=============================ALL DONE! ======================';
    echo '<br/>';
}
// end superviral code