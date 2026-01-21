<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../supplier_raw/supplier_raw.php';
require __DIR__ . '/../common/common.php';

use Bref\Context\Context;


return function (array $event, Context $context) {

    global $api;
    writeCloudWatchLog('refills-mass-lambda', 'Received `event`:'. json_encode($event));
    try {
        // Fetching query parameters from Lambda event
        $queryParams = $event['queryStringParameters'] ?? [];
        $orderArray = $queryParams['ids'];
        

        if(empty($orderArray)){
            return [
                'statusCode' => 400,
                'body' => json_encode(['body' => 'Order IDs are required']),
            ];
        }

        $now = time();

        $q = mysql_query("SELECT * FROM `orders` WHERE id IN ($orderArray)");

        while ($info = mysql_fetch_array($q)) {

            $info['fulfill_id'] = trim($info['fulfill_id']);
            $info['fulfill_id'] = $info['fulfill_id'] . ' ';


            $fulfills = explode(' ', $info['fulfill_id']);

            foreach ($fulfills as $fulfillid) {
                $id = $info['id'];

                if (empty($fulfillid)) continue;

                $fulfillid = trim($fulfillid);

                $refillthis = $api->refill($fulfillid);

                mysql_query("UPDATE `orders` SET `lastrefilled` = '$now' WHERE `id` = '$id' LIMIT 1");
            }

            unset($fulfills);
        }

        $sql = "UPDATE orders SET norefill = 0 WHERE id IN ($orderArray)";
        $runSql = mysql_query($sql);

        if ($runSql) {
            $msg = "Refill has been successfully completed.";
        } else {
            $msg = "Sql Error";
            writeCloudWatchLog('refills-mass-lambda', 'Sql error');
            return [
                'statusCode' => 500,
                'body' => json_encode(['error' => $msg]),
            ];
        }

        return [
            'statusCode' => 302,
            'headers' => [
                'Location' => '//etra.group/admin/refill-mass/',
            ],
        ];

    } catch (\Exception $e) {
        // Return error response in case of exceptions

        writeCloudWatchLog('refills-mass-lambda', 'error '. $e->getMessage());
        return [
            'statusCode' => 500,
            'body' => json_encode(['error' => $e->getMessage()]),
        ];
    }
};
