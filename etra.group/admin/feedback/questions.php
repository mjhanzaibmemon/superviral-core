<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('questions.html');
$_POST['readQuestion'] = addslashes($_POST['readQuestion']);
$_POST['readFeedback'] = addslashes($_POST['readFeedback']);

if (isset($_POST['readQuestion'])) {

    $q = mysql_query("UPDATE user_questions SET checked='1' WHERE id='$_POST[readQuestion]' AND brand = '$brand'");
}

if (isset($_POST['readFeedback'])) {
    $q = mysql_query("UPDATE feedbacks SET reviewed='1' WHERE id='$_POST[readFeedback]' AND brand = '$brand'");
}

$q = mysql_query("SELECT *  FROM `feedbacks` WHERE reviewed = '0' AND brand = '$brand' ORDER BY `id` DESC");
$feedbackData = "";
while ($info = mysql_fetch_array($q)) {


    $feedbackData .= '
    <tr>
    <td>' . $info['email'] . '</td>
    <td></td>

    <td>' . $info['feedback'] . '</td>
    <td></td>

    <td>' . ago($info['createdAt']) . '</td>';
    $feedbackData .= '<td><form href="/admin/feedback/questions.php" method ="POST"><input type="hidden" name="readFeedback" value="' . $info['id'] . '"><button class="btn btn3" style="margin: 0px !important;" href="#0" onclick="return confirm(\'Are you sure you read it?\');" >Read</button></form></td>';
    '</tr>';
}

$q = mysql_query("SELECT *  FROM `user_questions` WHERE Checked = '0' AND brand = '$brand' ORDER BY `id` DESC");
$questionData = "";
while ($info = mysql_fetch_array($q)) {


    $questionData .= '
                    <tr>
                      <td class="page">' . $info['page'] . '</td>
                      <td></td>

                      <td class="message">' . $info['question'] . '</td>
                      <td></td>

                      <td class="time">' . ago($info['createdAt']) . '</td>';
                      
    $questionData .= '<td><form href="/admin/feedback/questions.php" method ="POST"><input type="hidden" name="readQuestion" value="' . $info['id'] . '"><button class="btn btn3" style="margin: 0px !important;" href="#0" onclick="return confirm(\'Are you sure you read it?\');" >Read</button></form></td>';
    '</tr>';
}


$tpl = str_replace('{feedbackData}',$feedbackData,$tpl);
$tpl = str_replace('{questionData}',$questionData,$tpl);
$tpl = str_replace('{brand}',strtolower($brand_arr[$brand]),$tpl);

output($tpl, $options);
