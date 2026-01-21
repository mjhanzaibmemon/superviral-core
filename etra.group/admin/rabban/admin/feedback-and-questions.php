<?php



function ago($time)
{
    $periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
    $now = time();
    $difference     = $now - $time;
    $tense         = 'ago';
    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
        $difference /= $lengths[$j];
    }
    $difference = round($difference);
    if ($difference != 1) {
        $periods[$j] .= "s";
    }
    return "$difference $periods[$j] ago";
}




include('adminheader.php');
$_POST['readQuestion'] = addslashes($_POST['readQuestion']);
$_POST['readFeedback'] = addslashes($_POST['readFeedback']);

if (isset($_POST['readQuestion'])) {

    $q = mysql_query("UPDATE user_questions SET checked='1' WHERE id='$_POST[readQuestion]'");
}

if (isset($_POST['readFeedback'])) {
    $q = mysql_query("UPDATE feedbacks SET reviewed='1' WHERE id='$_POST[readFeedback]'");
}

$q = mysql_query("SELECT *  FROM `feedbacks` WHERE reviewed = '0' ORDER BY `id` DESC");
$feedbackData = "";
while ($info = mysql_fetch_array($q)) {


    $feedbackData .= '
    <tr>
    <td>' . $info['email'] . '</td>
    <td></td>

    <td>' . $info['feedback'] . '</td>
    <td></td>

    <td>' . ago($info['createdAt']) . '</td>';
    $feedbackData .= '<td><form href="' . $siteDomain . '/admin/feedback-and-questions.php" method ="POST"><input type="hidden" name="readFeedback" value="' . $info['id'] . '"><button class="btn btn3" style="margin: 0px !important;" href="#0" onclick="return confirm(\'Are you sure you read it?\');" >Read</button></form></td>';
    '</tr>';
}

$q = mysql_query("SELECT *  FROM `user_questions` WHERE Checked = '0' ORDER BY `id` DESC");
$questionData = "";
while ($info = mysql_fetch_array($q)) {


    $questionData .= '
                    <tr>
                      <td style="width:150px;font-size:14px;">' . $info['page'] . '</td>

                      <td>' . $info['question'] . '</td>

                      <td>' . ago($info['createdAt']) . '</td>';
    $questionData .= '<td><form href="' . $siteDomain . '/admin/feedback-and-questions.php" method ="POST"><input type="hidden" name="readQuestion" value="' . $info['id'] . '"><button class="btn btn3" style="margin: 0px !important;" href="#0" onclick="return confirm(\'Are you sure you read it?\');" >Read</button></form></td>';
    '</tr>';
}


?>
<!DOCTYPE html>

<head>
    <title>Feedbacks & Questions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/orderform.css">

    <style type="text/css">
        .btn {
            width: 100px;
            text-align: center;
        }

        .alignthis {
            padding: 55px 0;
        }


        .box23 {
            margin: 66px auto;
            width: 950px;
            
            border-radius: 5px;
            text-align: left;
            padding: 15px;
        }

        .box23 table tr td{background: #fff;
    padding: 13px 12px;}

        .box23 
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.6.0/chart.min.js"></script>
</head>

<body>


    <?= $header ?>
    <div class="alignthis" align="center">
        <h1 style="color:unset;">Feedbacks And Questions</h1>
    </div>

    <div class="alignthis" align="center">
        <h2>Feedbacks</h2>
        <div class="box23">

            <table style="width:100%">
                <?= $feedbackData ?>
            </table>

        </div>
    </div>


    <div class="alignthis" align="center">
        <h2>Questions</h2>

        <div class="box23">

            <table style="width:100%">
                <?= $questionData ?>
            </table>

        </div>

    </div>


</body>

</html>