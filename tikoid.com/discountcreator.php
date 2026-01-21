<?php

$id = $_GET['id'];
$expiry = $_GET['expiry'];

setcookie(
"discount",
$id,
$expiry,"/");

header('Location: https://tikoid.com/buy-tiktok-followers/');

?>