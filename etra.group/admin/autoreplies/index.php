<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');



$q = mysql_query("SELECT * FROM `email_autoreplies` ORDER BY `id` DESC");

while ($info = mysql_fetch_array($q)) {
    
    if($info['showdefault'] == 1) $showdefault = "Yes"; else $showdefault = "No";
    
    // $brandName = getBrandSelectedName($brand);

    $autoreplies .= '<tr>

                    <td>
                    <b>'. $info['title'] .'</b>
                    </td>
                    <td>
                       '. $info['autoreply'] .'
                    </td>
                     <td>
                       '. $info['page'] .'
                    </td>
					<td>' . $showdefault . '</td>
					<td style="width: 170px;">
                    <a class="btn btn-primary color3" href="editautoreply.php?id=' . $info['id'] . '">EDIT</a>
                    <a class="btn btn-primary color3" onclick = "return confirm(\' Are you sure to delete this autoreply? \')" href="editautoreply.php?did=' . $info['id'] . '">Delete</a>
					</td>

				<tr>';

}

if ($_GET['message'] == '5') {
    $message = '<div class="emailerror">Removed Successfully.</div><br>';
}

$tpl = str_replace('{autoreplies}',$autoreplies, $tpl);
$tpl = str_replace('{message}', $message, $tpl);


output($tpl, $options);
