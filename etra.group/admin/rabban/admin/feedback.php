<?php

include('adminheader.php');

if (!empty($_POST['submit'])) {


    $feedback = addslashes(trim($_POST['feedback']));
    $email = addslashes($_POST['email']);
    
    if (empty($feedback)) $failed = 'Please enter feedback';
    if (empty($email)) $failed = 'Please enter a valid email address';
    
    if (empty($failed)) {
        $now = time();
        $sql = "INSERT INTO `feedbacks` (email, feedback, createdAt) VALUES ('$email', '$feedback', $now);";
        $res = mysql_query($sql);

        if ($res) $reviewmessage = '<div class="emailsuccess">Feedback Submitted, Thank you!</div>';
        else $reviewmessage = '<div class="emailsuccess emailfailed">Failed: Technical error!</div>';
    } else {
        $reviewmessage = '<div class="emailsuccess emailfailed">Failed: ' . $failed . '</div>';
    }
}

?>
<!DOCTYPE html>

<head>
    <title>Submit feedback</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/orderform.css">

    <style type="text/css">
        .box23 {
            margin: 66px auto;
            width: 950px;
            background: #fff;
            border-radius: 5px;
            text-align: left;
            padding: 15px;
        }

        h1 {
            text-align: left;
            max-width: 100%;
        }

        .label {
            margin-top: 35px;
        }

        .container div input,
        .selectric,
        .input,
        .btn {
            padding: 13px;
            font-size: 14px;
        }

        .btn {
            width: 100px;
            text-align: center;
        }

        html {
            overflow-x: hidden;
        }

        .cke_reset_all {
            background: #f7f7f7 !important;
        }

        .articles {
            width: 100%;
        }

        .articles tr td {
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #000;
            padding: 10px;
            vertical-align: top
        }

        .articles tr:first-child td {
            background: #f1f1f1;
            font-weight: bold;
        }

        .status {
            font-weight: bold;
            height: 23px;
            width: 55px;
            padding: 5px;
            font-size: 15px;
            text-align: center;
            border-radius: 3px;
        }

        .btn {
            margin: 0 !important;
            width: 152px;
        }

        textarea {
            font-family: 'Open Sans';
            height: 400px;
        }


        .previouscontent {
            width: 100%
        }

        .previouscontent tr td {
            padding: 10px;
            border-bottom: 1px solid grey;
        }

        .previouscontent tr td .foo {
            display: inline-block;
            width: 100%;
            margin-bottom: 18px;
        }

        .language-less {
            width: 1px;
            height: 1px;
            resize: none;
        }
    </style>
    <script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.10.1.min.js"></script>
</head>

<body>
        <?=$header?>

    <h1 style="text-align:center;margin-top:35px;">Submit Feedback</h1>

    <div class="box23">


        <?= $reviewmessage ?>

        <form method="POST">

        <br/>
            Email:
            <input class="input inputcontact" type="email" placeholder="Please enter your email address" name="email" value="<?= $email ?>">
            <br><br>Feedback:
            <textarea class="input inputcontact" name="feedback" placeholder="Please enter feedback"></textarea>

            <input class="btn color3" name="submit" type="submit" value="submit">

        </form>


    </div>

    <!-- <div class="box23">


        <table class="previouscontent">

            <?= $previouscontent ?>

        </table>


    </div> -->



</body>

</html>