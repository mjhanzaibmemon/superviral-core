<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

// lock only for hassan and rabban
if(!in_array($_SESSION['first_name'], array('hassan','rabban','mac'))){
    echo 'You are not authorised to view';
    die;
}

$tpl = file_get_contents('tpl.html');

$search = addslashes($_POST['searchInp']);

$query = 'SELECT DISTINCT `from` FROM email_queue WHERE `from` <> "'. $search . '" limit 200';
$result = mysql_query($query);

$i = 1;


if(!empty($search)){
    $queryS = "SELECT DISTINCT `from` FROM email_queue WHERE `from` = '$search'  order by id desc";
    $resultS = mysql_query($queryS);
    $infoS = mysql_fetch_array($resultS);
    
    $htm .= '<div class="box-item" style="border: 3px solid black">
                <b>'. $i .'</b>
                <div class="i-group">
                    <input class="input" type="text" readonly style="padding:10px;width:200px;" value="'. $infoS['from'] .'">
                    <input class="input chkClass" name="chkInp" type="checkbox" checked>
                </div>
            </div>';
    $i++;
}else{
    $htm = '';
}



while($info = mysql_fetch_array($result)){

    $htm .= '<div class="box-item">
                        <b>'. $i .'</b>
                        <div class="i-group">
                            <input class="input" type="text" readonly style="padding:10px;width:200px;" value="'. $info['from'] .'">
                            <input class="input chkClass" name="chkInp" type="checkbox">
                        </div>
            </div>';
$i++;
}

$brandName = getBrandSelectedName($brand);
$tpl = str_replace('{htm}',$htm, $tpl);

output($tpl, $options);
