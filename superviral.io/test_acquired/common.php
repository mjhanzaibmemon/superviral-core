<?php

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

        public function curl_request($URL, $Data){
            $curl = curl_init($URL);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $curl,
                CURLOPT_HTTPHEADER,
                array("Content-type: application/json")
            );
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $Data);
    
            $json_response = curl_exec($curl);
    
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    
            curl_close($curl);
    
            $response = json_decode($json_response, true);
            return $response;
        }

}


?>