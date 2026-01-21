<?php



include('db.php');
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';

die;

$spusername = 'svthree';
$sppassword = '88Q^j9URqb0O';


    $file = 'https://scontent-yyz1-1.cdninstagram.com/v/t51.2885-15/313934294_3395287350728491_2728572423725744683_n.jpg?stp=c0.280.720.720a_dst-jpg_e15_s150x150&_nc_ht=scontent-yyz1-1.cdninstagram.com&_nc_cat=1&_nc_ohc=1sRT8Pa80DUAX_UzVh5&edm=ABfd0MgBAAAA&ccb=7-5&oh=00_AfAIxGUvyvO8FeU9__xVZifAW4kKjHJw6mckElK_fLTKAA&oe=636E9FC6&_nc_sid=7bff83';

    $file = 'https://superviral.io/';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $file);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($curl, CURLOPT_TIMEOUT, 8);

        curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
        
        //curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

        curl_setopt($curl, CURLOPT_ENCODING, '');




        $get = curl_exec($curl);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($curl);



print_r($get);

        echo $httpcode;




?>