<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');
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


$reportdone = addslashes($_POST['reportdone']);
$type = addslashes($_POST['type']);
$search = trim(addslashes($_POST['search']));
$mainid = addslashes($_GET['mainid']);

if (empty($type)) $type = 'cancelled';

//defect code 1 is detected but needs to be categorised as either 2 or 3 (prior to 4th September 2020)
//defect code 2 is cancelled
//defect code 3 is partial
//defect code 5 is ignore completely


$theid = $_GET['theid'];

if (!empty($theid)) {
    $styles = '.first' . $theid . ',.second' . $theid . '{background-color:#e9ffe9;}';
}

if ($_GET['message'] == 'email1') $message = '<div class="emailsuccess">Private IG account email: Sent</div>';
if ($_GET['message'] == 'updatetrue') {
    $message = '<div class="emailsuccess">Order ID #' . $mainid . ' successfully updated with Supplier ID: ' . $theid . '.</div>';
    $search = $mainid;
}



if ($type == 'cancelled') $q = mysql_query("SELECT * FROM `orders` WHERE `defect` = '2' AND `refund` = '0' AND brand = 'sv' ORDER BY `id` ASC LIMIT 1");
if ($type == 'partial') $q = mysql_query("SELECT * FROM `orders` WHERE `defect` = '3' AND `refund` = '0' AND brand = 'sv' ORDER BY `id` ASC LIMIT 1");

if (!empty($search)) {
    $q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$search' AND brand = '$brand' ORDER BY `id` ASC LIMIT 1");
}




