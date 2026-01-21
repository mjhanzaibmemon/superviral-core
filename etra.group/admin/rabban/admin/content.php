<?php

function getbetween($content,$start,$end){
$r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}



include('adminheader.php');

$countries = array(
	'ww' => 'Worldwide',
	'us' => 'United States',
	'uk' => 'United Kingdom'
	);

if(!empty($_POST['submit'])){


$country = addslashes($_POST['country']);
$page = addslashes($_POST['page']);
$name = addslashes($_POST['name']);
$content = addslashes(trim($_POST['content']));

$name = str_replace('{','',$name);
$name = str_replace('}','',$name);
$name = trim($name);

$name1 = getbetween($content,'{','}');

if(!empty($name1)){


	$name = $name1;
	$content = str_replace('{'.$name.'} ','',$content);
	$content = str_replace('{'.$name.'}','',$content);
	

}

	if(empty($name))$failed = 'No tag found';
	if(empty($content))$failed = 'No content found';

	////// CHECK FOR DUPLICATES
	$checkduplicate = mysql_query("SELECT * FROM `content` WHERE `page` = '$page' AND `country` = '$country' AND `name` = '$name' ORDER BY `id` DESC LIMIT 1");
	if(mysql_num_rows($checkduplicate)==1)$failed = 'Duplicate found for this <b>page</b> and country';

	$checkduplicate = mysql_query("SELECT * FROM `content` WHERE `page` = 'global' AND `country` = '$country' AND `name` = '$name' ORDER BY `id` DESC LIMIT 1");
	if(mysql_num_rows($checkduplicate)==1)$failed = 'Duplicate found for this <b>page</b> and country';
	/////

if(empty($failed)){

	$insertq = mysql_query("INSERT INTO `content`
		SET 
		`country` = '$country', 
		`page` = '$page', 
		`name` = '$name', 
		`content` = '$content'");

/*	if($country=='ww'){

			$insertq = mysql_query("INSERT INTO `content`
		SET 
		`country` = 'us', 
		`page` = '$page', 
		`name` = '$name', 
		`content` = '$content'");


			$insertq = mysql_query("INSERT INTO `content`
		SET 
		`country` = 'uk', 
		`page` = '$page', 
		`name` = '$name', 
		`content` = '$content'");

	}*/

	if($insertq)$reviewmessage = '<div class="emailsuccess">Submitted: <b>'.$name.'</b> Thank you!</div>';


}else{$reviewmessage = '<div class="emailsuccess" style="background-color:red;">Failed: '.$failed.'</div>';}

}


$pcq = mysql_query("SELECT * FROM `content` WHERE `page` = '$page' AND `country` = '$country' ORDER BY `id` ASC LIMIT 100");

while($pcqinfo = mysql_fetch_array($pcq)){

	$previouscontent .= '<tr>

	<td><div class="foo" >
<textarea class="language-less">{'.$pcqinfo['name'].'}</textarea>
<button class="btn btn3 report copy-button">{'.$pcqinfo['name'].'}</button><br>
<b>'.$pcqinfo['name'].'</b>
<div style="color: grey;
    height: 30px;
    overflow: hidden;
    width: 320px;"><pre>'.$pcqinfo['content'].'</pre></div>
</div></td>


<td><font color="grey">'.$pcqinfo['country'].' - '.$pcqinfo['page'].'</font></td>


</tr>';







}

if(empty($name)){$newmname = $name;}else{

	$newname = $name;
	$substr = substr("$name", -1);

	$newname = substr($name, 0, -1).($substr+1);

}

$findpageq = mysql_query("SELECT `page` FROM `content` GROUP BY `page`");

while($pageinfo = mysql_fetch_array($findpageq)){

if($pageinfo['page']==$_POST['page'])$selected = 'selected="selected"';

$pages1 .= '<option value="'.$pageinfo['page'].'" '.$selected.'>'.$pageinfo['page'].'</option>';

unset($selected);

}

foreach ($countries as $key => $country1){

if($key==$country)$selected = 'selected="selected"';

	$countryselect .= '<option value="'.$key.'" '.$selected.'>'.$country1.'</option>';
unset($selected);

}




?>
<!DOCTYPE html>
<head>
<title>Submit content</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">

<style type="text/css">

.box23{margin: 66px auto;
    width: 950px;
    background: #fff;
    border-radius: 5px;text-align:left;padding:15px;}

h1{text-align: left;max-width:100%;}

.label{margin-top:35px;}

.container div input, .selectric, .input, .btn {padding: 13px;font-size: 14px;}

.btn{width:100px;text-align:center;}

html{overflow-x: hidden;}

.cke_reset_all{background:#f7f7f7!important;}

.articles{width:100%;}
.articles tr td{border-right:1px solid #ccc;border-bottom:1px solid #000;padding:10px;vertical-align: top}
.articles tr:first-child td{background:#f1f1f1;font-weight:bold;}

.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;}

    .btn{margin: 0!important;width:152px;}

    textarea{font-family:'Open Sans';height:400px;}


.previouscontent{width:100%}
.previouscontent tr td{padding:10px;border-bottom:1px solid grey;}

.previouscontent tr td .foo{    display: inline-block;
    width: 100%;
    margin-bottom: 18px;}

.language-less{width:1px;height:1px;resize: none;}

</style>
<script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
</head>

	<body>


		<?=$header?>

		<div class="box23">


			<?=$reviewmessage?>

			<form method="POST">

				Country:<br>
				<select name="country" class="input inputcontact">
					<?=$countryselect?>
				</select>

				<br><br>Page:
				<select class="input inputcontact" name="page"><?=$pages1?></select>
				<br><br>Name/key:
				<input class="input inputcontact" name="name" value="<?=$newname?>">
				<br><br>Content:
				<textarea class="input inputcontact" name="content" placeholder="Content"></textarea>

				<input class="btn color3" name="submit" type="submit" value="submit content">

			</form>


		</div>

		<div class="box23">
		

			<table class="previouscontent">

				<?=$previouscontent?>

			</table>


		</div>



	</body>


<script>
(function(){

	// Get the elements.
	// - the 'pre' element.

	
	var pre = document.getElementsByClassName('foo');
	

	// Add a copy button in the 'pre' element.
	// which only has the className of 'language-'.
	
	for (var i = 0; i < pre.length; i++) {
		var isLanguage = pre[i].children[0].className.indexOf('language-');
		
		/*
		if ( isLanguage === 0 ) {
			var button           = document.createElement('button');
					button.className = 'copy-button';
					button.textContent = 'Copy';

					pre[i].appendChild(button);
		}*/
	};
	
	// Run Clipboard
	
	var copyCode = new Clipboard('.copy-button', {
		target: function(trigger) {
			return trigger.previousElementSibling;
    }
	});

	// On success:
	// - Change the "Copy" text to "Copied".
	// - Swap it to "Copy" in 2s.
	// - Lead user to the "contenteditable" area with Velocity scroll.
	
	copyCode.on('success', function(event) {
		event.clearSelection();
		event.trigger.style.backgroundColor = '#000';
/*		window.setTimeout(function() {
			event.trigger.textContent = '#### Copied';
		}, 2000);
*/
	});


})();
</script>

</html>