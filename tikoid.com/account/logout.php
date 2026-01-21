<?php

// Unset all cookies 
if (isset($_COOKIE['plus_id'])) {
    // unset($_COOKIE['plus_id']); 
    setcookie('plus_id', '', time()-3600, "/","");
    setcookie('plus_id', '', time()-3600, "/",".tikoid.com");
} 
if (isset($_COOKIE['plus_token'])) {
    // unset($_COOKIE['plus_token']); 
    setcookie('plus_token', '', time()-3600, "/","");
    setcookie('plus_token', '', time()-3600, "/",".tikoid.com");
} 


// Redirect to login page
header("location: /login/?logout=true");
exit;
?>