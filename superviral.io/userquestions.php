<?php

include('db.php');

$question = addslashes($_POST['question']);
$page = addslashes($_GET['page']);


//die( 'DONE! SENT EMAIL');

if((empty($question))&&(!empty($page))){



$heading = '<h1 class="firsth1">Suggest a question</h1>';

$text = 'Please enter your question you would like an answer to:';

$cta = '<div class="cta">

			<form method="POST" action="userquestions.php?page='.$page.'&submit=true">
				<input class="input inputcontact" name="question" style="padding: 13px 10px;
                font-size: 15px;" placeholder="Enter your question">
				<input class="btn color3" type="submit" name="submit" value="Ask this question" style="margin: 10px 0 35px 0!important;">
			
			</form>
		</div>';

}

if((!empty($_POST['submit']))){

    if((!empty($question))&&(!empty($page))){

        $text = "";
        $heading = '<h1 class="firsth1">Suggest a question</h1>';
        $now = time();
        $res = mysql_query("INSERT INTO `user_questions` (question, `page`, `createdAt`, `brand`) VALUES ('$question', '$page', $now, 'sv')");
        //echo "INSERT INTO `user_questions` (question, `page`, `createdAt`, `brand`) VALUES ('$question', '$page', $now, 'sv')";die;


        if($res){
           echo "<script>parent.closequestionDiv(\"Question submitted successfully - thank you for the feedback!\");</script>";
            //$text = "<span style='color: green;'>Question submitted successfully</span><br/>";
        }else{
            $text = "<span style='color: red;'>Technical Error!</span><br/>";
        }
        $text .= 'Please enter your question:';

        
        $cta = '<div class="cta">
                    <form method="POST" action="userquestions.php?page='.$page.'">
                        <input class="input inputcontact" name="question" style="padding: 13px 10px;
                        font-size: 15px;" placeholder="Enter your question">
                        <input class="btn color3" type="submit" name="submit" value="Submit" style="margin: 10px 0 35px 0!important;">
                    
                    </form>
                </div>';


    }
    else if((empty($question))){
        $heading = '<h1>Suggest a question</h1>';
        $text = "<span style='color: red;'>Question can't be blank</span><br/>";
        $text .= 'Please enter your question you would like an answer to:';
        
        $cta = '<div class="cta">
        
                    <form method="POST" action="userquestions.php?page='. $page .'">
                        <input class="input inputcontact" name="question" style="padding: 13px 10px;
                         font-size: 15px;" placeholder="Enter your question">
                        <input class="btn color3" type="submit" name="submit" value="Submit" style="margin: 10px 0 35px 0!important;">
                    
                    </form>
                </div>';
    }
}





?>
<!DOCTYPE html>
<head>
<title>Add a question</title>
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Language" content="en-gb">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://superviral.io/css/buystyle.min.css">
<link rel="stylesheet" type="text/css" href="https://superviral.io/css/orderform.css">
<style type="text/css">

	.bodypadding{width:100%;padding:15px 25px;box-sizing: border-box;}

	.heading{text-align: center;position: relative;}

	.h1{
    font-size: 39px;text-align: center;font-weight:bold;display:block;    font-family: "Source Sans Pro", sans-serif;}

    .text{margin-top: 32px;
    font-size: 14px;
    line-height: 27px;}

    .text ol li{margin-bottom:15px;}

    .cta{    margin-top: 2px;}

	@media only screen and (min-width: 768px){
		h1{font-size:43px;}

	}

    @media only screen and (min-width:992px){


    }

    @media only screen and (min-width:1200px){

    }

    @media only screen and (min-width:1500px){

    }


</style>	


</head>

	<body>

	<div class="bodypadding">
		<div class="heading color3 textcolor3">
			<?=$heading?>
		</div>

		<div class="text"><?=$text?></div>

		<?=$cta?>

	</div>
<script>
   
</script>

	</body>
</html>