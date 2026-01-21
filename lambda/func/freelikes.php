<?php


function emailTpl($loc2, $freetrialmd5, $md5unsub, $source, $igusername, $brand, $subject, $added)
{

    if ($source == 'cart') $gatracking = '&utm_source=freelikes&utm_medium=email&utm_campaign=freelikescart';
    if ($source == 'order') $gatracking = '&utm_source=freelikes&utm_medium=email&utm_campaign=freelikesorder';

    $emailbody = '
<br>
<p>It looks like your likes haven\'t grown much in the last couple of days.</p>

<br>

<p><b>Get your <b>Free 50 Instagram Likes</b> here</b>:</p>

<br>

<a href="https://superviral.io/free-likes/?id=' . $freetrialmd5 . '" style="color: #2e00f4;
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
            text-align:center;">Get 50 Free Instagram Likes Now &raquo;</a>

<br>


<p>All you need to do is enter your Instagram username and select a post, and that\'s it!</p> 

<br>

<p>We\'ll immediately start delivering high-quality likes to your account. Have a great weekend!</p>

<br>

<p style="font-size:12px">This no-reply email address doesn\'t accept incoming emails.</p>



';





    $tpl = file_get_contents(__DIR__ . '/../emailtemplate/emailtemplate.html');

    $tpl = str_replace('{body}', $emailbody, $tpl);
    $tpl = str_replace('{loc2}', $loc2, $tpl);
    $tpl = str_replace('{subject}', $subject, $tpl);
    //$tpl = str_replace('Black Friday - 50 Instagram likes!','FREE 50 Instagram likes for Black Friday!',$tpl);
    $tpl = str_replace('{username}', $igusername, $tpl);
    $tpl = str_replace('{md5unsub}', $md5unsub, $tpl);
    $formattedDate = date('d/m/Y h:i A', $added);
    $tpl = str_replace('{date_added}', $formattedDate, $tpl);

    return $tpl;
}
