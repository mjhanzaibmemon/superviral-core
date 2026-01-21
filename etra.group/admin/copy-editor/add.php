
<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('add.html');

function getbetween($content, $start, $end)
{
    $r = explode($start, $content);
    if (isset($r[1])) {
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}



$countries = array(
    // 'ww' => 'Worldwide',
    'us' => 'United States',
    'uk' => 'United Kingdom',
    'it' => 'Italy',
    'de' => 'Germany',
    'fr' => 'France',
    'es' => 'Spain'
);

$time = time();

if (!empty($_POST['submit'])) {


    $country = addslashes($_POST['country']);
    $page = addslashes($_POST['page']);
    $name = addslashes($_POST['name']);
    $content = addslashes(trim($_POST['content']));

    $name = str_replace('{', '', $name);
    $name = str_replace('}', '', $name);
    $name = trim($name);

    $name1 = getbetween($content, '{', '}');

    if (!empty($name1)) {


        $name = $name1;
        $content = str_replace('{' . $name . '} ', '', $content);
        $content = str_replace('{' . $name . '}', '', $content);
    }

    if (empty($name)) $failed = 'No tag found';
    if (empty($content)) $failed = 'No content found';

    ////// CHECK FOR DUPLICATES
    $checkduplicate = mysql_query("SELECT * FROM `content` WHERE `page` = '$page' AND `country` = '$country' AND `name` = '$name' AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");
    if (mysql_num_rows($checkduplicate) == 1) $failed = 'Duplicate found for this <b>page</b> and country';

    $checkduplicate = mysql_query("SELECT * FROM `content` WHERE `page` = 'global' AND `country` = '$country' AND `name` = '$name' AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");
    if (mysql_num_rows($checkduplicate) == 1) $failed = 'Duplicate found for this <b>page</b> and country';
    /////

    if (empty($failed)) {

        $insertq = mysql_query("INSERT INTO `content`
            SET 
            `country` = '$country', 
            `page` = '$page', 
            `name` = '$name', 
            `content` = '$content', brand = '$brand', added = '$time'");

        if ($country == 'ww') {

            $insertq = mysql_query("INSERT INTO `content`
            SET 
            `country` = 'us', 
            `page` = '$page', 
            `name` = '$name', 
            `content` = '$content', brand = '$brand', added = '$time'");


            $insertq = mysql_query("INSERT INTO `content`
            SET 
            `country` = 'uk', 
            `page` = '$page', 
            `name` = '$name', 
            `content` = '$content', brand = '$brand', added = '$time'");
        }

        if ($insertq) $reviewmessage = '<div class="emailsuccess">Submitted: <b>' . $name . '</b> Thank you!</div>';
    } else {
        $reviewmessage = '<div class="emailsuccess" style="background-color:red;">Failed: ' . $failed . '</div>';
    }
}


$pcq = mysql_query("SELECT * FROM `content` WHERE `page` = '$page' AND `country` = '$country' AND brand = '$brand' ORDER BY `id` DESC LIMIT 10");

while ($pcqinfo = mysql_fetch_array($pcq)) {

    $previouscontent .= '<tr>
    
        <td><div class="foo" >
    <textarea class="language-less">{' . $pcqinfo['name'] . '}</textarea>
    <button class="btn btn3 report copy-button">{' . $pcqinfo['name'] . '}</button> - <b>' . $pcqinfo['name'] . '</b>
    </div></td>
    
    
    <td><font color="grey">' . $pcqinfo['country'] . ' - ' . $pcqinfo['page'] . '</font></td>
    
    
    </tr>';
}

if (empty($name)) {
    $newmname = $name;
} else {

    $newname = $name;
    /*	$substr = substr("$name", -1);
    
        $newname = substr($name, 0, -1).($substr+1);*/
}

$findpageq = mysql_query("SELECT `page` FROM `content` WHERE brand = '$brand' GROUP BY `page`");

while ($pageinfo = mysql_fetch_array($findpageq)) {

    if ($pageinfo['page'] == $_POST['page']) $selected = 'selected="selected"';

    $pages1 .= '<option value="' . $pageinfo['page'] . '" ' . $selected . '>' . $pageinfo['page'] . '</option>';

    unset($selected);
}

foreach ($countries as $key => $country1) {

    if ($key == $country) $selected = 'selected="selected"';

    $countryselect .= '<option value="' . $key . '" ' . $selected . '>' . $country1 . '</option>';
    unset($selected);
}


$tpl = str_replace('{country}', $countryselect, $tpl);
$tpl = str_replace('{reviewmessage}', $reviewmessage, $tpl);
$tpl = str_replace('{previouscontent}', $previouscontent, $tpl);
$tpl = str_replace('{pages1}', $pages1, $tpl);
$tpl = str_replace('{newname}', $newname, $tpl);


output($tpl, $options);
