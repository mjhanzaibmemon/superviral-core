<?php

    $url = 'https://cssminifier.com/raw';
    $css = file_get_contents('style.css');
    $css2 = file_get_contents('owl.carousel.min.css');
    // init the request, set various options, and send it
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
        CURLOPT_POSTFIELDS => http_build_query([ "input" => $css ])
    ]);

    $minified = curl_exec($ch);

    // finally, close the request
    curl_close($ch);

// echo $css.$css2;

if(empty($minified)){echo 'No response - so original file has been used';

$fp = fopen('style.min.css', 'w');
fwrite($fp, $css);
fclose($fp);

$fpa = fopen('buystyle.min.css', 'w');
fwrite($fpa, $css.$css2);
fclose($fpa);


die;}else{

$fp = fopen('style.min.css', 'w');
fwrite($fp, $minified);
fclose($fp);

$fpa = fopen('buystyle.min.css', 'w');
fwrite($fpa, $minified.$css2);
fclose($fpa);

}

?>