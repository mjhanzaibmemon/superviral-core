<?php


include('adminheader.php');

$id = $_GET['id'];

if($_SESSION['admin_user'] == "rabban"){
	$q = mysql_query("SELECT * FROM `articles` ORDER BY `id` DESC");

}else{
	$q = mysql_query("SELECT * FROM `articles` WHERE added_by = '{$_SESSION['admin_user']}' ORDER BY `id` DESC");

}


$approveHtm = "";
$publicPrivateHtm = "";

while($info = mysql_fetch_array($q)){

	if($info['published']=='1'){$live = '<div class="status" style="    background: #82fd82;">LIVE</div>';}else{

	$live = '<div class="status" style="background: #ccc;">DRAFT</div>';

    }

    if(!empty($info['summary1']))$summary1 = '<li>'.$info['summary1'].'</li>';
    if(!empty($info['summary2']))$summary2 = '<li>'.$info['summary2'].'</li>';
    if(!empty($info['summary3']))$summary3 = '<li>'.$info['summary3'].'</li>';

	if($_SESSION['admin_user'] == 'rabban'){

		if($info['superadmin_approve'] == 1){
			$approveHtm = '<a style="width: 120px;" class="btn color3" href="/admin/editarticle.php?id='.$info['id'].'&approve=0">DISAPPROVE</a>';

		}else{
			$approveHtm = '<a class="btn color3" href="/admin/editarticle.php?id='.$info['id'].'&approve=1">APPROVE</a>';

		}
	}

	if($info['article_type'] == 'private'){
		$publicPrivateHtm = '<a style="width: 150px;" class="btn color3" href="/admin/editarticle.php?id='.$info['id'].'&articletype=public">MAKE PUBLIC</a>';

	}else{
		$publicPrivateHtm = '<a style="width: 150px;" class="btn color3" href="/admin/editarticle.php?id='.$info['id'].'&articletype=private">MAKE PRIVATE</a>';

	}

	$articles .= '<tr>

					<td><b>'.$info['title'].'</b><br>
					<ul style="font-size:15px;">'.$summary1.$summary2.$summary3.'</ul>
					<img src="'. $info['author_image'] .'" style="width:100px">
					</td>
					<td>'.$live.'</td>
					<td>
					<a class="btn color3" href="/admin/editarticle.php?id='.$info['id'].'">EDIT</a>
					'. $approveHtm .'
					'. $publicPrivateHtm .'
					</td>

				<tr>';

	unset($live);
	unset($summary1);
	unset($summary2);
	unset($summary3);

}



?>
<!DOCTYPE html>
<head>
<title>Edit or Add Article</title>
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

    .btn{margin: 0!important;}

</style>
<script src="ckeditor/ckeditor.js"></script>
</head>

	<body>


		<?=$header?>

		<div class="box23">

			<a class="btn color3" style="width:210px" href="/admin/editarticle.php?id=new">Create a new article</a>
			<hr>

			<table class="articles">

				<tr>

					<td>Title</td>
					<td>Status</td>
					<td>Action</td>

				</tr>

				<?=$articles?>

			</table>

		</div>





	</body>
</html>