<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;


require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('edit_tpl.html');


$id = addslashes($_GET['id']);
$category = addslashes($_GET['category']);


$btnSave = addslashes($_POST['save']);

if(!empty($btnSave)){

    foreach ($_POST as $key => $value) {
        if ($key !== 'save') { // Ensure the 'save' key is skipped
            mysql_query("
                UPDATE `ai_support_fixed_kb` 
                SET `value` = '" . addslashes($value) . "' 
                WHERE `type` = '$key' AND `parent_id` = '$id'
            ");
        }
    }
    
}


$formQ = mysql_query("SELECT * FROM `ai_support_fixed_kb` WHERE `parent_id` ='$id'");


$formHtm = "";
while ($form = mysql_fetch_array($formQ)) {

    if($form['type'] == 'api'){continue;}
    
    if($form['type'] == 'info'){

        $formHtm .= '<tr>
                    <td>'. ucfirst($form['type']) .'</td>
                    <td>
                        <textarea rows=10 name="'. strtolower($form['type']) .'" class="input">'. $form['value'] .'</textarea>
                    </td>
                </tr>';

    }else{
        $formHtm .= '<tr>

                    <td>'. ucfirst($form['type']) .'</td>
                    <td><input name="'. strtolower($form['type']) .'" class="input" value="'. $form['value'] .'" autocomplete="off"></td>

                </tr>';
    }

   
}


$tpl = str_replace('{form}',$formHtm,$tpl);
$tpl = str_replace('{category}',$category,$tpl);


output($tpl, $options);
