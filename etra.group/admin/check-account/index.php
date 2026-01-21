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
$deleteAccountID = addslashes($_POST['deleteaccountid']);
$deleteMessage = "";
// if(empty($orderid))$orderid = addslashes(trim($_GET['orderid']));
if (empty($user)) {
    $user = addslashes(trim($_GET['user']));
}

if (!empty($deleteAccountID)) {
    mysql_query("DELETE FROM `accounts` WHERE `id` = '$deleteAccountID'  AND brand ='$brand' LIMIT 1"); 
    $deleteMessage = '<div class="alert alert-error">Deleted Successfully</div>';
}

if (!empty($user)) {$q = mysql_query("SELECT * FROM `accounts` WHERE  (brand ='$brand' AND `email` LIKE '%$user%') OR (brand ='$brand' AND `username` LIKE '%$user%')   ORDER BY `id` DESC");
    $field = '<input type="hidden" name="user" value="' . $user . '">';}

$brandName = getBrandSelectedName($brand);
$domain = getBrandSelectedDomain($brand);
if ($q) { 
    

    while ($info = mysql_fetch_array($q)) {
        

        $results .= '

            <div class="box23">

            <table id="account' . $info['id'] . '" class="perorder">

                <tr><td>Account #' . $info['id'] . '</td><td></td></tr>
                <tr><td>🏢 Company:</td><td style=""><img src="/admin/assets/icons/'. $brandName .'.svg"></td></tr>
                <tr><td>#️⃣ Account ID: </td><td>' . $info['id'] . '</td></tr>
                <tr><td>📧 Email address: </td><td><a href=' . $siteDomain . '/admin/check-account/?user=' . $info['email'] . '>' . $info['email'] . '</a></td></tr>
                <tr><td>📦 Username: </td><td>' . $info['username'] . '</td></tr>
                <tr><td>⌚ Account first created: </td><td>' . date('l jS \of F Y H:i:s ', $info['added']) . '</td></tr>  
                <tr><td>⌚ Last login: </td><td>' . date('l jS \of F Y H:i:s ', $info['lastlogin']) . '</td></tr>
                <tr><td></td><td><form action="' . $siteDomain . '/admin/check-account/" method ="POST"><input type="hidden" name="deleteaccountid" value="'. $info['id'] .'"><button class="btn btn3 report"  onclick="return confirm(\'Are you sure you want to delete these account?\');" >Delete Account</button></form></td></tr>
                <tr><td></td><td><a href="https://'. $domain .'/bypass-login.php?id='. base64_encode($info['id']) .'&key='. $info['password'] .'"><button class="btn btn3 report"  onclick="return confirm(\'Are you sure you want to login these account?\');" >Login Account</button></a></td></tr></table>

            </div>';

    }

    if (!empty($results)) {
        $results = '' . $results . '';
    }

}

    if(!empty($user) && mysqli_num_rows($q) < 1)
    $deleteMessage = '<div class="emailsuccess emailfailed">No Accounts Found</div>';

    $tpl = str_replace('{dontdosupportdiv}',$dontdosupportdiv,$tpl);
    $tpl = str_replace('{dontdosupportcss}',$dontdosupportcss,$tpl);
    $tpl = str_replace('{user}',$user,$tpl);
    $tpl = str_replace('{summaryresults}',$summaryresults,$tpl);
    $tpl = str_replace('{results}',$results,$tpl);
    $tpl = str_replace('{deletedMsg}',$deleteMessage,$tpl);

output($tpl, $options);
