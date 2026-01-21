<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));
session_start();
$db=1;

require_once  '../db.php';
include_once 'common.php';

if(empty($_SESSION['id'])){
    header("Location: /blog-manage/login.php");
    exit;
}
// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}

// echo var_dump($_COOKIE);die;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$total_result = mysql_query("SELECT COUNT(*) AS total FROM `articles` WHERE `country`='US' AND `article_type` = 'public' AND superadmin_approve=1");
$total_row = mysql_fetch_array($total_result);
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $limit);

$q = mysql_query("SELECT * FROM `articles` WHERE `country`='US' AND `article_type` = 'public' AND superadmin_approve=1 ORDER BY `written` DESC LIMIT $limit OFFSET $offset");

while ($info = mysql_fetch_array($q)) {
    $scheduledDate = date('d-m-y', $info['written']);

    $scheduledLater = 0;
    if($info['written'] > time()){
        $scheduledLater = 1;
    }

    if ($info['superadmin_approve'] == '1' && $scheduledLater == 0) {
        $live = '<div class="status" style="    background: #82fd82;">LIVE</div>';
    } else if($info['superadmin_approve'] == '1' && $scheduledLater == 1){
        $live = '<div class="status" style="width: 85px;background:Orange">'. $scheduledDate .'</div>';
    }else{
        $live = '<div class="status" style="    background: #ccc;
        ">DRAFT</div>';
    }

    if (!empty($info['summary1'])) $summary1 = '<li>' . $info['summary1'] . '</li>';
    if (!empty($info['summary2'])) $summary2 = '<li>' . $info['summary2'] . '</li>';
    if (!empty($info['summary3'])) $summary3 = '<li>' . $info['summary3'] . '</li>';


    if ($_SESSION['first_name'] == 'rabban') {

        if ($info['superadmin_approve'] == 1) {
            $approveHtm = '<a style="width: 120px;" class="btn color3 btn-primary" href="editarticle.php?id=' . $info['id'] . '&approve=0">DISAPPROVE</a>';
        } else {
            $approveHtm = '<a class="btn color3 btn-primary" href="editarticle.php?id=' . $info['id'] . '&approve=1">APPROVE</a>';
        }

        if ($info['article_type'] == 'private') {
        
            $publicPrivateHtm = '<a style="width: 150px;" class="btn color3 btn-primary" href="editarticle.php?id=' . $info['id'] . '&articletype=public">MAKE PUBLIC</a>';
        } else {
            $publicPrivateHtm = '<a style="width: 150px;" class="btn color3 btn-primary" href="editarticle.php?id=' . $info['id'] . '&articletype=private">MAKE PRIVATE</a>';
        }

    }
    
    $results .= '
                <tr style="'.($scheduledLater == 1 ? 'background:lightgoldenrodyellow;' : '').'">
                    <td>
                        <a href="https://superviral.io'. ($info['country'] == "uk" ? "/uk" :"") .'/blog/'.$info['url'].'?testpreview=true&previewtime='.time().'" target="_blank" rel="nofollow">' . stripslashes($info['title']) . '</a>
                        <p style="color:#777;font-size:13px;">'. stripslashes($info['shortdesc']) .'</p>
                    </td>
                    <td>'.$live.'</td>
                    <td><a class="edit-btn" href="edit.php?id=' . $info['id'] . '">Edit</a></td>
                </tr>    
    ';

    $summary1 = $summary2 = $summary3 = '';

    unset($live);
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


$tpl = file_get_contents('tpl.html');

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{results}', $results, $tpl);
$tpl = str_replace('{pagination}', $pagination, $tpl);

echo $tpl;
?>
