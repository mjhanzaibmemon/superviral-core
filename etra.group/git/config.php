<?php


require '/home/etra/public_html/etra.group/loadParamStore.php';


// loadEnv('/home/etra/.env');

#All git config will be here



// [SERVER]
$allowedforgit = [
    '212.159.178.222'
];

if(!in_array($_SERVER['HTTP_X_FORWARDED_FOR'],$allowedforgit)){
    die('Error 35343');
}


$key=str_replace('\n', "
", $key);




// [PATH]


$host = '18.216.125.105';




$user = 'etra';




// [SETTING]



$errorReporting = "false";


// /////////////////////////////////////////////////////////////////// HTTp AUTH

global $gitAdmins ;
// array cred
$gitAdmins = array(
    'rabban' => $rabbangit,
    'manjur' => $manjurgit
    );



$blockedFilesArr = array();

// just add here which not need to display
$blockedFilesArr = [".", "..", ".cache", "db.php", "livecheckout.php","config.php",".well-known","cgi-bin","sm-db.php"]; 



if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])){

    if ($_SERVER['HTTP_X_FORWARDED_PROTO']=="http") {
    $url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $url);
            exit;
        }

}


function http_auth($user, $pass, $realm = "Secured Area")
{

        $adminauthenticate = 0;


        global $gitAdmins;



        foreach($gitAdmins as $key => $value){


            if((isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_USER'] == $key && $_SERVER['PHP_AUTH_PW'] == $value))

            {
                $adminauthenticate = 1;
            }

        }

                if($adminauthenticate == 0)
        	{

                header('WWW-Authenticate: Basic realm="Secured Area"');
                header('Status: 401 Unauthorized');
                exit();

            }
}


//http_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);



?>
