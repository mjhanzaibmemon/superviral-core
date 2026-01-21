<?php



die;

$id = addslashes($_GET['id']);
$expiry = addslashes($_GET['expiry']);

setcookie(
"discount",
$id,
$expiry,"/");

header('Location: https://superviral.io/'.$loclinkforward.'buy-instagram-followers/');

?>