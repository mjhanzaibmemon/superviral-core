<?php

include('db.php');
include('ordercontrol.php');
$type = addslashes($_POST['type']);

switch ($type) {

    case 'categoryWiseComments':

        $packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));
            
        $maxamount = $packageinfo['amount'];

        $ids = $_POST['ids'];
        $ids = implode(',', $ids);

        $query = "SELECT * FROM ig_comments WHERE cat_id IN ($ids) ORDER BY RAND() LIMIT $maxamount";
        $queryRun = mysql_query($query);
        $categoryWiseComments = [];
        while($comments = mysql_fetch_array($queryRun)){
                $categoryWiseComments[] = $comments;
        }

        echo json_encode(['categoryWiseComments' => $categoryWiseComments]); die;
    break;

    case "regenerateComments":

        $uncheckedCommentIds = $_POST['uncheckedCommentIds'];
        $uncheckedCount = count($uncheckedCommentIds);
        $uncheckedCommentIds = implode(',', $uncheckedCommentIds);
       
        $ids = $_POST['ids'];
        $ids = implode(',', $ids);
        $checkedCommentIds = $_POST['checkedCommentIds'];
        if(empty($checkedCommentIds)){
            $checkedCommentIds = [0];
        }
        $checkedCount = count($checkedCommentIds);
        $checkedCommentIds = implode(',', $checkedCommentIds);
       
      
      
        $allComments = [];
       
        // exisitng comments
        $query = "SELECT * FROM ig_comments WHERE cat_id IN ($ids) and id IN ($checkedCommentIds) ORDER BY FIELD(id, $checkedCommentIds)";
        $queryRun = mysql_query($query);
        $uncheckedComments = [];
        while($comments = mysql_fetch_array($queryRun)){
                $allComments[] = $comments;
        }

        // regenerate comments 
        $query = "SELECT * FROM ig_comments WHERE cat_id IN ($ids) and id NOT IN ($uncheckedCommentIds,$checkedCommentIds) ORDER BY RAND() LIMIT $uncheckedCount";
        $queryRun = mysql_query($query);
        
        while($comments = mysql_fetch_array($queryRun)){
                $allComments[] = $comments;
        }
        echo json_encode(['allComments' => $allComments]); die;

    break;

}


?>