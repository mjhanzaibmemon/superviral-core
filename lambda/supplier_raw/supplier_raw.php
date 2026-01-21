<?php



class Api
{
    public function setApiKey($value)
    {
        $this->api_key = $value;
    }
    public function setApiUrl($value)
    {
        $this->api_url = $value;
    }

    public function order($data)
    { // add order
        $post = array_merge(array('key' => $this->api_key, 'action' => 'add'), $data);
        return json_decode($this->connect($post));
    }

    public function status($order_id)
    { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'status',
            'order' => $order_id
        )));
    }

    public function multiStatus($order_ids)
    { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'status',
            'orders' => implode(",", (array)$order_ids)
        )));
    }

    public function services()
    { // get services
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'services',
        )));
    }

    public function balance()
    { // get balance
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'balance',
        )));
    }

    public function refill($order_id)
    { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'refill',
            'order' => $order_id
        )));
    }

    private function connect($post)
    {
        $_post = array();
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name . '=' . urlencode($value);
            }
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Connection: keep-alive'
            ));
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

$fulfillment_url = getenv('fulfillment_url') ?? 'Not set';
$fulfillment_api_key = getenv('fulfillment_api_key') ?? 'Not set';

if ($fulfillment_url === 'Not set' || $fulfillment_api_key === 'Not set') {
    echo json_encode(['error' => 'API credentials not set.']);
    die;
}

global $api;
$api = new Api();

$api->setApiKey($fulfillment_api_key);
$api->setApiUrl($fulfillment_url);