<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$blogsection=1;
$db=1;
include_once('header.php');

$articleid = $_GET['id'];
$sortby = $_GET['sortby'];
$year = $_GET['year'];
$month = $_GET['month'];
$day = $_GET['day'];
$url= $_GET['url'];


$q = mysql_query("SELECT * FROM `articles` WHERE `url` = '$url' AND `superadmin_approve` = '1' AND `country` = '{$locas[$loc]['sdb']}' ORDER BY `id` DESC LIMIT 1");
//TEMPORARILY BY PASS SUPERADMIN APPROVE
$q = mysql_query("SELECT * FROM `articles` WHERE `url` = '$url' AND `country` = '{$locas[$loc]['sdb']}' ORDER BY `id` DESC LIMIT 1");

if($_GET['testpreview']=='true')$q = mysql_query("SELECT * FROM `articles` WHERE `url` = '$url' AND `country` = '{$locas[$loc]['sdb']}' ORDER BY `id` DESC LIMIT 1");

if(mysql_num_rows($q)==0){

    if($locas[$loc]['sdb'] == 'uk'){

        $q = mysql_query("SELECT * FROM `articles` WHERE `url` = '$url' AND `superadmin_approve` = '1' AND `country` = 'us' ORDER BY `id` DESC LIMIT 1");
        if(mysql_num_rows($q)==0){
            header('Location: /uk/blog/' ,TRUE,301);die;
        }else{
            header('Location: /blog/'.$url,TRUE,301);die;;
        }

    }else{

        $q = mysql_query("SELECT * FROM `articles` WHERE `url` = '$url' AND `superadmin_approve` = '1' AND `country` = 'uk' ORDER BY `id` DESC LIMIT 1");
        if(mysql_num_rows($q)==0){
            header('Location: /blog/' ,TRUE,301);die;
        }else{
            header('Location: /uk/blog/'.$url,TRUE,301);die;
        }

    }
    
}

$info = mysql_fetch_array($q);

$article = $info['article'];
$thumbnail = $info['thumb_image'];
$url = $info['category'];$_GET['url'] = $info['category'];

//$update = mysql_query("UPDATE `articles` SET `shared` = `shared` + 1 WHERE `id` = '{$info['id']}' LIMIT 1");

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

function generate_navigation($HTML) {
    $DOM = new DOMDocument();
    $DOM->loadHTML($HTML);

    $navigation = '<ul id="table-of-contents">';

    
    $h2IteratorStatus = 0; 
    $h3IteratorStatus = 0; 
    foreach($DOM->getElementsByTagName('*') as $element) {
        if($element->tagName == 'h2') {

            if($h3IteratorStatus){
                //it's open, need to close
                $navigation .= '</ul>';
                $h3IteratorStatus = 0;
            }

            if($h2IteratorStatus){
                //it's open, need to close
                $navigation .= '</li>';
                $h2IteratorStatus = 0;
            }
          
            $elementLink = preg_replace('/^\d+\.\s|\d+\.\)\s|\d+\)\s/', '', $element->textContent);
            $elementLink = preg_replace('/\xc2\xa0/', ' ', $elementLink);
            $elementLink = trim($elementLink);
            $elementLink = str_replace('&nbsp;',' ',$elementLink);
            $elementLink = str_replace(':','',$elementLink);
            $elementLink = strtr($elementLink, ' ', '-');
            $elementLink = strtolower($elementLink);
            $h2IteratorStatus = 1;
            
            $element->textContent = preg_replace('/^\d+\.\s|\d+\.\)\s|\d+\)\s/', '', $element->textContent);
            $element->textContent = rtrim($element->textContent,':');
            $elementLink = str_replace("'", '-', $elementLink);
            $elementLink = str_replace("’", '-', $elementLink);

            $navigation .= '<li class=\'toc-item\'><a href=\'#heading2-'. $elementLink .'\'>' . $element->textContent .'</a></li>';

        } 
        // else if ($element->tagName == 'h3') {

        //     if(!$h3IteratorStatus){
        //         $navigation .= '<ul>';
        //         $h3IteratorStatus = 1;
        //     }
        //     $elementLink = preg_replace('/^\d+\.\s|\d+\.\)\s|\d+\)\s/', '', $element->textContent);
        //     $elementLink = preg_replace('/\xc2\xa0/', ' ', $elementLink);
        //     $elementLink = trim($elementLink);
        //     $elementLink = str_replace([' ','/'],'-',$elementLink);
        //     $elementLink = str_replace('&nbsp;','',$elementLink);
        //     $elementLink = strtolower($elementLink);    

        //     $element->textContent = preg_replace('/^\d+\.\s|\d+\.\)\s|\d+\)\s/', '', $element->textContent);

        //     $navigation .= '<li class=\'toc-item\'><a href=\'#heading-'. $elementLink .'\'>' . $element->textContent .'</a></li>';
        // }
    }

    //check for last opened h3
    if($h3IteratorStatus){
        $navigation .= '</ul>';
    }
    //check for last opened h2
    if($h2IteratorStatus){
        //it's open, need to close
        $navigation .= '</li>';
    }

    return $navigation.'</ul>';
}

