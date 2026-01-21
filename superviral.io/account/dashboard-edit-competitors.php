<?php


$db=1;
include('header.php');
include('auth.php');

$errorMessage = "<span style='text-align:center;margin-top:10px'></span>";
$competitorsList = "";

$submit = addslashes($_POST['submit']);
$competitor = addslashes($_POST['newcompetitor']);
$accountId = $userinfo['id'];

$competitorId = addslashes($_GET['id']);

if(!empty($competitorId)){

  mysql_query("UPDATE account_competitors SET archive = 1 WHERE id = $competitorId");

}


if(isset($submit) && !empty($submit)){

  if(!empty($competitor)){
    
   
    $insertQuery = "INSERT INTO account_competitors SET competitor= '$competitor', account_id= $accountId";
    $res = mysql_query($insertQuery);
    if($res){
      
    }else{
      $errorMessage = "<span style='text-align:center;margin-top:10px;color:red'>Error!</span>";

    } 

   


  }else{
  $errorMessage = "<span style='text-align:center;margin-top:10px;color:red'>Please enter competitor name</span>";
  
  }

}

$competitorListQuery = "SELECT * FROM account_competitors WHERE account_id = $accountId AND archive = 0";

$competitorListQueryRun = mysql_query($competitorListQuery);
$countCompetitor = mysql_num_rows($competitorListQueryRun);
while($data = mysql_fetch_array($competitorListQueryRun)){
  $competitorsList .= '<li><img src="/imgs/ig-icon-input.svg" />@'. $data['competitor'] .'<a class="deletethis" href="dashboard-edit-competitors.php?id='. $data['id'] .'"><img src="/imgs/close-this.svg"></a></li>';

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
    right: 33px;
        top: 4px;}

 .competitorlists li .deletethis img{    width: 16px;}

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
      font-size: 14px;}

</style>
  <script>
    document.body.className += ' variation-' + window.chosenVariation;
  </script>

		<div  class="orderbody" align="center">


			<div class="cnwidth">


				<div class="container tycontainer dshadow">

					<h1>Choose upto 5 competitors</h1>
     
					<div class="competitorholder">
								
							<ul class="competitorlists">
                <?= $competitorsList ?>
							</ul>

					</div>
          <?= $errorMessage ?>
          <?php if($countCompetitor != 5) {?>
					<div class="addcompetitorholder">
            
          Add a new competitor:			
          <div class="cta">
         
			          <form method="POST" action="dashboard-edit-competitors.php">
                     
                    <img src="/imgs/ig-icon-input.svg" />
						        <input class="newcompetitor" type="input" name="newcompetitor">

						        <input type="submit" name="submit" class="color4 btn addthiscompetitorbtn" value="Add competitor" style="width:100%!important">

  		          </form>

		      </div>

					</div>
          <?php } ?>
					<a href="?closeandrefreshcompetitors=true" onclick="parent.closesignupDiv();" class="nothankyou">Close</a>
					

				</div>

			</div>

		</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script> 


  </body>
</html>