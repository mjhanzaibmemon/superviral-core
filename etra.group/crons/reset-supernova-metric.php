<?php

include('../sm-db.php');


mysql_query("UPDATE `admin_statistics` SET `metric` = 0 WHERE `type` IN  ('supernova_api_freetools_profile','supernova_api_freetools_userid','supernova_api_freetools_stories','supernova_api_freetools_post','supernova_api_freetools_tiktok_post','supernova_api_getpost_userid','supernova_api_getpost_posts','supernova_api_loadmorepost_posts','supernova_api_preloadpost_userid','supernova_api_preloadpost_tiktok_userid','supernova_api_preloadpost_tiktok_posts','supernova_api_freefollowers_profile','supernova_api_freelikes_post','supernova_api_tiktok_freefollowers_userid','supernova_api_tiktok_freefollowers_profile','supernova_api_tiktok_freelikes_posts','supernova_api_tiktok_lambda_getprofile','supernova_api_tiktok_lambda_getpost','supernova_api_tiktok_lambda_getposts') AND `brand` = 'sv'");


?>
