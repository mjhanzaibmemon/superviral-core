<?php

session_start();

$user  = $_SESSION['first_name'];
$link = $_SERVER['PHP_SELF'];
$last = explode("/", $link, 4);
$currentPage = $last[2];
$showSearchMenu = false;

/*

    ### PAGE NAMES

    1. download-chat
    2. reset-monthly-free
    3. email-sent
    4. change-service
    5. user-outreach
    6. git-diffcheck
    7. reports
    8. blacklist
    9. stats
    10. articles
    11. missing-orders
    12. reviews
    13. copy-editor
    14. copy-editor/add.php
    15. email-support
    16. free-followers
    17. free-likes
    18. refunds
    19. feedback
    20. feedback/questions.php
    21. reset-payment-attempt
    22. post-grabber-tester
    23. failed-orders
    24. resend-orders
    25. autoreplies
    26. refill-mass
    27. email-stats
    28. lambda-logs
    29. fraud-import
    30. transfer-data
    31. redis

*/

switch($user){
    case 'mac':
    case 'rabban':
        $AllowedPage = ['cogs','fraud-import','lambda-logs','download-chats','reset-monthly-free','email-sent','change-service','user-outreach','git-diffcheck','reports','blacklist','stats','articles','missing-orders','reviews','copy-editor','copy-editor/add.php','email-support','free-followers','free-likes','refunds','feedback','feedback/questions.php','reset-payment-attempt','post-grabber-tester','failed-orders','resend-orders','autoreplies','refill-mass','email-stats','transfer-data', 'redis'];
        $showSearchMenu = true;
        break;
    case 'anuj':    
        $AllowedPage = ['transfer-data'];
        break;
    case 'abu':    
        $AllowedPage = ['email-support', 'resend-orders'];
        break;
    case 'naeem':
        $AllowedPage = ['email-support', 'resend-orders'];
        break;
    case 'mo':
        $AllowedPage = ['email-support'];
        break;        
    case 'hassan':    
        $AllowedPage = ['cogs','fraud-import','download-chats','refill-mass','autoreplies','resend-orders','change-service','copy-editor','check-user','check-account','check-al','check-ml','reports','blacklist','stats','articles','missing-orders','reviews','copy-editor','email-support','free-followers', 'free-likes','refunds','feedback','feedback','feedback/questions.php'];
        $showSearchMenu = true;
        break; 
    case 'tahmin.a':    
    case 'tahmin':    
        $AllowedPage = ['articles'];
        $showSearchMenu = true;
        break;
    case 'admin':
        $AllowedPage = ['reports','blacklist','stats','articles','missing-orders','reviews','copy-editor','email-support','free-followers', 'free-likes','refunds','feedback'];
        $showSearchMenu = true;
        break;
    default:
        $AllowedPage = [];
        echo 'Not Authorised to access';die;
        break;
}







?>
