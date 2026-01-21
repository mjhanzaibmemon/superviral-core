<?php



// error_reporting(E_ERROR | E_PARSE);

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';



$type = addslashes($_POST['type']);

$adminName  = $_SESSION['first_name'];

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
global $brand;
        $country = addslashes($_POST['country']);
        $company = addslashes($_POST['company']);

        if(!empty($company)){
           $brand = $company;
        }
        $page = mysql_query("SELECT id,`page` FROM `content` WHERE country = '$country' AND brand = '$brand' group BY `page`;");

        while($pageData = mysql_fetch_array($page)){
        
            $pageHtml[] = $pageData;
        
        }

        echo json_encode($pageHtml);

        die;

}



function getPageName(){
  
    // page
    global $brand;
            $pages = addslashes($_POST['page']);
            $country = addslashes($_POST['country']);
            $company = addslashes($_POST['company']);

            if(!empty($company)){
               $brand = $company;
            }
            $page = mysql_query("SELECT id,`name` FROM `content` WHERE `page` = '$pages' and country = '$country' AND brand = '$brand' group BY `name`;");
    
            while($pageData = mysql_fetch_array($page)){
            
                $pageHtml[] = $pageData;
            
            }
    
            echo json_encode($pageHtml);
    
            die;
    
    }

    
function getContent(){
  
    // page
    global $brand;
            $pages = addslashes($_POST['page']);
            $country = addslashes($_POST['country']);
            $name = addslashes($_POST['name']);
            $company = addslashes($_POST['company']);

            if(!empty($company)){
               $brand = $company;
            }
            $content = mysql_query("SELECT id,`content`,updated_by FROM `content` WHERE `name` = '$name' and `page` = '$pages' and country = '$country' AND brand = '$brand' order by id desc limit 1");
    
            $contentData = mysql_fetch_array($content);
            echo json_encode($contentData);
    
            die;
    
    }
    
function getWWContent(){
  
        // page
        global $brand;
                $pages = addslashes($_POST['page']);
                $name = addslashes($_POST['name']);
                $company = addslashes($_POST['company']);

                if(!empty($company)){
                   $brand = $company;
                }
                $content = mysql_query("SELECT id,`content`,updated_by FROM `content` WHERE `name` = '$name' and `page` = '$pages' and country = 'ww' AND brand = '$brand' order by id desc limit 1");
        
                $contentData = mysql_fetch_array($content);
                echo json_encode($contentData);
        
                die;
        
}
        
function finishEditing(){
  
    // page
    global $brand;
            $adminName  = $_SESSION['first_name'];
            $pages = addslashes($_POST['page']);
            $country = addslashes($_POST['country']);
            $name = addslashes($_POST['name']);
            $content = addslashes($_POST['content']);
            $now = time();
            $company = addslashes($_POST['company']);

            if(!empty($company)){
               $brand = $company;
            }
            if(empty($country) || empty($name) || empty($content) || empty($pages)){

                return;
            }
            
            $checkifExist =  mysql_query("SELECT * FROM `content` WHERE `name` = '$name' and `page` = '$pages' and country = '$country' AND brand = '$brand'");

            if(mysql_num_rows($checkifExist)>0){
    
                $content_query = mysql_query("UPDATE `content` set content= '$content', updated_by = '$adminName', added = '$now' WHERE `name` = '$name' and `page` = '$pages' and country = '$country' AND brand = '$brand'");
    
            }else{

                $content_query = mysql_query("INSERT INTO `content` set content= '$content', `name` = '$name', `page` = '$pages', country = '$country', updated_by = '$adminName', brand = '$brand', added = '$now'");

            }

            // Insert into history table
            $content_history = mysql_query("INSERT INTO `content_history` set content= '$content', `name` = '$name', `page` = '$pages', country = '$country',`date` = '$now', brand = '$brand'");


           
            if($content_query){
                echo json_encode(['message'=>'Successfully Done']);

            }else{
                echo json_encode(['message'=>'Failed']);
            }
    
            die;
    
}

function deleteContent(){
  
    // page
    global $brand;
            $pages = addslashes($_POST['page']);
            $country = addslashes($_POST['country']);
            $name = addslashes($_POST['name']);
            $company = addslashes($_POST['company']);

            if(!empty($company)){
               $brand = $company;
            }

            $content = mysql_query("DELETE FROM `content` WHERE `name` = '$name' and `page` = '$pages' and country = '$country' AND brand = '$brand'");

           
            if($content){
                echo json_encode(['message'=>'Successfully Done']);

            }else{
                echo json_encode(['message'=>'Failed']);
            }
    
            die;
    
}

function getHistoryContent(){

     // page
     global $brand;
     $pages = addslashes($_POST['page']);
     $country = addslashes($_POST['country']);
     $name = addslashes($_POST['name']);
     $company = addslashes($_POST['company']);

     if(!empty($company)){
        $brand = $company;
     }
     $content_history = mysql_query("SELECT * FROM `content_history` WHERE `name` = '$name' and `page` = '$pages' and country = '$country' AND brand = '$brand' order by `date` desc");

     while($Data = mysql_fetch_array($content_history)){
        
        $Data['time']= ago($Data['date']);
        $contentData[] = $Data;
    
    }

    echo json_encode($contentData);

     die;

}