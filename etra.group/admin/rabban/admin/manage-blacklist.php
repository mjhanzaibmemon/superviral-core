<?php


include('adminheader.php');

$thisstaffmember = addslashes($_SESSION['admin_user']);

date_default_timezone_set('Europe/London');

function ago($time)
{$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");
   $now = time();
       $difference     = $now - $time;
       $tense         = 'ago';
   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }
   $difference = round($difference);
   if($difference != 1) {
       $periods[$j].= "s";
   }   return "$difference $periods[$j] ago";}

$user = addslashes(trim($_POST['user']));
$email = addslashes(trim($_POST['email']));
$ip = addslashes(trim($_POST['ip']));
$search = addslashes(trim($_POST['search']));
$create = addslashes($_POST['type']);

$id = addslashes($_GET['id']);

// delete
if(isset($id) && !empty($id)){

    $q = mysql_query("SELECT * FROM `blacklist` WHERE id = $id LIMIT 1");
    $val = mysql_fetch_array($q);
    $email = $val['emailaddress'];
    $user = $val['igusername'];
    $ip = $val['ipaddress'];


    mysql_query("DELETE FROM `blacklist` WHERE emailaddress = '$email' OR igusername = '$user' OR ipaddress = '$ip'");
    header('Location: /admin/manage-blacklist.php');
}



// insert
$time = time();
if(isset($create) && $create == "createBlacklist"){
    if((!empty($user) || !empty($email) || !empty($ip))){

        $query = "INSERT INTO `blacklist` SET 
        `emailaddress` = '$email', 
        `igusername` = '$user', 
        `ipaddress` = '$ip',
        `added` = '$time'";

        $q = mysql_query($query);  
        if($q){
            
            $error ='<div style="color:green;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Added Successfully!!</div>';

            echo json_encode(['msg' => $error]);
            die;
        }else{

            $error ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Something went wrong!!</div>';
            echo json_encode(['msg' => $error]);
            die;
        }


    }
    else{
        
            $error ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Input can\'t be blank</div>';
            echo json_encode(['msg' => $error]);
            die;
        

    }

}

// SEARCH
if(isset($search) && !empty($search)){
    if((!empty($user) || !empty($email) || !empty($ip))){

        if(!empty($user)) $user = $user; else $user = " ";
        if(!empty($email)) $email = $email; else $email = " ";
        if(!empty($ip)) $ip = $ip; else $ip = $ip = " ";

        $query = "SELECT * FROM `blacklist` WHERE 
        `emailaddress` LIKE '%$email%' 
        OR `igusername` LIKE '%$user%' 
        OR `ipaddress` LIKE '%$ip%' 
        ORDER BY `id` DESC";

        $q = mysql_query($query);  
        $data = ""; 

        if(mysql_num_rows($q) > 0){
        
        
        
            while($info = mysql_fetch_array($q)){
            
                $data .='<tr>
				<td>'. $info['ipaddress'] .'</td>
				<td>'. $info['emailaddress'] .'</td>
				<td>'. $info['igusername'] .'</td>
				<td><a href="?id='. $info['id'] .'" onclick="return confirm(\'Are you sure you want to delete?\');">Delete</a></td>
				</tr>';
            
            }


        }else{
        
            $error ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">No Records Found! </div>';
        
        }

        }else{
        
            $error ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Input can\'t be blank</div>';
        
        }
}


if(!empty($data))$data = '<div class="box23"><table class="summarytbl" style="width:100%"><tr>
	<td>Ip</td>
	<td>Email</td>
	<td>User</td>
	<td>Action</td>
	</tr>'.$data.'</table></div>';

if(empty($data))$data = '';

?>
<!DOCTYPE html>

<head>
    <title>Manage Blacklist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/orderform.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>
    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
    <style type="text/css">
    <?=$dontdosupportcss?>.box23 {
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

    /* .summarytbl{font-size:14px;} */
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

      /* The Modal (background) */
      .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
                z-index: 15000;
            /* Sit on top */
            padding-top: 100px;
            /* Location of the box */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgb(0, 0, 0);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black w/ opacity */
        }

        /* Modal Content */
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            display: flow-root;
        }

        /* The Close Button */
        .close {
            color: #aaaaaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

    <?=$styles?>
    </style>
</head>

<body>
    <!-- The Modal -->
    <div id="submitReportModal" class="modal">

        <!-- Modal content -->
        <div class="modal-content" style="max-height: 600px;overflow-y: auto;">
            <span class="close" style="margin-top:-10px">&times;</span>
            <h3 class="fontBold">Create Blacklist</h3>

            <div id="msgDisplay"></div>
            <table class="articles">

                <tr>

                    <td>Username</td>
                    <td><input id="user" class="input" value="" autocomplete="off"></td>

                </tr>

                <tr>

                    <td>Email</td>
                    <td><input id="email" class="input" value="" autocomplete="off"></td>

                </tr>
                <tr>

                    <td>IP</td>
                    <td><input id="ip" class="input" value="" autocomplete="off"></td>

                </tr>

            </table>
            <button class="btn color4" style="width:100px" onclick="createBlacklist();">Create</button>
        </div>

    </div>


    <?=$header?>


    <h1 style="text-align:center;margin-top:35px;">❌ Manage Blacklists</h1>


    <?=$dontdosupportdiv?>

    <div class="box23">



        <form method="POST" action="">
            <?= $error ?>
            <table class="articles">

                <tr>

                    <td>Username</td>
                    <td><input name="user" class="input" value="" autocomplete="off"></td>

                </tr>

                <tr>

                    <td>Email</td>
                    <td><input name="email" class="input" value="" autocomplete="off"></td>

                </tr>
                <tr>

                    <td>IP</td>
                    <td><input name="ip" class="input" value="" autocomplete="off"></td>

                </tr>
                <tr>

                    <td></td>
                    <td>
                        <button style="float:left;width: 150px;" name="create" class="btn btn3 modal-button" href="#submitReportModal">Create Blacklist</button>
                        <input style="float:right;" type="submit" name="search" class="btn btn4" value="Search">
                    </td>
                </tr>

            </table>

        </form>




    </div>

    <?=$data?>

    <script>
    function createBlacklist() {
        var email = $('#email').val();
        var ip = $('#ip').val();
        var user = $('#user').val();
        if (email == "" && ip == "" && user == "") {
            alert("Inputs can't be blank");
            return;
        }
        $.ajax({

            url: '/admin/manage-blacklist.php',
            type: 'POST',
            data: {
                'email': email,
                'ip': ip,
                'user': user,
                'type' : 'createBlacklist'
            },
            dataType: 'json',
            success: function(data) {

                $('#msgDisplay').html(data.msg);
                $('#email').val('');
                $('#user').val('');
                $('#ip').val('');
                setTimeout(function(){
                    $('#msgDisplay').html('');
                },3000)
            },

        });
    }

    var btn = document.querySelectorAll("button.modal-button");

// All page modals
var modals = document.querySelectorAll('.modal');

// Get the <span> element that closes the modal
var spans = document.getElementsByClassName("close");
// When the user clicks the button, open the modal
for (var i = 0; i < btn.length; i++) {
    btn[i].onclick = function(e) {
        e.preventDefault();
        modal = document.querySelector(e.target.getAttribute("href"));
        modal.style.display = "block";
    }
}

// When the user clicks on <span> (x), close the modal
for (var i = 0; i < spans.length; i++) {
    spans[i].onclick = function() {
        for (var index in modals) {
            if (typeof modals[index].style !== 'undefined') modals[index].style.display = "none";
        }
    }
}
    </script>

</body>

</html>