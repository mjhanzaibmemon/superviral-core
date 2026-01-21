<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/foodie.app/config/config.php';

$Query_eq = mysql_query('SELECT * FROM `ext_post_qc` WHERE `ue_done` = 0 AND `ue_id` != "" AND `caption_items` != "" LIMIT 100');
$i = 1;
echo '<pre>';

while ($row = mysql_fetch_array($Query_eq)) {
        $caption = $row['caption_items'];
        $ue_id = $row['ue_id'];

        $captionArr = array_map('trim', explode(',', $caption));
        
        if (isset($captionArr)) {
            $item_data = get_ue_items($captionArr, $ue_id);
            $ue_ids = $item_data['ids'];
            $ue_tags = $item_data['tags'];
            print_r($item_data);
            mysql_query('UPDATE ext_post_qc SET `ue_items`="'.$ue_ids.'", `keywords`="'.trim($ue_tag).'", `ue_done`=1 WHERE id='.$row['id']);
            echo 'UPDATE ext_post_qc SET `ue_items`="'.$ue_ids.'", `keywords`="'.trim($ue_tag).'", `ue_done`=1 WHERE id='.$row['id'];

        } else {
            echo 'No items found';
        }
        echo '<hr>';
        $i++;
}


function get_ue_items($arr, $ue_id){
    
    $ue_ids = array();
    $ue_tags = array();
    
    foreach ($arr as $item) {
        $clean_name = trim($item);

        $check_menu = mysql_query("SELECT * FROM ext_uber_menu WHERE title like '%$clean_name%' AND `restaurant_id` = {$ue_id}");
        if (mysql_num_rows($check_menu) > 0) {
            while($ue_row = mysql_fetch_array($check_menu)){
                $ue_ids[]= $ue_row['id'];
                $ue_tags[] = $ue_row['tags'];
            }
        }
    }

    $results['ids'] = implode(',',$ue_ids);
    $results['tags'] = implode(' ',$ue_tags);

    return $results;
}