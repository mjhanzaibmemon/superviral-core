<?php


include('adminheader.php');

date_default_timezone_set('Europe/London');

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

// country

$country = mysql_query("SELECT id,country FROM `content` group by `country`");


$countryHtml = "";
while ($countryData = mysql_fetch_array($country)) {

    $countryHtml .= '<option value="' . $countryData['country'] . '">' . $countryData['country'] . '</option>';
}

// page

$page = mysql_query("SELECT id,`page` FROM `content` group BY `page`;");

$pageHtml = "";
while ($pageData = mysql_fetch_array($page)) {

    $pageHtml .= '<option value="' . $pageData['page'] . '">' . $pageData['page'] . '</option>';
}

// country

$name = mysql_query("SELECT id,`name` FROM `content` group BY `name`;");

$nameHtml = "";
while ($nameData = mysql_fetch_array($name)) {

    $nameHtml .= '<option value="' . $nameData['name'] . '">' . $nameData['name'] . '</option>';
}

?>
<!DOCTYPE html>

<head>
    <title>Website Structure</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/orderform.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>
    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
    <style type="text/css">
        .box23 {
            margin: 66px auto;
            width: 95%;
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
            border-bottom: 1px solid #f1f1f1;
            padding: 19px 10px;
            vertical-align: top
        }

        .articles tr td:first-child {
            font-size: 19px;
            width: 34%;
            vertical-align: middle;
        }

        .articles tr:last-child td {
            border-bottom: 0;
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
        }


        .adminmenu {
            display: inline-block;
            background-color: white;
            border-top: 1px solid #ccc;
            width: 100%;
        }

        .adminmenu a {
            float: left;
            padding: 15px;
        }

        .perorder {
            width: 100%;
        }

        .perorder tr:first-child td {
            background-color: #ccc;
            font-weight: bold;
            font-size: 20px;
        }

        .perorder tr td:first-child {
            width: 30%;
            vertical-align: top;
        }

        .perorder tr td {
            padding: 14px 5px;
            border-bottom: 1px solid #e0e0e0;
        }

        .perorder tr.grey td {
            color: grey;
        }

        .perorder a {
            text-decoration: underline;
            color: blue;
        }

        .trackinginfo {
            border-bottom: 1px dashed #e8e8e8;
            margin-bottom: 2px;
            padding: 11px;
            font-size: 14px;
            color: grey;
        }

        .trackinginfo .trackingheader {
            font-weight: bold;
        }

        .report {
            float: left;
            width: initial;
            margin-right: 10px !important;
            border: 1px solid black !important;
            color: black !important;
            text-decoration: none !important;
        }

        .reportmessage {
            float: left;
            width: 100%;
            height: 120px;
            box-sizing: border-box;
            margin: 0px;
            margin-bottom: 20px;
            resize: vertical;
            padding: 10px;
            font-family: 'Open Sans';
        }

        .adminnotif {
            font-size: 15px;
            padding: 11px;
            margin-bottom: 10px;
        }

        .language-less {
            width: 1px;
            height: 1px;
            resize: none;
        }

        .foo {
            display: inline-block;
            width: 100%;
            margin-bottom: 18px;
        }

        .rectifyinput {
            width: 181px;
            float: left;
            margin-top: 0;
            margin-right: 10px;
        }

        .summarytbl {
            font-size: 14px;
        }

        .summarytbl tr:hover {
            background-color: #e4fbff;
        }

        .summarytbl tr td {
            border-bottom: 1px solid #dadada;
            padding: 7px;
        }

        .searchspan {
            font-size: 13px;
            color: #4747bf;
            line-height: 22px;
            display: block;
        }
    </style>
    <script type="text/javascript">


    </script>
</head>

