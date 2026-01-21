<?php


include('adminheader.php');


?>
<!DOCTYPE html>
<head>
<title>Admin Panel Index</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">

<style type="text/css">

.btn{width:100px;text-align:center;}

</style>
<script src="ckeditor/ckeditor.js"></script>
</head>

	<body>


		<?=$header?>



		<script>

CKEDITOR.on('instanceReady', function(ev) { ev.editor.fire('contentDom'); });

    CKEDITOR.replace('editor1', {
      width: '768px',
      contentsCss: "body {font-size: 15px;font-family:'Open Sans';letter-spacing: .4px;}"
    });


CKEDITOR.addCss( 'p.imgcaption{background-color:#ccc;padding:5px;font-weight:600;font-size:15px;font-family: arial;}' );


		</script>


	</body>
</html>