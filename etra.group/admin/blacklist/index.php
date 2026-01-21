<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$thisstaffmember = addslashes($_SESSION['first_name']);

$user = addslashes(trim($_POST['user']));
$email = addslashes(trim($_POST['email']));
$ip = addslashes(trim($_POST['ip']));
$search = addslashes(trim($_POST['search']));
$create = addslashes($_POST['type']);

$id = addslashes($_GET['id']);

// delete
if(isset($id) && !empty($id)){

    $q = mysql_query("SELECT * FROM `blacklist` WHERE id = $id AND brand = '$brand' LIMIT 1");
    $val = mysql_fetch_array($q);
    $email = $val['emailaddress'];
    $user = $val['igusername'];
    $ip = $val['ipaddress'];
    

    mysql_query("DELETE FROM `blacklist` WHERE  (brand = '$brand' AND emailaddress = '$email') OR (brand = '$brand' AND igusername = '$user') OR (brand = '$brand' AND ipaddress = '$ip')");
    header('Location: /admin/blacklist/');
}



// insert
$time = time();
if(isset($create) && $create == "createBlacklist"){
    if((!empty($user) || !empty($email) || !empty($ip))){

        $query = "INSERT INTO `blacklist` SET 
        `emailaddress` = '$email', 
        `igusername` = '$user', 
        `ipaddress` = '$ip',
        `added` = '$time',
         brand = '$brand', `source` = 'admin-blacklist'";

        $q = mysql_query($query);  
        if($q){
            
            $error ='<div style="color:green;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Added Successfully!!</div>';

            echo json_encode(['msg' => $error]);
            die;
        }else{

            $error ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Something went wrong!!</div>';
            echo json_encode(['msg' => $error]);
            die;
        }


    }
    else{
        
            $error ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Input can\'t be blank</div>';
            echo json_encode(['msg' => $error]);
            die;
        

    }

}

// SEARCH
if(isset($search) && !empty($search)){
    if((!empty($user) || !empty($email) || !empty($ip))){

        if(!empty($user)) $user = $user; else $user = " ";
        if(!empty($email)) $email = $email; else $email = " ";
        if(!empty($ip)) $ip = $ip; else $ip = $ip = " ";

        $query = "SELECT * FROM `blacklist` WHERE (brand = '$brand' AND
        `emailaddress` LIKE '%$email%' )
        OR (brand = '$brand' AND `igusername` LIKE '%$user%') 
        OR  ( brand = '$brand' AND `ipaddress` LIKE '%$ip%' )
        ORDER BY `id` DESC";

        $q = mysql_query($query);  
        $data = ""; 

        if(mysql_num_rows($q) > 0){
        
        
        
            while($info = mysql_fetch_array($q)){
            
                $data .='<tr>
				<td>'. $info['ipaddress'] .'</td>
				<td>'. $info['emailaddress'] .'</td>
				<td>'. $info['igusername'] .'</td>
				<td><a href="?id='. $info['id'] .'" onclick="return confirm(\'Are you sure you want to delete?\');">Delete</a></td>
				</tr>';
            
            }


        }else{
        
            $error ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">No Records Found! </div>';
        
        }

        }else{
        
            $error ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Input can\'t be blank</div>';
        
        }
}


if(!empty($data))$data = '<div class="box23"><table class="summarytbl" style="width:100%"><tr>
	<td>Ip</td>
	<td>Email</td>
	<td>User</td>
	<td>Action</td>
	</tr>'.$data.'</table></div>';

if(empty($data))$data = '';
$brandName = getBrandSelectedName($brand);
$tpl = str_replace('{error}',$error, $tpl);
$tpl = str_replace('{data}',$data, $tpl);
$tpl = str_replace('{brandName}',$brandName, $tpl);

output($tpl, $options);
