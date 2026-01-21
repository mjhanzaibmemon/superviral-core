<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$daysago = 7;

$beginofdaysago = strtotime("today", (time() - ($daysago * 86400)));

$beginoftoday = strtotime("today", time());

$canvasHtml = "";
//TEST
$beginofdaysago = $beginofdaysago + 86400;
$beginoftoday = $beginoftoday + 86400;

$packageType = $_GET['packageType'] ? $_GET['packageType'] : 'followers';

$q = mysql_query("SELECT `type` as packagetype from supplier_cost WHERE `type` NOT IN ('freetrial') group by `type`;");
$k = 0;
$packageCount = mysql_num_rows($q);

while ($info = mysql_fetch_array($q)) {

    $q1 = mysql_query("SELECT 
                            FROM_UNIXTIME(`timestamp`, '%d%m%Y') AS `day`,
                            `amount`,
                            COUNT(*) AS `order_count`,
                            SUM(`cost`) AS `total_spent`,
                            AVG(`cost`) AS `average_supplier_cost`,
                            `service_id`
                                FROM 
                                    (SELECT 
                                         `timestamp`,
                                         `cost`,
                                         `amount`,
                                         `service_id`
                                     FROM 
                                         `supplier_cost`
                                     WHERE 
                                        `timestamp` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) 
                                        AND `type` = '{$info['packagetype']}'
                                     ORDER BY 
                                         `timestamp` DESC, `id` DESC) AS `latest_orders`
                                GROUP BY 
                                    `day`, `service_id`
                                ORDER BY 
                            `day` DESC, `amount` DESC;");


    $positiveresult = [];
    $amounts = [];

    while ($info1 = mysql_fetch_array($q1)) {
        $day = $info1['day'];
        $amount = $info1['amount'];
        $service = $info1['service_id'];
    
        if (!isset($positiveresult[$amount])) {
            $positiveresult[$amount] = [
                'service' => $service,
                'data' => []
            ];
        }
    
        $positiveresult[$amount]['data'][$day] = $info1['average_supplier_cost'];
        $amounts[$amount] = true;
    }



    $daycats = strtotime("today", time()) + 86400;
    $labelsbackend = [];
    $labelsbackendDisp = [];
    $datasets = [];

    for ($x = $daysago; $x >= 1; $x--) {
        $newdate = date('dmY', $daycats - (86400 * $x));
        $labelsbackend[] = date('dmY', $daycats - (86400 * $x));
        $labelsbackendDisp[] = "'" . date('d/m/Y', $daycats - (86400 * $x)) . "'";
    }

    foreach ($amounts as $amount => $val) {
        $dataset = [];
    
        // Get the corresponding service for this amount
        $service = $positiveresult[$amount]['service'] ?? '0';
    
        foreach ($labelsbackend as $label) {
            $value = $positiveresult[$amount]['data'][$label] ?? 0;
            $dataset[] = $value;
        }
    
        $datasets[] = "{
            label: 'Service: $service',
            data: [" . implode(",", $dataset) . "],
            borderColor: getRandomColor(),
            backgroundColor: 'rgba(0,0,0,0)',
        }";
    }

    $canvasHtml .= '<h2>Supplier Cost - ' . $info['packagetype'] . '</h2><canvas id="myChart' . $k . '" style="max-height:300px;margin:25px;"></canvas>';

    $canvasJs .= "function getRandomColor() {
        return '#' + Math.floor(Math.random()*16777215).toString(16);
    }";

    $canvasJs .= "const ctx$k = document.getElementById('myChart$k').getContext('2d');
        const myChart$k = new Chart(ctx$k, {
            type: 'line',
            data: {
                labels: [" . implode(',', $labelsbackendDisp) . "],
                datasets: [" . implode(",", $datasets) . "]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });";

    $k++;
}

$tpl = str_replace('{canvasHtml}', $canvasHtml, $tpl);

$tpl = str_replace('{canvasJs}', $canvasJs, $tpl);

// // orders free

// $qOF = mysql_query("SELECT 
//     FROM_UNIXTIME(`added`, '%d%m%Y') AS `day`,
//     COUNT(*) AS total_orders

// FROM 
//     orders_free
// WHERE 
//     added >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY)
// GROUP BY 
//     FROM_UNIXTIME(`added`, '%d%m%Y')
// ORDER BY 
//     `day` DESC;");

// while ($infoOF = mysql_fetch_array($qOF)) {

// $day = $infoOF['day'];
// $positiveresultOF[$day] = $infoOF['total_orders'];
// }


// $daycats = strtotime("today", time()) + 86400;


// for ($x = $daysago; $x >= 1; $x--) {

// $newdate = date('dmY', $daycats - (86400 * $x));

// $labelsOF  .= $labelsOF [$newdate];
// $labelsbackendOF   .= "'" . date('l - d/m/Y', $daycats - (86400 * $x)) . "',";

// $checkthisdaypOF  = $positiveresultOF [$newdate];
// if (empty($checkthisdaypOF )) $checkthisdaypOF  = 0;

// $posdaydataOF   .= $checkthisdaypOF   . ',';
// }



// $labelsOF  = rtrim($labelsOF , ',');
// $labelsbackendOF   = rtrim($labelsbackendOF  , ',');

// $posdaydataOF   = rtrim($posdaydataOF  , ',');

// $tpl = str_replace('{labelsbackendOF}', $labelsbackendOF, $tpl);
// $tpl = str_replace('{posdaydataOF}', $posdaydataOF, $tpl);


output($tpl, $options);
