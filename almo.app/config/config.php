<?php

// $host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.superviral.io)
// $subdomain = explode('.', $host)[0]; // Get the first part of the domain
// $initial = $subdomain . '.';
// $subdomain = '/'. $subdomain . '/etra.group';
// if(!empty($initial) && $initial != "almo.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require dirname($_SERVER["DOCUMENT_ROOT"]) .'/etra.group/loadParamStore.php';
$requestUri = $_SERVER['REQUEST_URI'];


$ip =   $_SERVER['HTTP_X_FORWARDED_FOR'];  

// loadEnv('/home/etra/.env');

$dbName = 'etra_almo';

$conn = '';
// Setup database connection

$conn = mysql_connect ('localhost' , $dbUser , $dbPass) or die(mysql_error());


mysql_select_db ($dbName , $conn);


date_default_timezone_set('Europe/London');

function mysql_connect($server,$username,$password){

    return mysqli_connect($server,$username,$password);

}
function mysql_select_db($database_name,$link){

    return mysqli_select_db($link,$database_name);

}

function mysql_query($query){ global $conn;

    return mysqli_query($conn,$query);

}

function mysql_fetch_array($result){

    return mysqli_fetch_assoc($result);

}
function mysql_num_rows($result){

    return mysqli_num_rows($result);

}

function mysql_insert_id(){ global $conn;

    return mysqli_insert_id($conn);

}

?>