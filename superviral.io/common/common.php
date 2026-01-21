<?php

/*if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222'){

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        

}*/

class Common {

    public function request_hash($param, $company_hashcode)
        {

            if (in_array($param['transaction']['transaction_type'], array('AUTH_ONLY', 'AUTH_CAPTURE', 'CREDIT', 'BENEFICIARY_NEW'))) {

                $str = $param['timestamp'] . $param['transaction']['transaction_type'] . $param['company_id'] . $param['transaction']['merchant_order_id'];
            } elseif (
                in_array($param['transaction_type'], array(
                    'CAPTURE', 'VOID',
                    'REFUND', 'SUBSCRIPTION_MANAGE', 'ACCOUNT_UPDATER', 'PAY_OUT'
                ))
            ) {
                $str = $param['timestamp'] . $param['transaction_type'] . $param['company_id'] .
                    $param['original_transaction_id'];
            }

            return hash('sha256', $str . $company_hashcode);
        }

        public function curl_request($URL, $Data) {
            $curl = curl_init($URL);
        
            // Check if $Data is already a JSON string
            $jsonData = is_array($Data) ? json_encode($Data) : $Data;
        
            curl_setopt_array($curl, [
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Connection: Keep-Alive",
                    "Accept: application/json"
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_TIMEOUT => 10,  // Limit execution time
                CURLOPT_CONNECTTIMEOUT => 5,  // Connection time limit
                CURLOPT_TCP_FASTOPEN => true,  // Faster TCP connection
                CURLOPT_FRESH_CONNECT => false,  // Allow Keep-Alive
                CURLOPT_FORBID_REUSE => false,  // Keep-Alive enabled
                CURLOPT_NOSIGNAL => 1,  // Prevent multi-threading issues
                CURLOPT_DNS_CACHE_TIMEOUT => 300,  // Cache DNS for 5 min
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,  // Prefer IPv4
            ]);
        
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
            // Check for cURL errors
            if ($json_response === false) {
                $error = curl_error($curl);
                curl_close($curl);
                return ["error" => "cURL Error: " . $error];
            }
        
            curl_close($curl);
        
            // Return decoded JSON response
            return json_decode($json_response, true);
        }
        

}


?>
