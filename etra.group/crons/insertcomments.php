<?php

die;

include('../sm-db.php');
include('emailer.php');

$email_txt = file_get_contents('comments.txt');

$list = explode("\n",$email_txt);


//////////////////////////////////////////////////////////////////////

$i = 0;


foreach($list as $comment){

$comment = trim($comment);

if(empty($comment))continue;


$comment = str_replace('"', '', $comment);
$comment = str_replace("'", "\'", $comment);


$insertq = mysql_query("INSERT INTO `ig_comments` SET `cat_id` = '8',`comment` = '$comment'");

$i++;

if($insertq){




echo $i.'. Inserted!: '.$comment.'<hr>';



} else{



die($i.'. Not Inserted!: '.$comment.'<hr>');

}

}



/*$arraysplice = array_shift($list);

$email_txt = implode("\n", $list);

file_put_contents('emails.txt',$email_txt); // in case u f up

echo ' <meta http-equiv="refresh" content="1">';
*/
?>