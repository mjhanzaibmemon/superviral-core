<?php


// $host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
// $subdomain = explode('.', $host)[0]; // Get the first part of the domain
// $initial = $subdomain . '.';
// $subdomain = '/'. $subdomain . '/etra.group';

// if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
// core layout function 
  
error_reporting(E_ERROR | E_PARSE);

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/admin/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/admin/common/core/func.php';



$brand_arr = array('sv'=>'Superviral','to'=>'Tikoid','fb'=>'Feedbuzz','sz'=>'Swizzy','tp'=>'Tokpop');

// header menu array
$leftpages = array(
    'Customer'=> array('Email' => 'email-support','Email Marketing' => 'email-stats','Reports' => 'reports', 'Refill Mass' => 'refill-mass', 'Fraud Import' => 'fraud-import', 'Blacklist' => 'blacklist', 'Auto Replies' => 'autoreplies','Outreach' => 'user-outreach'),
    'Orders'=>array('Missing Orders' => 'missing-orders','Finalise Refunds' => 'refunds','Resend Orders'=>'resend-orders','Packages'=>'change-service','Supplier Cost'=>'cogs'),
    'Website' => array('Edit Copy' => 'copy-editor', 'Add Copy' => 'copy-editor/add.php','Stats' => 'stats','Reset Payment Counter' => 'reset-payment-attempt','Reset Monthly Free' => 'reset-monthly-free','Test API systems' => 'post-grabber-tester'),
    'Content' => array('Articles' => 'articles', 'Submit Feedback' => 'feedback', 'Review Feedback' => 'feedback/questions.php' ,'Reviews' => 'reviews' ,'Download Support' => 'download-chats'),
    'Dev' => array('Diff Check' => 'git-diffcheck', 'Lambda Logs' => 'lambda-logs', 'Transfer Data' => 'transfer-data', 'Redis' => 'redis')
);

$rightpages = array(
    'Search Customer' => 'check-user', 'Search Orders' => 'check-user', 'Search Account' => 'check-account', 'Search AL' => 'check-al', 'Search Mail List' => 'check-ml'
);

function output($tpl, $options = array()){
    global $brand, $brand_arr, $leftpages, $rightpages,$user;


    // get global tags

    $meta = tpl_get('meta', $tpl);$tpl = tpl_replace('meta','',$tpl);

    $head_tags = tpl_get('head_tags', $tpl);$tpl = tpl_replace('head_tags','',$tpl);

    $script_tags = tpl_get('script_tags', $tpl);$tpl = tpl_replace('script_tags','',$tpl);



    $layout = file_get_contents($_SERVER['DOCUMENT_ROOT']. '/admin/layout.html');

    // left top menu
    $leftMenuHtm = '';
    include $_SERVER['DOCUMENT_ROOT'] .'/admin/permission.php';

    $htmArray = [];
    foreach($leftpages as $leftpagekey => $leftPage){

        $htmArray[$leftpagekey] .= '<li class="nav-item dropdown" >
        <button class="dropdown-toggle"><span class="text">'. $leftpagekey .'</span><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="11" height="7" viewBox="0 0 11 7" fill="none"><path d="M1 1.16528L5.5 5.16528L10 1.16528" stroke="white" stroke-width="2" stroke-linejoin="round" /></svg></span></button>
        <ul class="dropdown-menu">';
        $chkpagecount = 0;
        foreach($leftPage as $pagekey => $page){

            if(!in_array($page, $AllowedPage))
            continue;

            $chkpagecount++;
            $htmArray[$leftpagekey] .= '<li><a href="/admin/'. $page .'/">'. $pagekey .'</a></li>';
        }
        $htmArray[$leftpagekey] .= '</ul></li>';
        if($chkpagecount == 0){

            $htmArray[$leftpagekey] = '';
        } 
    }

    foreach ($htmArray as $htm){
        $leftMenuHtm .= $htm;
    }

    $layout = str_replace('{leftMenuHtm}', $leftMenuHtm, $layout);

    // end left top menu

    // right top menu
    if($showSearchMenu !== true){
        $layout = tpl_replace('nav_searchMenu','',$layout);
    }

    // end right top menu


    $layout = str_replace('{timestamp}', time(), $layout);

    if(isset($options['noindex'])){

        $layout = str_replace('<head>', '<head><meta name="robots" content="noindex">', $layout);

    }





    if(isset($options['modal'])){

        $options['no_header']=$options['no_footer']=1;

    }



    if(isset($options['no_header'])){

        $layout = tpl_replace('header','',$layout);

    }

    if(isset($options['no_footer'])){

        $layout = tpl_replace('footer','',$layout);

    }


    $brand_options = createBrandOptions();


    if($meta){

        $layout = tpl_replace('meta', $meta, $layout);

    }

    $layout = tpl_replace('head_tags', $head_tags, $layout);

    $layout = tpl_replace('script_tags', $script_tags, $layout);

    $tpl = str_replace('{body}', $tpl, $layout);

    $tpl = tpl_replace('brand_options', $brand_options, $tpl);

    $tpl = str_replace('class="btn-logo" href="?brand='.$brand.'"', 'class="btn-logo active" href="?brand='.$brand.'"',$tpl);

    $tpl = str_replace('{brand_name}', $brand_arr[$brand], $tpl);

    $tpl = str_replace('{user}', $user, $tpl);

    $tpl = str_replace('{year}',date('Y'),$tpl);

    // compress tpl

    // $tpl = preg_replace('/\s+/', ' ', $tpl);

    echo $tpl;

}







