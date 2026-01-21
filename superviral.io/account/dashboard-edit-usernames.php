<?php


$db=1;
include('header.php');
include('auth.php');


$id= addslashes($_GET['id']);
$val= addslashes($_GET['val']);

if($id != "" && $id !=null){

  $updateQuery = mysql_query("UPDATE account_usernames SET active = $val WHERE id = $id LIMIT 1");

}

$userList = "";

$accountId = $userinfo['id'];

$userListQuery = "SELECT * FROM account_usernames WHERE account_id = $accountId AND `brand`='sv' AND active = 1";
$userListQueryRun = mysql_query($userListQuery);
$countUserActiveList = mysql_num_rows($userListQueryRun);



$userListQuery = "SELECT * FROM account_usernames WHERE account_id = $accountId AND `brand`='sv'";

$userListQueryRun = mysql_query($userListQuery);
$countUserList = mysql_num_rows($userListQueryRun);
$i= 1;
while($data = mysql_fetch_array($userListQueryRun)){
  if ($data['active']== 1){

      $chkd = "checked";

      if($countUserActiveList > 1){
        $disabled = "";
      }else{
        $disabled = "disabled";
      }

  }else{
      $chkd = "";
      $disabled = "";
  }
  $userList .= '<li><img src="/imgs/ig-icon-input.svg" />@'. $data['username'] .'<a class="deletethis" href="#">
                <label class="switch">
                    <input type="checkbox" onchange = "callFuncUser(this)" data-id = "'. $data['id'] .'" data-val = "'. $data['active'] .'"  id ="toggleSwitchUser'. $data['id'] .'" '. $chkd .' '. $disabled.'>
                    <span class="slider round"></span>
                </label></a>
                </li>';
$i++;
}




?>
<!DOCTYPE html>
<head>
<title>Superviral</title>
<meta name="description" content="" />
<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1.0, user-scalable=no">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.min.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">
</head>

  <body>

<style type="text/css">
h1{    text-align: left;
    font-size: 23px;
    width: 100%;
    display: block;
    max-width: 100%;}

.competitorholder{margin-top: 10px}

.competitorlists{    list-style: none;
    margin: 0;
padding: 0;}
.competitorlists li{    border-bottom: 1px dashed #cfcfcf;
    padding: 13px;
    padding-left: 40px;
    position: relative;
    color: blue;
    font-weight: bold;}
.competitorlists li img{    width: 21px;
    position: absolute;
    left: 6px;
    top: 13px;
    fill: #ccc;}

.deletethis{    position: absolute;
    right: 0px;
    top: 9px;}

.addcompetitorholder{    margin-top: 15px;position: relative;}

.addcompetitorholder img{    position: absolute;
    top: 46px;
    left: 12px;
    width: 21px;}
.container .addcompetitorholder input{    padding-left: 40px;
    font-size: 15px;}

.addthiscompetitorbtn{font-size: 15px;padding: 10px;text-align: center;font-weight: bold;    margin-bottom: 12px;}

.nothankyou{    text-decoration: underline;
    width: 100%;
    display: block;
    text-align: center;
      font-size: 14px;margin-top: 20px;}




 .container label.switch {
  position: relative;
  display: inline-block;
width: 50px;
     height: 27px;
  margin: 0;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
     position: absolute;
    content: "";
    height: 19px;
    width: 18px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
  display: initial;
  margin: 0;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(24px);
  -ms-transform: translateX(24px);
  transform: translateX(24px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
  margin: 0;
}

.slider.round:before {
  border-radius: 50%;
}


</style>
  <script>
    document.body.className += ' variation-' + window.chosenVariation;

    function callFuncUser(elem){  

      let id = $(elem).attr('data-id');
      let val = $(elem).attr('data-val');
      if(val == 0) val = 1; else val = 0;
      window.location.href= "dashboard-edit-usernames.php?id=" + id + "&val=" + val;

    }
  </script>

		<div  class="orderbody" align="center">


			<div class="cnwidth">


				<div class="container tycontainer dshadow">

					<h1>Edit your Instagram usernames</h1>
     
					<div class="competitorholder">
								
							<ul class="competitorlists">
							<?= $userList; ?>
							</ul>

					</div>


                
					<a href="?closeandrefreshcompetitors=true" onclick="parent.closeeditusernames();" class="nothankyou">Close</a>
					

				</div>




			</div>

		</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script> 


  </body>
</html>