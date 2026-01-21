<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


$thisstaffmember = addslashes($_SESSION['first_name']);
$date_of_absence = date('d-m-Y', time());

$checkstaffq = mysql_query("SELECT * FROM `staff_absence` WHERE `staff` = '$thisstaffmember' AND `date_of_absence` = '$date_of_absence' LIMIT 1");

if (mysql_num_rows($checkstaffq) == 1) {

    $dontdosupportcss = '

	body{background:#fbb!important;}

	.box23{display:none!important;}

	';

    $dontdosupportdiv = '<div style="    width: 100%;
    text-align: center;
    padding-top: 15px;
    font-size: 28px;">Unfortunately, the time limit to start support was missed today,<br>
    please resume support on your next working day.</div>';
}


$user = addslashes(trim($_POST['user']));
$unsubscribeID = addslashes($_POST['unsubscribeID']);
$Message = "";
// if(empty($orderid))$orderid = addslashes(trim($_GET['orderid']));
if (empty($user)) {
    $user = addslashes(trim($_GET['user']));
}

if (!empty($unsubscribeID)) {
    mysql_query("UPDATE users
    SET unsubscribe= CASE
       WHEN unsubscribe=1 THEN 0
       WHEN unsubscribe =0 THEN 1
    END
    where id ='" . $unsubscribeID . "' AND brand ='$brand' LIMIT 1");
    $Message = '<div class="emailsuccess">Done Successfully</div>';
}

if (!empty($user)) {
    $q = mysql_query("SELECT * FROM `users` WHERE brand ='$brand' AND `emailaddress` LIKE '%$user%' ORDER BY `id` DESC");
    $field = '<input type="hidden" name="user" value="' . $user . '">';
}

$brandName = getBrandSelectedName($brand);
if ($q) {

    while ($info = mysql_fetch_array($q)) {

        $results .= '

            <div class="box23">

            <table id="mailList' . $info['id'] . '" class="perorder">

                <tr><td>Mail list #' . $info['id'] . '</td><td></td></tr>
                <tr><td>🏢 Company:</td><td style=""><img src="/admin/assets/icons/'. $brandName .'.svg"></td></tr>
                <tr><td>#️⃣ Mail List ID: </td><td>' . $info['id'] . '</td></tr>
                <tr><td>📧 Email address: </td><td><a href=' . $siteDomain . '/admin/check-ml/?user=' . $info['emailaddress'] . '>' . $info['emailaddress'] . '</a></td></tr>
                <tr><td>📦 Source: </td><td>' . $info['source'] . '</td></tr>
                <tr><td>⌚ Date Added: </td><td>' . date('l jS \of F Y H:i:s ', $info['added']) . '</td></tr>
                <tr><td></td>
                <td> ';
        if ($info["unsubscribe"] == 0) {
            $results .=  '<form href="' . $siteDomain . '/admin/check-ml/" method ="POST"><input type="hidden" name="unsubscribeID" value="' . $info['id'] . '"><button class="btn btn3 report"  onclick="return confirm(\'Are you sure you want to unsubscribe?\');" >Unsubscribe</button></form>';
        } else {
            $results .= '<form href="' . $siteDomain . '/admin/check-ml/" method ="POST"><input type="hidden" name="unsubscribeID" value="' . $info['id'] . '"><button class="btn btn3 report"  onclick="return confirm(\'Are you sure you want to subscribe?\');" >Subscribe</button></form>';
        }

        $results .= '</td></tr>
            </table>

            </div>';
    }

    if (!empty($results)) {
        $results = '' . $results . '';
    }
}
if (!empty($user) && mysqli_num_rows($q) < 1)
    $Message = '<div class="emailsuccess emailfailed">No Mail List Found</div>';

$tpl = str_replace('{dontdosupportdiv}', $dontdosupportdiv, $tpl);
$tpl = str_replace('{dontdosupportcss}', $dontdosupportcss, $tpl);
$tpl = str_replace('{user}', $user, $tpl);
$tpl = str_replace('{results}', $results, $tpl);


output($tpl, $options);