$word = str_word_count(strip_tags($info['article']));
$m = floor($word / 500);
$s = floor($word % 200 / (200 / 60));
$est = $m . ' minute' . ($m == 1 ? '' : 's');

if(!empty($info['author_description']))$authordisplay = 'display:block;';


//#### TABLE OF CONTENT (TOC) ######

//find h tags
$headlines = preg_match_all('#<h3.*?>(.*?)</h3>#i', $info['article'], $headlines_arr, PREG_SET_ORDER);
$headlines = $headlines_arr;

// add ID to the h tags

foreach($headlines as $k => $v){
    $elementId = preg_replace('/^\d+\.\s|\d+\.\)\s|\d+\)\s/', '', $v[1]);
    $elementId = str_replace([' ','/'],'-',$elementId);
    $elementId = str_replace('&nbsp;','',$elementId);
    $elementId = preg_replace('#<[^>]+>#', '', $elementId);
    $elementId = preg_replace('#-$#', '', $elementId);
    $elementId = strtolower($elementId);
    $newNode = '<h3 id="heading-'.$elementId.'">'.$v[1].'</h3>';
    $article = str_replace($v[0],$newNode,$article);

}

//find h tags

$headlines1 = preg_match_all('#<h2.*?>(.*?)</h2>#is', $info['article'], $headlines_arr1, PREG_SET_ORDER);
$headlines1 = $headlines_arr1;


// add ID to the h tagss

foreach($headlines1 as $k => $v){
    $elementId = preg_replace('/^\d+\.\s|\d+\.\)\s|\d+\)\s/', '', $v[1]);
    $elementId = str_replace(['&nbsp;',' ','/',';'],'-',$elementId);
    $elementId = str_replace(['&nbsp;',':'],'',$elementId);
    $elementId = preg_replace('#<[^>]+>#', '', $elementId);
    $elementId = preg_replace('#-$#', '', $elementId);
    $elementId = str_replace('&rsquo', '', $elementId);
    $elementId = str_replace("&#39", '', $elementId);
    $elementId = str_replace("'", '-', $elementId);
    $elementId = strtolower(trim($elementId));

    $newNode = '<h2 id="heading2-'.$elementId.'">'.$v[1].'</h2>';
    $article = str_replace($v[0],$newNode,$article);

}

$getdata = $info['article'];
libxml_use_internal_errors(true);