while ($info = mysql_fetch_array($q)) {

    ////////////////// IF SHOW POSTS INSTEAD OF USERNAME


    if (empty($info['chooseposts'])) {
        if ($info['packagetype'] == 'likes' || $info['packagetype'] == 'views') {

            $findchoosepostsq = mysql_query("SELECT * FROM `order_session_paid` WHERE `order_session` = '{$info['order_session']}' AND brand = '$brand' LIMIT 1");

            if (mysql_num_rows($findchoosepostsq) == '0') $findchoosepostsq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$info['order_session']}' AND brand = 'sv' LIMIT 1");


            if (mysql_num_rows($findchoosepostsq) == 1) {
                $findchooseposts = mysql_fetch_array($findchoosepostsq);

                $thechoosepostsfound = $findchooseposts['chooseposts'];


                if (!empty($thechoosepostsfound)) {

                    $thechoosepostsfound = explode('~~~', $thechoosepostsfound);

                    foreach ($thechoosepostsfound as $posts1) {

                        if (empty($posts1)) continue;

                        $posts2 = explode('###', $posts1);

                        $theupdatequery .= $posts2[0] . ' ';
                        $info['chooseposts'] .= $posts2[0] . ' ';
                    }
                }

                $theupdatequery1 = ' Update query: "' . $theupdatequery . '" ';
                $chooseposts = ' FOUND: ';
                mysql_query("UPDATE `orders` SET `chooseposts` = '$theupdatequery' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");
            }
        }
    }


    ////////////////////

    $info['price'] = sprintf('%.2f', $info['price'] / 100);


    if (!empty($info['chooseposts'])) {

        $thispost = $info['chooseposts'];
        $thispost = explode(' ', $thispost);

        foreach ($thispost as $thisposta) {

            if (empty($thisposta)) continue;

            $posts .= '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $thisposta . '">' . $thisposta . '</a><br>';
        }



        $posts = $posts . $chooseposts . $theupdatequery1;

        $show = $posts;
    } else {

        $show = '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $info['igusername'] . '">' . $info['igusername'] . '</a>';
    }


    $fulfills = explode(' ', trim($info['fulfill_id']));

    $fulfillcount = count(array_filter($fulfills));
    $balance = $api->multiStatus($fulfills);

    $balance = json_decode(json_encode($balance), True);

    foreach ($balance as $key => $order) {


        //    if($order['status']=='Partial')$left = ' - '.($info['amount'] - $order['remains']).'/'.$info['amount'];
        //if ($order['status'] == 'Partial') $left = ' - ' . $order['remains'] . '/' . $info['amount'];

        $thisorderstatus .= '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $key . '">' . $key . '</a> - ' . $order['status'] . $left . '<br>';

        unset($left);
    }


    $notesadminq = mysql_query("SELECT * FROM `admin_order_notes` WHERE `orderid` ='{$info['id']}' AND brand = '$brand'");
    if (mysql_num_rows($notesadminq) !== '0') {

        while ($notesinfo = mysql_fetch_array($notesadminq)) {

            $notesadmin .= '<div style="padding:5px;border-bottom:1px dashed grey">' . ago($notesinfo['added']) . ' - ' . $notesinfo['notes'] . '</div>';
        }

        $notesadmin = '<div class="sdiv">' . $notesadmin . '</div>';
    }


    $articles .= '<div class="defectorder">

    <div class="sdiv">
    <b><a target="_BLANK" href="/admin/check-user/?orderid=' . $info['id'] . '#order' . $info['id'] . '">' . $info['id'] . '</a> - ' . $show . '</b><br>£' . $info['price'] . ' - ' . $info['amount'] . ' ' . $info['packagetype'] . '<br>' . date('l jS \of F Y H:i:s ', $info['added']) . '
    </div>

    <div class="sdiv">
					' . $thisorderstatus . '
    </div>

    <div class="sdiv">
                        <form id="makeorder" action="/admin/api/ordermakefordefect.php" method="POST">
                        <input type="hidden" name="update" value="save">
                        <input type="hidden" name="reorder" value="yes">
                        <input type="hidden" name="defectpage" value="defect">
                        <input type="hidden" name="pagefrom" value="' . $type . '">
                        <input type="hidden" name="id" value="' . $info['id'] . '">
                        <input type="hidden" name="ordersession" value="' . $info['order_session'] . '">
                        <input type="submit" onclick="return confirm(\'Are you sure you want to create a new order?\');" class="btn color3 btn-primary" style="width:150px;" value="Make Order"></form>
    </div>


    <div class="sdiv" style="display:none">
                    <form method="POST" action="/admin/api/ordersupdate.php" style="display:none;">
                    <input type="hidden" name="pagefrom" value="' . $type . '">
                    <input type="hidden" name="defectpage" value="defect">
                    <input type="hidden" name="update" value="save">
                    <input type="hidden" name="id" value="' . $info['id'] . '">
                    <input class="input" name="orderid" value="' . $info['fulfill_id'] . '">
                    <input type="submit" class="btn color3 btn-primary" value="SAVE">
                    </form>

                    <form method="POST" action="/admin/api/emailissue.php">
                    <input type="hidden" name="defectpage" value="defect">
                    <input type="hidden" name="pagefrom" value="' . $type . '">
                    <input type="hidden" name="id" value="' . $info['id'] . '">
                    <input type="hidden" name="ordersession" value="' . $info['order_session'] . '">
                    <input type="submit" class="btn color3 btn-primary" value="Send Email for private profile">
                    </form>

    </div>

    <div class="sdiv" style="display:none">
    

                    <form method="POST" action="/admin/api/emailissue2.php">
                    <input type="hidden" name="defectpage" value="defect">
                    <input type="hidden" name="pagefrom" value="' . $type . '">
                    <input type="hidden" name="id" value="' . $info['id'] . '">
                    <input type="hidden" name="ordersession" value="' . $info['order_session'] . '">
                    <input type="submit" class="btn color3 btn-primary" value="Send Email for not-available profile">
                    </form>


    </div>

    ' . $notesadmin . '


                </div>';

    unset($posts);
    unset($thisorderstatus);
}


$tpl = str_replace('{articles}', $articles, $tpl);
$tpl = str_replace('{message}', $message, $tpl);
$tpl = str_replace('{search}', $search, $tpl);

output($tpl, $options);