function tpl_get($needle, $tpl=''){

    if(!$tpl){global $tpl;}

    preg_match('/<!-- @'.preg_quote($needle).' -->(.*)<!-- @'.preg_quote($needle).' -->/Usi',$tpl, $matches);

    return $matches[1];

}





function tpl_replace($needle, $replace, $tpl=''){

    if(!$tpl){global $tpl;}

    

    $replace=str_replace("$", "&dollar;", $replace);

    $tpl = preg_replace('/<!-- @'.preg_quote($needle).' -->.*<!-- @'.preg_quote($needle).' -->/Usi', $replace,$tpl);

    return $tpl;

}

function createBrandOptions(){
    global $brand, $brand_arr;

    $options = '<option value="">All</option>';

    foreach($brand_arr as $k=>$v){
        $selected = $brand == $k ? 'selected' : '';
        $options.='<option class="brand_option '.strtolower($v).'" value="'.$k.'" '.$selected.'>'.$v.'</option>';
    }

    return $options;

}

session_start();


if(isset($_SESSION['first_name']) && !empty($_SESSION['first_name'])){

    // success
   $user  = $_SESSION['first_name'];

}else{
    // echo 'Error 401: User Not Logged In, Try Login <a href="/admin/?location='. $_SERVER['REQUEST_URI'].'">here</a>';
    // die;
    header('location: /admin/?location='. $_SERVER['REQUEST_URI'] );
}


global $brand;



$brand = addslashes($_GET['brand']);

if(!empty($brand)){ // switch company
    $_SESSION['brand'] = addslashes($_GET['brand']);
}

if(empty($brand) && empty($_SESSION['brand'])){
    $brand = 'sv'; // default
}else{
    $brand = $_SESSION['brand'];
}


if(!empty($_POST['company'])){

    $brand = addslashes($_POST['company']);
}
// //////////////////////////////////////////////////////////////////////////////////


function getBrandSelectedPlatform($br){

    switch($br){
        case 'sv':
            $name = 'Instagram';
        break;
        case 'fb':
            $name = 'Instagram';
        break;
        case 'to':
            $name = 'TikTok';
        break;
        case 'tp':
            $name = 'TikTok';
        break;
        case 'sz':
            $name = ' ';
        break;    

    }
    return $name;
}

function getBrandSelectedName($br){

    switch($br){
        case 'sv':
            $name = 'Superviral';
        break;
        case 'fb':
            $name = 'Feedbuzz';
        break;
        case 'to':
            $name = 'Tikoid';
        break;
        case 'tp':
            $name = 'Tokpop';
        break;
        case 'sz':
            $name = 'Swizzy';
        break;    

    }
    return $name;
}

function getBrandSelectedDomain($br){

    switch($br){
        case 'sv':
            $name = 'superviral.io';
        break;
        case 'fb':
            $name = 'feedbuzz.io';
        break;
        case 'to':
            $name = 'tikoid.com';
        break;
        case 'tp':
            $name = 'tokpop.com';
        break;
        case 'sz':
            $name = 'swizzy.io';
        break;    

    }
    return $name;
}

function getBrandSelectedSource($br){

    switch($br){
        case 'sv':
        case 'sz':
        case 'fb':
        case 'ig':
            $name = 'instagram.com';
        break;
        case 'to':
        case 'tp':
        case 'tt':    
            $name = 'tiktok.com';
        break;    

    }
    return $name;
}

function getSocialMediaSource($br){

    switch($br){
        case 'ig':
            $name = "Instagram";
        break;
        case 'tt':
            $name = "Tiktok";
        break;    

    }
    return $name;
}
// eg:



// $tpl = file_get_contents('template.html');

// $options = [];



// if(isset($_GET['no_header'])){

//     $options['no_header'] = 1;

// }





// $snippet = tpl_get('item',$tpl);

// $html = '';

// for($i=0;$i<10;$i++){

//     $temp = $snippet;

//     $temp = str_replace('{title}','Title '.$i,$temp);

//     $html .= $temp;

// }

// $tpl = tpl_replace('item',$html,$tpl);





// output($tpl,$options);
