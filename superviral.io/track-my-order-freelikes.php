<?php

$db=1;
include('header.php');

$hash = addslashes($_GET['hash']);


$notextupdate = addslashes($_GET['notextupdate']);
if($notextupdate=='true'){
$therefresh = '<script>window.top.location.reload();</script>';
echo $therefresh;die;
}

$q = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '$hash' AND `freelikes` = '0' LIMIT 1");
if(mysql_num_rows($q)=='0'){die('Free likes already claimed - You can get more Instagram Likes on Superviral!');}


$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';


$info = mysql_fetch_array($q);


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
h2{text-align: left;font-size:17px;margin:5px 0;}
p{text-align: left;margin-bottom:30px;color:#525252;font-size:15px;    line-height: 26px;}
ul{    padding: 0;
    list-style: none;}
ul li{text-align: left;
    font-size: 13px;
    position: relative;
    padding-left: 28px;
    margin-bottom: 9px;
    line-height: 26px;}
.tick{    width: 20px;
    height: 20px;
    background: url(/imgs/all-images.png) no-repeat 0 -26px;
    display: block;
    position: absolute;
    left: 0;
    top: 3px;}

.btn{margin-bottom:15px;}
.container div span, .container label{display: initial;}

.nothankyou{    text-decoration: underline;
    width: 100%;
    display: block;
    text-align: center;
      font-size: 14px;}




  .orderstatus{    display: inline-block;
    width: 100%;
    padding: 10px;
    box-sizing: border-box;}
  .selectedposts{float: left;
    margin-top: 14px!important;
    color: #000!important;font-size:12px;}

    .selectedposts .amount_of_posts,.selectedposts .likes_per_post{ font-weight:bold;   display: unset!important; font-size: inherit!important;margin-top:0!important;color: #000;}
  
  .btn{width: 100%;
    text-align: center;
    margin: 0!important;
    padding: 9px 15px!important;
    font-size: 15px;}

  .image_checkboxes{overflow-y:scroll;height:275px;}
  .image_checkboxes div{position:relative;display:inline-block;width:30%;height:94px;float:left;border:2px solid transparent;overflow:hidden;    box-sizing: border-box;margin:4px;}
  .image_checkboxes div .amount{position:absolute;display:none;color: #fff;
      background: rgba(0,0,0,.65);
      height: 30px;
      text-align: center;
      box-sizing: border-box;
      width: 100%;margin:0;
    font-size: 14px;
      line-height: 25px;}
  .image_checkboxes div img{width:100%;height:100%;}
  .image_checkboxes .active{border-color:#4e03e0;}
  .image_checkboxes .active .amount{display:block;}

  .whichpackage{    text-align: center!important;
    font-size: 15px;
    color: #464646;}
    .whichpackage a{    color: #4e03e0;
    text-decoration: underline;}

.image_checkboxes .lds-dual-ring {
  display: inline-block;
      width: initial;
    height: initial;
    right: 50%;
    position: absolute;
}
.lds-dual-ring:after {
  content: " ";
  display: block;
  width: 64px;
  height: 64px;
  margin: 8px;
  border-radius: 50%;
  border: 6px solid #1a73e7;
  border-color: #1a73e7 transparent #1a73e7 transparent;
  animation: lds-dual-ring 0.6s linear infinite;
}


@keyframes lds-dual-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}



@media only screen and (min-width: 768px){
.container {

    padding: 37px 3%!important;
}

}


</style>
  <script>
    document.body.className += ' variation-' + window.chosenVariation;
  </script>

		<div  class="orderbody" align="center">


			<div class="cnwidth">


				<div class="container tycontainer dshadow">

					<h1>Get 50 Free Likes Instantly!</h1>
                    <h2>Please select your post:</h2>

      <form id="idform" method="POST" action="track-my-order-freelikes-2.php?hash=<?=$hash?>">
      
      <div id="loadpic" class="image_checkboxes">
              <div id="loadingdiv" class="lds-dual-ring"></div>
      </div>

        <div class="orderstatus">


        <div class="selectedposts" style="display:none;">


          <span class="amount_of_posts">0</span> <b>posts</b> Selected / <span class="likes_per_post">0</span> <b>likes</b> per post


        </div>


          <input type="hidden" id="posts_selected" name="posts_selected">
          <input type="submit" value="submit" style="display: none;">
        
          
        <a onclick="submitform();" href="#" class="color3 btn">Deliver Likes Now &raquo;</a>

      </div>

      <a href="?notextupdate=true&hash=<?=$order_session?>&id=<?=$id?>" class="nothankyou">I don't want 50 free likes</a>
      </form>
                


				</div>




			</div>

		</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script> 
<script type="text/javascript">

$("#loadpic").load("https://<?=$locredirect?>superviral.io/api/?datatype=thumbs&username=<?=$info['igusername']?>");



   var selected_list = JSON.parse('{}');
   var limit = 1;
   var max_amount_likes = 50;
   var amount_of_posts = Object.keys(selected_list).length;

    update_selected_list();    

   $('.image_checkboxes').on( "click", "div", function() {
      var image = this;
      var input = $(this).prev();
      var value = $(image).attr('data-value');
      
      if(value in selected_list){
        delete selected_list[value];
      }else{
        if(amount_of_posts >= limit){return max_limit_hit();}
        selected_list[value] = value
      }
      amount_of_posts = Object.keys(selected_list).length;
      
      update_selected_list();
  });

   function max_limit_hit(){
    alert('You can only select 1 post to give 50 likes.');
    return;
   }



   function update_selected_list(){
      var checkbox_cn = $('.img-responsive');
      $(checkbox_cn).each(function(){  
      var image = this;      
        var value = $(image).attr('data-value');
        if(value in selected_list){
          $(image).addClass('active');
        }else{
          $(image).removeClass('active');
        }
      });
      $('#posts_selected').val(JSON.stringify(selected_list));
      update_likes_statistics();
   }



  function update_likes_statistics(){  
    var likes_per_post =  Math.floor( max_amount_likes / amount_of_posts);
    

    if(likes_per_post == Infinity){likes_per_post=0;}

    $('.likes_per_post').html(likes_per_post);
    $('.amount_of_posts').html(amount_of_posts);
    $('.image_checkboxes div .amount').html('+' + likes_per_post + ' likes');
  }

function submitform(){

if(amount_of_posts=='0'){alert('Please select a post.');}else{document.getElementById('idform').submit();}

}


var parent = document.getElementById('container1');
var child = document.getElementById('container2');
child.style.paddingRight = child.offsetWidth - child.clientWidth + "px";






</script>

  </body>
</html>