<?php
include_once('../db.php');
include('auth.php');
include('header.php');


$shortcode = addslashes($_GET['shortcode']);

if (time() > strtotime("today 06:00:00")) { // if current time greater than 06:00
    // echo "yes";
    $daycats = strtotime("today", time()) + 86400; //add +1 day
} else {
    // echo "No";
    $daycats = strtotime("today", time());
}

$daysago = 7;

for ($x = $daysago; $x >= 1; $x--) {

    $newdate = date('dmY', $daycats - (86400 * $x));


    // $labels .= $labels[$newdate];
    $labelsbackend .= "'" . date('dS D', $daycats - (86400 * $x)) . "',";
}
$labels = rtrim($labels, ',');
$labelsbackend = rtrim($labelsbackend, ',');

$q1 = mysql_query("SELECT dates, likes
from
(
  select DATE(FROM_UNIXTIME(cs.added)) AS dates, cs.likes AS likes
  from ig_thumbs ig INNER JOIN checkposts_stats cs ON ig.id = cs.checkposts_id
  WHERE DATE(FROM_UNIXTIME(cs.added)) BETWEEN date_add(curdate(), INTERVAL -6 day) 
  AND CURDATE() 
  AND ig.shortcode = '$shortcode'
   group by DATE(FROM_UNIXTIME(cs.added))
  union all
  select CURDATE(), 0 
  union all
  select date_add(curdate(), interval -1 DAY), 0 
  union all
  select date_add(curdate(), interval -2 DAY), 0 
  union all
  select date_add(curdate(), interval -3 DAY), 0 
  union all
  select date_add(curdate(), interval -4 DAY), 0 
  union all
  select date_add(curdate(), interval -5 DAY), 0
  union all
  select date_add(curdate(), interval -6 DAY), 0
) x
group BY x.dates
order BY x.dates");

while ($info1 = mysql_fetch_array($q1)) {

    $likes .= $info1["likes"] . ",";
}

$likes = rtrim($likes, ',');



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dashboard - Superviral</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style.min.css">
    <link rel="stylesheet" type="text/css" href="/css/accountstyle.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.6.0/chart.min.js"></script>


    <style type="text/css">
        
.nothankyou{    text-decoration: underline;
    width: 100%;
    display: block;
    text-align: center;
      font-size: 14px;}

        .loader {
            border: 6px solid #f3f3f3;
            border-radius: 50%;
            border-top: 6px solid #3498db;
            width: 20px;
            height: 20px;
            -webkit-animation: spin 2s linear infinite;
            /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap');

        body {
            background: #f2f2f2;
        }

        .display-section,
        .social-status-section,
        .competitors-section,
        .order-history-section,
        .auto-likes-section {
            margin-bottom: 65px;
        }

        .container1 {
            width: 100%;
            max-width: 800px;
            margin: auto;
            padding: 10px 20px;
            box-sizing: border-box;
        }

        .container1 h3,
        button {
            font-family: "Source Sans Pro", sans-serif;
            font-size: 24px;
        }

        img {
            width: 100%;
        }

        a {
            text-decoration: none;
        }

        .page-header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        /* Navigation Section End Here */

        /* Display Section Start Here */
        .display-section {
            padding: 10px 0;
        }

        .display-container {
            display: grid;
            grid-template-columns: 1fr;
            position: relative;
            margin-bottom: 16px;
        }

        .display-content {
            display: block;
            justify-content: center;
            align-items: center;
            gap: 10px;
            height: 90px;
            padding: 15px;
            border-radius: 10px;
        }

        .display-content img {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            position: absolute;
            left: 0;
        }

        .display-rating-holder .fullinformation {
            display: none;
        }

        .display-rating-holder-active .fullinformation {
            display: block;
            margin-top: -30px;
        }

        @media only screen and (min-width: 768px) {
            .changeusernametabs {
                padding-left: 23px;
            }

            .display-rating-holder-active .fullinformation {
                padding: 15px;
                padding-bottom: 30px;
            }

        }

        @keyframes fadein {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Firefox < 16 */
        @-moz-keyframes fadein {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Safari, Chrome and Opera > 12.1 */
        @-webkit-keyframes fadein {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Internet Explorer */
        @-ms-keyframes fadein {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>


    <section class="display-section">
        <div class="container1">
            <div class="display-container">

                <div class="fullinformation">


                    <div class="alignthis" align="center">
                        <h2>Post Stats</h2>
                        <canvas id="viewPostChart" style="width:100%;height:400px"></canvas>
                    </div>

                    <a href="?closeandrefreshcompetitors=true" onclick="parent.closeviewStatsDiv();" class="nothankyou">Close</a>
                </div>
            </div>
        </div>

    </section>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- <script src="./js/index.js"></script> -->
    <script>
        const ctx = document.getElementById('viewPostChart').getContext('2d');
        const viewPostChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?= $labelsbackend ?>],
                datasets: [{
                    label: '',
                    data: [<?= $likes ?>],
                    borderColor: '#50C878',
                    backgroundColor: '#50C878',
                }, ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>