<?php

include('db.php');
include('ordercontrol.php');

$heading = '<h1 class="firsth1">Enter Post Link</h1>';

$text = 'Copy and paste the your IG post link ';

$cta = '<div class="cta">
            <form method="POST" action="/orderselectlink.php">
                <input class="input inputcontact" name="post_input" style="padding: 13px 10px;font-size: 15px;" placeholder="https://www.instagram.com/p/C1B69-arTEX/">
                <input class="btn color4" type="submit" name="submit" value="Next" style="margin: 20px 0 35px 0!important;">
            </form>
        </div>
';
// Ciu4cNurp0G###https://cdn.superviral.io/thumbs/d946346ddca3917ce29ae3a093ee8538.jpg~~~

$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));
$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);

$packagetype = $packageinfo['type'];

if(!empty($_POST)){
    $string = $_POST['post_input'];
    $matches = 'instagram';

    $parse = parse_url($string);
    $domain = $parse['host'];

    if (strpos($string, $matches) == false || strpos($domain, $matches) == false) {
        echo "<script>alert('Invalid URL');parent.closequestionDiv(0);</script>";die;
     }
}

if (!empty($_POST['post_input'])) {
    $submitted_values = $_POST['post_input'];

    $shortcodes = explode('/' , $submitted_values );
    $shortcode =$shortcodes[4];

    if($shortcode == 'p' || strlen($shortcode) < 11){
        $shortcode = $shortcodes[5];
    }
    $values = $shortcode .'###' . addslashes($submitted_values) . '~~~';

    mysql_query("UPDATE `order_session` SET 
               `chooseposts` = '{$values}'
              WHERE `order_session` = '$ordersession' LIMIT 1");


    if ($packagetype == 'comments') {

        echo "<script>window.top.location.href = '/" . $loclinkforward . $locas[$loc]['order'] . "/select-comments/';</script>";

        // header('Location: /' . $loclinkforward . $locas[$loc]['order'] . '/select-comments/');
        die;
    }

    echo "<script>window.top.location.href = '/" . $loclinkforward . $locas[$loc]['order'] . "/" . $locas[$loc]['order2'] . "/';</script>";
    // header('Location: /' . $loclinkforward . $locas[$loc]['order'] . '/' . $locas[$loc]['order2'] . '/');

    die;
}

if ((!empty($_POST['submit']))) {
    echo "<script>parent.closequestionDiv(1);</script>";
}


?>
<!DOCTYPE html>

<head>
    <title>Add a question</title>
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Language" content="en-gb">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://superviral.io/css/buystyle.min.css">
    <link rel="stylesheet" type="text/css" href="https://superviral.io/css/orderform.css">
    <style type="text/css">
        .bodypadding {
            width: 100%;
            padding: 15px 25px;
            box-sizing: border-box;
        }

        .heading {
            text-align: center;
            position: relative;
        }

        .h1 {
            font-size: 39px;
            text-align: center;
            font-weight: bold;
            display: block;
            font-family: "Source Sans Pro", sans-serif;
        }

        .text {
            margin-top: 17px;
            font-size: 14px;
            line-height: 27px;
            text-align: center;
        }

        .text ol li {
            margin-bottom: 15px;
        }

        .cta {
            margin-top: 2px;
        }

        @media only screen and (min-width: 768px) {
            h1 {
                font-size: 43px;
            }

        }

        @media only screen and (min-width:992px) {}

        @media only screen and (min-width:1200px) {}

        @media only screen and (min-width:1500px) {}
    </style>


</head>

<body>

    <div class="bodypadding">
        <div class="heading color3 textcolor3">
            <?= $heading ?>
        </div>

        <div class="text"><?= $text ?></div>

        <?= $cta ?>

    </div>
    <script>

    </script>

</body>

</html>