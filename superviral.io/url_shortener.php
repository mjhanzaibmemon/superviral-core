<?php

include('db.php');



$name = addslashes($_POST['tname']);

$bitlyURL = "";
$htm = "";
if ($name != "") {

    ////generate BITLY CODE

    $time = time();
    $bitlyhash = getRandomString();
    $bitlyhref = 'https://superviral.io?utm_source=tiktok&utm_medium=video&utm_campaign=' . $name;
    $bitlyq = mysql_query("INSERT INTO `bitly` SET `hash` = '$bitlyhash', `href` = '$bitlyhref',`added` = '$time'");


    if ($bitlyq) {
        $bitlyURL = 'https://superviral.io/a/' . $bitlyhash;
    } else {
        $bitlyURL = "Something went wrong";
    }
    ////

    $htm = ' <span style="color:#1a73e7" class="leftColSubHeadng foo">
    '.$bitlyURL.'<textarea style="max-height: 0px;
                        /* display: none; */
                        min-height: 0px !important;
                        width: 0px;
                        resize: none;" class="language-less">'.$bitlyURL.'</textarea>
        <a href="#" style="margin: 0px;margin-top: -12px !IMPORTANT;width: 80px;float: right;" class="btn btn3 copy-button">Copy</a>
    </span>';

}


function getRandomString($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
}

?>



<!DOCTYPE html>

<head>
    <title>URL Shortener</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="alternate" hreflang="en-us" href="https://us.superviral.io/contact-us/" />
    <link rel="alternate" hreflang="en-gb" href="https://uk.superviral.io/contact-us/" />
    <link rel="alternate" hreflang="de" href="https://de.superviral.io/kontakt/" />
    <link rel="alternate" hreflang="it" href="https://it.superviral.io/contattaci/" />
    <link rel="alternate" hreflang="es" href="https://es.superviral.io/contactanos/" />
    <link rel="alternate" hreflang="fr" href="https://fr.superviral.io/contactez-nous/" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style.min.css">
    <link rel="stylesheet" type="text/css" href="/css/orderform.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>
    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>

    <style type="text/css">
        h1 {
            color: #000;
            text-align: center;
            max-width: 100%;
            width: 100%;
        }

        body {
            background: #f5f7fe;
        }

        .containercontactus {
            display: inline-block;
            width: 100%;
        }

        .cn2 {
            margin-top: 20px !important;
            text-align: left;
        }

        .containercontactus .left,
        .containercontactus .right {
            width: 100%;
        }



        .labelcontact {
            font-size: 14px;
            color: #8c8c8c;
            display: block;
            margin-top: 20px;
            margin-bottom: -5px;
        }

        .inputcontact,
        .btncontact {
            padding: 12px;
            width: 100%;
            font-size: 15px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            border: 1px solid #bbb;
            margin-top: 9px;
            box-sizing: border-box;
            outline: 0;
        }

        .textareacontact {
            height: 300px;
            font-family: 'Poppins';
        }

        .mobofficeloc img {
            width: 100%;
        }

        .emailsuccess {
            margin-bottom: 35px;
        }


        @media only screen and (min-width: 768px) {

            .contactusspan {
                display: block;
                margin-bottom: 20px;
            }

        }

        @media only screen and (min-width: 992px) {

            .containercontactus {
                max-width: 768px;
                margin: 0 auto;
            }

        }

        @media only screen and (min-width: 1200px) {}
    </style>
</head>

<body>
    <div class="color2 se1" align="center">

        <div class="cnwidth">

            <h1>URL Shortener</h1>
            <span class="contactusspan"></span>

            <div class="container containercontactus">




                <div class="left">
                   
                  <?php echo $htm; ?>
                </div>

                <div class="right">


                    <form method="POST" enctype="multipart/form-data">

                        <div class="label labelcontact">Tiktok Username</div>
                        <input class="input inputcontact" name="tname" value="" required>
                        <input type="submit" class="btn color4" name="submit" value="submit">


                    </form>

                </div>

            </div>

        </div>
    </div>
    <script>
        (function(){

// Get the elements.
// - the 'pre' element.


var pre = document.getElementsByClassName('foo');


// Add a copy button in the 'pre' element.
// which only has the className of 'language-'.

for (var i = 0; i < pre.length; i++) {
    var isLanguage = pre[i].children[0].className.indexOf('language-');

};

// Run Clipboard

var copyCode = new Clipboard('.copy-button', {
    target: function(trigger) {
        return trigger.previousElementSibling;
}
});


copyCode.on('success', function(event) {
    event.clearSelection();
    event.trigger.textContent = 'Copied';
    window.setTimeout(function() {
        event.trigger.textContent = 'Copy';
    }, 2000);

});


copyCode.on('error', function(event) { 
    event.trigger.textContent = 'Press "Ctrl + C" to copy';
    window.setTimeout(function() {
        event.trigger.textContent = 'Copy';
    }, 5000);
});

})();
    </script>
</body>

</html>