$h2Tag= array();
$xmlDoc = new DOMDocument();
$xmlDoc->loadHTML($getdata);    
$searchNode = $xmlDoc->getElementsByTagName("h2");

 foreach($searchNode as $d){
   $h2Tag[] =  $d->textContent;
 }

 $article = str_replace('<h2', '<h2 class="h2Class"',$article);
 
 foreach($h2Tag as $h2){
    $elementId = preg_replace('/^\d+\.\s|\d+\.\)\s|\d+\)\s/', '', $h2);
    $elementId = preg_replace('/\xc2\xa0/', ' ', $elementId);
    $elementId = trim($elementId);
    $elementId = str_replace(['&nbsp;',':'],'',$elementId);
    $elementId = strtr($elementId, ' ', '-');
    $elementId = str_replace("&#39", '', $elementId);
    $elementId = str_replace('&rsquo', '', $elementId);
    $elementId = str_replace("'", '-', $elementId);
    $elementId = strtolower($elementId);

    $h2Ids[] = 'heading2-' .$elementId;    
}
$h2Ids = json_encode($h2Ids);

$summary1 = str_replace('\\','',$info['summary1']);
$summary2 = str_replace('\\','',$info['summary2']);
$summary3 = str_replace('\\','',$info['summary3']);

$article = str_replace('https://svstorage.s3.amazonaws.com/','https://cdn.superviral.io/media/',$article);
$info['thumb_image'] = str_replace('https://svstorage.s3.amazonaws.com/','https://cdn.superviral.io/media/',$info['thumb_image']);
$info['author_image'] = str_replace('https://svstorage.s3.amazonaws.com/','https://cdn.superviral.io/media/',$info['author_image']);


// call to generate parent child system
$toc = generate_navigation($info['article']);

$tpl = @file_get_contents("article.html");

$tpl = str_replace('{header}', $header,$tpl);
$tpl = str_replace('{footer}', $footer,$tpl);
$tpl = str_replace('{headerscript}', $headerscript,$tpl);

$tpl = str_replace('{authordisplay}', $authordisplay,$tpl);
$tpl = str_replace('{authordescription}', $info['author_description'],$tpl);
$tpl = str_replace('{authorimage}', $info['author_image'],$tpl);
$tpl = str_replace('{sharelink}', 'https://superviral.io'. $loclink .'/blog/'.$info['url'],$tpl);
$tpl = str_replace('{shares}', $info['shared'],$tpl);
$tpl = str_replace('{id}', ucwords(stripslashes($info['id'])),$tpl);
$tpl = str_replace('{h1}', ucwords(stripslashes($info['h1'])),$tpl);
$tpl = str_replace('{metatitle}', ($info['meta_title'] ? stripslashes($info['meta_title']) : stripslashes($info['title'])), $tpl);
$tpl = str_replace('{title}', stripslashes($info['title']),$tpl);
$tpl = str_replace('{description}', ucfirst(stripslashes($info['shortdesc'])),$tpl);
$tpl = str_replace('{summary_display}', ($summary1 == '' ? 'display:none;' : ''),$tpl);
$tpl = str_replace('{summary1}', ucfirst(stripslashes($summary1)),$tpl);
$tpl = str_replace('{summary2}', ucfirst(stripslashes($summary2)),$tpl);
$tpl = str_replace('{summary3}', ucfirst(stripslashes($summary3)),$tpl);
//$tpl = str_replace('{written}', ucwords(gmdate('H:i l jS F Y', $info['written'])),$tpl);
$tpl = str_replace('{written}', ucwords(gmdate('F jS Y', $info['written'])),$tpl);
$tpl = str_replace('{readtime}', $est.' read',$tpl);
$tpl = str_replace('{toc}', $toc,$tpl);
$tpl = str_replace('{toc_display}', ($_GET['testpreview'] ? '' : 'display:none;'),$tpl);
$article = str_replace("[img", "<img", $article);
$article = str_replace('&quot;]', ">", $article);
$article = str_replace('&quot;', "", $article);

$tpl = str_replace('{article}', stripslashes($article),$tpl);
$tpl = str_replace('{author}', stripslashes($info['author']),$tpl);
$tpl = str_replace('{thumbnail}', stripslashes($info['thumb_image']),$tpl);
$tpl = str_replace('{loclocation}', $loclinkforward , $tpl);
$tpl = str_replace('{h2Ids}', $h2Ids , $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('article','global')");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

echo $tpl;

?>
