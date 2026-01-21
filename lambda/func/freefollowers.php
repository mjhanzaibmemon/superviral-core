<?php


function emailTpl($loc2, $freetrialmd5, $md5unsub, $source, $igusername, $brand, $subject, $socialmedia)
{

    if ($source == 'cart') $gatracking = '&utm_source=freefollowers&utm_medium=email&utm_campaign=freefollowerscart';
    if ($source == 'order') $gatracking = '&utm_source=freefollowers&utm_medium=email&utm_campaign=freefollowersorder';

    switch ($brand) {
        case 'sv':
            $domain = 'superviral.io';
            $path = 'superviral.io/' . $loc2;
            $product = "Instagram";
            break;
        case 'to':
            $domain = 'tikoid.com';
            $path = 'tikoid.com/';
            $product = "Tiktok";
            break;
    }

    // if($brand == 'sv'){
    //     if($socialmedia == 'ig'){
    //         $product = "Instagram";
    //     }else{
    //         $product = "Tiktok";
    //     }
    // }
   
    $emailbody = '
    <br>
    <p>It looks like your followers haven\'t grown much in the last couple of days.</p>
    
    <br>
    
    <p><b>Get your <b>Free 30 ' . $product . ' Followers</b> here</b>:</p>
    
    <br>
    
    <a href="https://' . $path . 'free-followers/?id=' . $freetrialmd5 . $gatracking . '&emailtype=freefollowers" style="color: #2e00f4;
                border: 2px solid #2e00f4;
                display: block;
                width: 330px;
                padding: 16px 9px;
                text-decoration: none;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                margin: 5px auto;
                font-weight: 700;
                text-align:center;">Get 30 Free ' . $product . ' Followers Now &raquo;</a>
    
    <br>
   
    <p>All you need to do is enter your ' . $product . ' username, and that\'s it!</p> 
    
    <br>
    
    <p>We\'ll immediately start delivering high-quality followers to your account. Have a great weekend!</p>
    
    <br>
    
    <p style="font-size:12px">This no-reply email address doesn\'t accept incoming emails.</p>
    
    
    
    ';
    $tpl = file_get_contents( __DIR__ . '/../emailtemplate/emailtemplate.html');
    $tpl = str_replace('{body}', $emailbody, $tpl);
    $tpl = str_replace('{loc2}', $loc2, $tpl);
    $tpl = str_replace('{subject}', $subject, $tpl);
    $tpl = str_replace('{username}', $igusername, $tpl);
    $tpl = str_replace('{md5unsub}', $md5unsub, $tpl);


    return $tpl;
}
