<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');
function limitString($string, $limit) {
    if (strlen($string) > $limit) {
        return substr($string, 0, $limit) . '...';
    } else {
        return $string;
    }
}

if(!empty($_POST['submit'])){
    $id = addslashes($_POST['submit']);

    mysql_query("UPDATE `content` SET `difference` = 0 WHERE id = $id");
}

// live connection DB:

// $conn1 = mysql_connect($dbServer1, $dbUser1, $dbPass1) or die(mysql_error());
// mysql_select_db($dbName1, $conn1);

$Query = mysql_query("SELECT * from `content` WHERE `difference` = 1");

$datahtml = '';
if (mysql_num_rows($Query) > 0) {
    while ($data = mysql_fetch_array($Query)) {
        // $liveQueryRun = mysqli_query($conn1,"SELECT * FROM content WHERE `country` = '$testCountry' AND `name` = '$testName' AND `page` = '$testPage' ORDER BY ID DESC;");
        // $liveData = mysqli_fetch_assoc($liveQueryRun);
        // $liveContent = $liveData['content'];

        $datahtml .= ' <tr>
        <td>'. $data['page'] .'</td>
        <td>'. $data['name'] .'</td>
        <td>'. limitString($data['content'], 40) .'</td>
        <td>
            '. limitString($liveContent, 40) .'
        </td>
        <td><form method="post">
            <button class="btn btn-primary btn-transparent" name="submit" value = "'. $data['id'] .'"><svg xmlns="http://www.w3.org/2000/svg" x="0px"
                    y="0px" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M9 19.4L3.3 13.7 4.7 12.3 9 16.6 20.3 5.3 21.7 6.7z"></path>
                </svg></button><form>
        </td>
    </tr>';
    }
}else{  
    $datahtml .= '<tr><td colspan = "5" >No Records</td></tr>';
}


$tpl = str_replace('{datahtml}', $datahtml, $tpl);
output($tpl, $options);
