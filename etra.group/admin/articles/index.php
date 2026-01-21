<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


$id = $_GET['id'];
$country = $_GET['country'];
$showPrivate = $_GET['show_private'];

if(!empty($_GET['country'])){
    $country_sql = 'AND country="'.$country.'"';
    $tpl = str_replace('<option class="country-option" value="'.$country.'"','<option class="country-option" value="'.$country.'" selected',$tpl);
}

if(!empty($showPrivate) && $showPrivate){
    $private_public_sql = 'AND article_type="private"';
    $tpl = str_replace('id="privateChkbox" name="show_private"','id="privateChkbox" name="show_private" checked',$tpl);
}else{
    $private_public_sql = 'AND article_type="public"';
}

if ($_SESSION['first_name'] == "rabban" || $_SESSION['first_name'] == "mac") {

    $q = mysql_query("SELECT * FROM `articles` WHERE brand = '$brand' {$country_sql} {$private_public_sql} ORDER BY `id` DESC");
} else {
    $q = mysql_query("SELECT * FROM `articles` WHERE added_by = '{$_SESSION['first_name']}' AND brand = '$brand' {$country_sql} {$private_public_sql} ORDER BY `id` DESC");
}

$approveHtm = "";
$publicPrivateHtm = "";
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



    $brandName = getBrandSelectedName($brand);
    $articles .= '<tr>

                    <td>
                    <b><a style="color:#000" href="editarticle.php?id=' . $info['id'] . '">' . stripslashes($info['title']) . '</a></b><br>
					<ul style="font-size:15px;overflow:hidden;">' . $summary1 . $summary2 . $summary3 . '</ul>
					<img src="' . $info['author_image'] . '" style="width:100px">
					</td>
                    <td>
                    <img src="/admin/assets/icons/'. $brandName .'.svg" alt="logo">
                    </td>
                    <td>
                    '.($info['country'] == 'us' ? '<img src="/admin/assets/images/us.png">' : '<img src="/admin/assets/images/uk.png">').'
                    </td>
					<td>' . $live . '</td>
					<td style="width: 170px;">
                    <a class="btn btn-primary color3" href="editarticle.php?id=' . $info['id'] . '">EDIT</a><br>
					' . $approveHtm . ' 
                    <br>
					' . $publicPrivateHtm . '
                    <br>
                    <br>
                    <a class="btn btn-primary color3" href="https://superviral.io'. ($info['country'] == "uk" ? "/uk" :"") .'/blog/'.$info['url'].'?testpreview=true&previewtime='.time().'">
                    <span>
                    <svg style="vertical-align: text-top;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                      <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                    </svg>
                    </span>
                    <span>
                        PREVIEW
                    </span>
                    </a>
                    <br>                    
					</td>

				<tr>';

    $summary1 = $summary2 = $summary3 = '';

    unset($live);
}

$tpl = str_replace('{articles}',$articles, $tpl);

output($tpl, $options);
