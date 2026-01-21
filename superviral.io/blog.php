<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$blogsection=1;
$db=1;
include_once('header.php');

function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}


$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$total_result = mysql_query("SELECT COUNT(*) AS total FROM `articles` WHERE `brand`='sv' AND superadmin_approve = 1 AND `country` = '{$locas[$loc]['sdb']}'");
$total_row = mysql_fetch_array($total_result);
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $limit);
$now = time();
$sql = "SELECT * FROM `articles`
        WHERE `brand` = 'sv'
        AND `superadmin_approve` = 1
        AND `country` = '{$locas[$loc]['sdb']}'
        AND `written` <= $now
        ORDER BY `written` DESC
        LIMIT $limit OFFSET $offset";

//echo $sql . '<br>';
//$q = mysql_query("SELECT * FROM `articles` WHERE `brand`='sv' AND superadmin_approve = 1 AND `country` = '{$locas[$loc]['sdb']}' ORDER BY `written` DESC LIMIT $limit OFFSET $offset");
$q = mysql_query($sql);

while($info = mysql_fetch_array($q)){
    if($info['written'] > time()){
        continue;
    }
     // check for IP if article is private
     if($info['article_type'] == 'private'){
        $checkIfUserIpExist = mysql_num_rows(mysql_query("SELECT * FROM `articles_ipaddress` WHERE `ip` = '$userIP' LIMIT 1"));

        if($checkIfUserIpExist == 0){
            continue;
        }
    }
   
    $info['summary1'] = str_replace('\\','',$info['summary1']);
    $info['summary2'] = str_replace('\\','',$info['summary2']);
    $info['summary3'] = str_replace('\\','',$info['summary3']);
	if($info['summary1'] != ''){
		$article_summary = '
                        <li>'.stripslashes($info['summary1']).'</li>
                        <li>'.stripslashes($info['summary2']).'</li>
                        <li>'.stripslashes($info['summary3']).'</li>';
	}else{
		$article_summary = '';
	}

	$articles .= '<div class="container dshadow">

                	<div class="containerholder">

                        <a href="'. $loclink .'/blog/'.$info['url'].'" title="'.stripslashes($info['title']).'"><h2>'.stripslashes($info['title']).'</h2></a>
                        <span class="authortime">By '.$info['author'].', '.ucwords(gmdate('F jS Y', $info['written'])).'</span>
                        <ul class="blogpoints">

   			'.stripslashes($article_summary).'

                        </ul>
                        <div class="blogdesc">'.stripslashes($info['shortdesc']).'</div>

                        <a class="btn btnblog color3" title="'.$info['title'].'" href="'. $loclink .'/blog/'.$info['url'].'">Read More &raquo;</a>

                	</div>

                </div>';

}


$pagination = '<div class="pagination">';


if ($page > 1) {
    $pagination .= '<a href="?page=' . ($page - 1) . '" class="pagination-btn pagination-prev">‹ Back</a>';
}


for ($i = 1; $i <= $total_pages; $i++) {
    if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2) {
        if ($i == $page) {
            $pagination .= '<span class="pagination-btn pagination-number active">' . $i . '</span>';
        } else {
            $pagination .= '<a href="?page=' . $i . '" class="pagination-btn pagination-number">' . $i . '</a>';
        }
    } elseif ($i == 2 && $page > 4 || $i == $total_pages - 1 && $page < $total_pages - 3) {
        $pagination .= '<span class="pagination-dots">...</span>';
    }
}


if ($page < $total_pages) {
    $pagination .= '<a href="?page=' . ($page + 1) . '" class="pagination-btn pagination-next">Next ›</a>';
}

$pagination .= '</div>';

$tpl = @file_get_contents("blog.html");

$tpl = str_replace('{header}', $header,$tpl);
$tpl = str_replace('{headerscript}', $headerscript,$tpl);
$tpl = str_replace('{footer}', $footer,$tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{articles}', $articles,$tpl);
$tpl = str_replace('{pagination}', $pagination,$tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('blog','global')");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;

?>
