<?php



// error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../db.php';



$type = addslashes($_POST['type']);

$adminName  = $_SERVER['PHP_AUTH_USER'];

switch ($type) {

    case "getCountryPage":

        if(empty($adminName)){
            echo json_encode(['message'=>'Sorry! you are not authorised user']);
            return;
        }
        
        getCountryPage();
        break;

    case "getPageName":

        if(empty($adminName)){
            echo json_encode(['message'=>'Sorry! you are not authorised user']);
            return;
        }

        getPageName();

        break;

    case "getContent":
        
        if(empty($adminName)){
            echo json_encode(['message'=>'Sorry! you are not authorised user']);
            return;
        }

        getContent();

        break;    
  
    case "getWWContent":

        if(empty($adminName)){
            echo json_encode(['message'=>'Sorry! you are not authorised user']);
            return;
        }

        getWWContent();

        break;    
    
    case "finishEditing":

        if(empty($adminName)){
            echo json_encode(['message'=>'Sorry! you are not authorised user']);
            return;
        }
    
        finishEditing();

        break;

    case "deleteContent":

        if(empty($adminName)){
            echo json_encode(['message'=>'Sorry! you are not authorised user']);
            return;
        }
        
        deleteContent();

        break;

    case "getHistoryContent":

        if(empty($adminName)){
            echo json_encode(['message'=>'Sorry! you are not authorised user']);
            return;
        }
        
        getHistoryContent();

        break;    
}


function getCountryPage(){
  
// page

        $country = addslashes($_POST['country']);

        $page = mysql_query("SELECT id,`page` FROM `content` WHERE country = '$country' group BY `page`;");

        while($pageData = mysql_fetch_array($page)){
        
            $pageHtml[] = $pageData;
        
        }

        echo json_encode($pageHtml);

        die;

}



function getPageName(){
  
    // page
    
            $pages = addslashes($_POST['page']);
            $country = addslashes($_POST['country']);
    
            $page = mysql_query("SELECT id,`name` FROM `content` WHERE `page` = '$pages' and country = '$country' group BY `name`;");
    
            while($pageData = mysql_fetch_array($page)){
            
                $pageHtml[] = $pageData;
            
            }
    
            echo json_encode($pageHtml);
    
            die;
    
    }

    
function getContent(){
  
    // page
    
            $pages = addslashes($_POST['page']);
            $country = addslashes($_POST['country']);
            $name = addslashes($_POST['name']);
    
            $content = mysql_query("SELECT id,`content`,updated_by FROM `content` WHERE `name` = '$name' and `page` = '$pages' and country = '$country' order by id desc limit 1");
    
            $contentData = mysql_fetch_array($content);
            echo json_encode($contentData);
    
            die;
    
    }
    
function getWWContent(){
  
        // page
        
                $pages = addslashes($_POST['page']);
                $name = addslashes($_POST['name']);
        
                $content = mysql_query("SELECT id,`content`,updated_by FROM `content` WHERE `name` = '$name' and `page` = '$pages' and country = 'ww' order by id desc limit 1");
        
                $contentData = mysql_fetch_array($content);
                echo json_encode($contentData);
        
                die;
        
}
        
function finishEditing(){
  
    // page
            $adminName  = $_SERVER['PHP_AUTH_USER'];
            $pages = addslashes($_POST['page']);
            $country = addslashes($_POST['country']);
            $name = addslashes($_POST['name']);
            $content = addslashes($_POST['content']);
            $now = time();
    
            if(empty($country) || empty($name) || empty($content) || empty($pages)){

                return;
            }
            
            $checkifExist =  mysql_query("SELECT * FROM `content` WHERE `name` = '$name' and `page` = '$pages' and country = '$country'");

            if(mysql_num_rows($checkifExist)>0){
    
                $content_query = mysql_query("UPDATE `content` set content= '$content', updated_by = '$adminName' WHERE `name` = '$name' and `page` = '$pages' and country = '$country'");
    
            }else{

                $content_query = mysql_query("INSERT INTO `content` set content= '$content', `name` = '$name', `page` = '$pages', country = '$country'");

            }

            // Insert into history table
            $content_history = mysql_query("INSERT INTO `content_history` set content= '$content', `name` = '$name', `page` = '$pages', country = '$country',`date` = '$now'");


           
            if($content_query){
                echo json_encode(['message'=>'Successfully Done']);

            }else{
                echo json_encode(['message'=>'Failed']);
            }
    
            die;
    
}

function deleteContent(){
  
    // page
    
            $pages = addslashes($_POST['page']);
            $country = addslashes($_POST['country']);
            $name = addslashes($_POST['name']);
    

            $content = mysql_query("DELETE FROM `content` WHERE `name` = '$name' and `page` = '$pages' and country = '$country'");

           
            if($content){
                echo json_encode(['message'=>'Successfully Done']);

            }else{
                echo json_encode(['message'=>'Failed']);
            }
    
            die;
    
}

function getHistoryContent(){

     // page
    
     $pages = addslashes($_POST['page']);
     $country = addslashes($_POST['country']);
     $name = addslashes($_POST['name']);

     $content_history = mysql_query("SELECT * FROM `content_history` WHERE `name` = '$name' and `page` = '$pages' and country = '$country' order by `date` desc");

     while($Data = mysql_fetch_array($content_history)){
        
        $Data['time']= ago($Data['date']);
        $contentData[] = $Data;
    
    }

    echo json_encode($contentData);

     die;

}



function ago($time)
{$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");
   $now = time();
       $difference     = $now - $time;
       $tense         = 'ago';
   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }
   $difference = round($difference);
   if($difference != 1) {
       $periods[$j].= "s";
}   return "$difference $periods[$j] ago";}