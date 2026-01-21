<?php
// include $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';
session_start();

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra."){
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}else{
    $initial = "";
} 


if(isset($_SESSION['first_name']) && !empty($_SESSION['first_name'])){

    // success
   $user  = $_SESSION['first_name'];
   
   header('location: /admin/email-support/');
}

if(!empty($_GET['location'])){
    $_SESSION['location'] = $_GET['location'];

}
$tpl = file_get_contents('index.html');
// google login

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] ."/common/googleapi/vendor/autoload.php";

// include_once $_SERVER['DOCUMENT_ROOT']. "/common/googleapi/vendor/autoload.php";

$google_client = new Google_Client();

//$google_client->setClientId('152075261240-565u7km8sbpid8plticuo75a9n5lsck0.apps.googleusercontent.com'); //Define your ClientID

//$google_client->setClientSecret('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'); //Define your Client Secret Key



////////// OUR UNIQUE ID FOR LIVE SERVER

$google_client->setClientId('343552994911-1ags89k4n0cf78duu20tmoa4vuujs9vv.apps.googleusercontent.com'); //Define your ClientID

$google_client->setClientSecret('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'); //Define your Client Secret Key



$google_client->setRedirectUri('https://'. $initial .'etra.group/admin/'); //Define your Redirect Uri

$google_client->addScope('email');

$google_client->addScope('profile');

if (isset($_GET["code"])) {
    $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);

    if (!isset($token["error"])) {

            $google_client->setAccessToken($token['access_token']);

            $_SESSION['access_token'] = $token['access_token'];
    
            $google_service = new Google_Service_Oauth2($google_client);
    
            $data = $google_service->userinfo->get();

        if(strpos($data['email'],'etra.group') !== false){
    
            $current_datetime = date('Y-m-d H:i:s');
    
            // print_r($data);
    
            $_SESSION['first_name'] = strtolower($data['given_name']);
            $_SESSION['last_name'] = strtolower($data['family_name']);
            $_SESSION['email_address'] = strtolower($data['email']);
            $_SESSION['profile_picture'] = strtolower($data['picture']);
    
            if(!empty($_SESSION['location']))
            header('location: '. $_SESSION['location']);
            else
            header('location: /admin/email-support/');
        
        }else{
            echo '<script>
                    alert("You are not authorized to access this application. please login with etra.group email id");
                    window.location.href = \'/admin/\';
                 </script>';
            die;
        }
      
    }
}


$login_button = '';

// echo $_SESSION['access_token'];

if (!$_SESSION['access_token']) {
    //  echo 'test';

    $login_button = '<a href="' . $google_client->createAuthUrl() . '"><img src="asset/sign-in-with-google.png" /></a>';
}

$tpl = str_replace('{googleHref}', $google_client->createAuthUrl(), $tpl);



echo $tpl;