<body>


    <?= $header ?>


    <h1 style="text-align:center;margin-top:35px;">Browse Website Structure</h1>

    <div class="box23">



        <form method="POST" action="#">
            <table class="articles">

                <tr>

                    <td style="font-size: 16px;">Country:</td>
                    <td>Page:</td>
                    <td>Name:</td>
                </tr>
                <tr>
                    <td>
                        <select name="country" id="country" class="input" onchange="getContent();getPageName();">
                            <option value="">Select</option>
                            <?= $countryHtml ?>
                        </select>
                        <span id="countryError" style="color: red;font-weight: 600;font-size: 15px;"></span>
                    </td>
                    <td>
                        <select name="page" id="page" class="input" onchange="getPageName();">
                            <option value="">Select</option>
                            <?= $pageHtml ?>
                        </select>
                        <span id="pageError" style="color: red;font-weight: 600;font-size: 15px;"></span>
                    </td>
                    <td>
                        <select name="name" id="name" class="input" onchange="getContent();getWWContent();">
                            <option value="">Select</option>
                            <?= $nameHtml ?>
                        </select>
                        <span id="nameError" style="color: red;font-weight: 600;font-size: 15px;"></span>
                    </td>
                </tr>

                <!-- <tr>

					<td></td>
					<td><input style="float:left;" type="submit" name="submit" class="btn color3" value="Search"><a href="https://superviral.io/admin/check-user.php" class="btn btn3 report" style="float:right;">Reset Search</a>
					<a href="https://superviral.io/admin/no-order-report.php" class="btn btn3 report" style="float:right;">Submit No-Order Report</a></td>

				</tr> -->

            </table>

        </form>




    </div>

    <div class="box23">



        <form method="POST" action="#">
            <table class="articles">

                <tr>

                    <td style="font-size: 16px;">WW Version</td>
                    <td>UK version</td>
                    <td>Updated By</td>
                </tr>
                <tr>

                    <td style="font-size: 16px;" id="wwVersionHdngId"></td>
                    <td id="ukVersionHdngId"></td>
                    <td id="contentBox3"></td>
                </tr>
                <tr>
                    <td>
                        <div id="contentBox1"></div>
                    </td>
                    <td>
                        <textarea name="" class="input" id="contentBox2" rows="15"></textarea>
                    </td>
                    
                    <td>

                    </td>
                </tr>

                <tr>

                    <td></td>
                    <td>
                        <a style="float:left;width: 140px;" onclick="finishEditing();" class="btn color3">Finish Editing</a>
                        <span id="finishSucess" style="color: green;font-size: 15px;font-weight: 600;float: left;margin-left: 10px;margin-top: 10px;"></span>
                    </td>
                    <td>
                        <a style="background: white;width: 120px;float: right;color: black;" onclick="deleteContent();" class="btn color3">Delete</a>
                    </td>
                </tr>

            </table>
        </form>





    </div>

    <div class="box23">



        <form method="POST" action="#">
            <table class="articles">

                <tr>

                    <td style="font-size: 16px;">Content</td>
                    <td>Last Updated</td>
                    <td>Action</td>
                </tr>
                <tbody id="bindHistoryContent">

                </tbody>


            </table>
        </form>





    </div>


    <script>
        function getPageName() {
            var country = $("#country").val().trim();
            var page = $("#page").val().trim();

            if (country == "") {
                $('#countryError').html('Please select country');
            }
            if (page == "") {
                $('#pageError').html('Please select page');
            }
            // $("#cover-spin").show();
            $.ajax({

                url: '/admin/language_aligment_handler.php',
                type: 'POST',
                data: {
                    'type': 'getPageName',
                    'page': page,
                    'country': country,
                },
                dataType: 'json',
                success: function(data) {
                    $("#name").html('');

                    if (data != null) {
                        if (data.message) {
                            alert(data.message);
                            return;
                        }

                        var i;
                        var htm = '<option value="">Select</option>';
                        $("#name").append(htm);

                        for (i = 0; i < data.length; i++) {

                            htm = '<option value="' + data[i].name + '">' + data[i].name + '</option>';
                            $("#name").append(htm);
                        }
                    } else {
                        htm = '<option value="">Select</option>';
                        $("#name").append(htm);
                    }

                    // $("#cover-spin").hide();
                },

            });
        }

        $("#country").change(function() {

            if ($(this).val() != "") {
                $('#countryError').html('');
            }

        });

        $("#page").change(function() {

            if ($(this).val() != "") {
                $('#pageError').html('');
            }

        });

        $("#name").change(function() {

            if ($(this).val() != "") {
                $('#nameError').html('');
            }

        });


        function getContent() {
            var country = $("#country").val().trim();
            var page = $("#page").val().trim();
            var name = $("#name").val().trim();

            $("#ukVersionHdngId").html(name);

            if (country == "") {
                $('#countryError').html('Please select country');
            }
            if (page == "") {
                $('#pageError').html('Please select page');
            }
            if (name == "") {
                $('#nameError').html('Please select name');
            }
            // $("#cover-spin").show();
            $.ajax({

                url: '/admin/language_aligment_handler.php',
                type: 'POST',
                data: {
                    'type': 'getContent',
                    'page': page,
                    'country': country,
                    'name': name,
                },
                dataType: 'json',
                success: function(data) {
                    $("#contentBox2").val('');

                    if (data != null) {
                        if (data.message) {
                            alert(data.message);
                            return;
                        }


                        $("#contentBox2").val(data.content);
                        $("#contentBox3").html(data.updated_by);
                    } else {
                        $("#contentBox2").val('Not Found');
                    }

                    // $("#cover-spin").hide();
                },

            });

            getHistoryContent();
        }

        function getWWContent() {
            var page = $("#page").val().trim();
            var name = $("#name").val().trim();

            $("#wwVersionHdngId").html(name);

            if (page == "") {
                $('#pageError').html('Please select page');
            }
            if (name == "") {
                $('#nameError').html('Please select name');
            }
            // $("#cover-spin").show();
            $.ajax({

                url: '/admin/language_aligment_handler.php',
                type: 'POST',
                data: {
                    'type': 'getWWContent',
                    'page': page,
                    'name': name,
                },
                dataType: 'json',
                success: function(data) {
                    $("#contentBox1").html('');

                    if (data != null) {
                        if (data.message) {
                            alert(data.message);
                            return;
                        }

                        $("#contentBox1").html(data.content);
                    } else {
                        $("#contentBox1").html('Not Found');
                    }

                    // $("#cover-spin").hide();
                },

            });
        }

        function finishEditing() {
            var contentBoxVal = $("#contentBox2").val().trim();
            var country = $("#country").val().trim();
            var page = $("#page").val().trim();
            var name = $("#name").val().trim();

            $("#ukVersionHdngId").html(name);

            if (contentBoxVal == "") {
                alert('Content value can\'t be blank');
                return;
            }
            if (country == "") {
                $('#countryError').html('Please select country');
                return;
            }
            if (page == "") {
                $('#pageError').html('Please select page');
                return;
            }
            if (name == "") {
                $('#nameError').html('Please select name');
                return;
            }
            // $("#cover-spin").show();
            $.ajax({

                url: '/admin/language_aligment_handler.php',
                type: 'POST',
                data: {
                    'type': 'finishEditing',
                    'page': page,
                    'country': country,
                    'name': name,
                    'content': contentBoxVal
                },
                dataType: 'json',
                success: function(data) {

                    if (data != null) {
                        $("#finishSucess").html(data.message);
                        getHistoryContent();
                    }
                    setTimeout(function(){
                        $("#finishSucess").html('');
                    },2000)

                    // $("#cover-spin").hide();
                },

            });

        }

        function deleteContent() {
            var country = $("#country").val().trim();
            var page = $("#page").val().trim();
            var name = $("#name").val().trim();

            if (country == "") {
                $('#countryError').html('Please select country');
            }
            if (page == "") {
                $('#pageError').html('Please select page');
            }
            if (name == "") {
                $('#nameError').html('Please select name');
            }
            // $("#cover-spin").show();
            $.ajax({

                url: '/admin/language_aligment_handler.php',
                type: 'POST',
                data: {
                    'type': 'deleteContent',
                    'page': page,
                    'country': country,
                    'name': name,
                },
                dataType: 'json',
                success: function(data) {

                    if (data != null) {
                        alert(data.message);
                    }


                    // $("#cover-spin").hide();
                },

            });

        }

        function getHistoryContent() {

            var country = $("#country").val().trim();
            var page = $("#page").val().trim();
            var name = $("#name").val().trim();

            // $("#cover-spin").show();
            $.ajax({

                url: '/admin/language_aligment_handler.php',
                type: 'POST',
                data: {
                    'type': 'getHistoryContent',
                    'page': page,
                    'country': country,
                    'name': name,
                },
                dataType: 'json',
                success: function(data) {
                    $("#bindHistoryContent").html('');

                    if (data != null) {

                        if (data.message) {
                            alert(data.message);
                            return;
                        }

                        var htm = "";
                        var i;
                        for (i = 0; i < data.length; i++) {
                            htm += '<tr>' +
                                '<td id="contentId' + i + '">' + data[i].content + '</td>' +
                                '<td>' + data[i].time + '</td>' +
                                '<td><a style="float:left;width: 100px;" onclick="restoreContent(' + i + ');" class="btn color3">Restore</a></td>' +
                                '</tr>';
                        }
                        $("#bindHistoryContent").html(htm);
                    } else {
                        $("#bindHistoryContent").html('No History Found');
                    }

                    // $("#cover-spin").hide();
                },

            });
        }

        function restoreContent(key) {
            var val = $('#contentId' + key).html().trim();

            $("#contentBox2").val(val);

        }
    </script>

</body>

</html>