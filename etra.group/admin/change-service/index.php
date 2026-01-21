<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$category = $_GET['category'] ? $_GET['category'] : 'followers';
$jap_filter = $_GET['jap'] ? $_GET['jap'] : 'jap1';
if($category == 'freeautoviews'){
	$jap_filter = 'views_jap1';
}
$socialMedia = $_GET['socialMedia'] ? $_GET['socialMedia'] : 'ig';

if(isset($_POST['update'])){
	$id = addslashes($_POST['id']);
	$col = addslashes($_POST['jap_name']);
	$value = addslashes($_POST['jap_value']);
	
	if($category == "freeautolikes" || $category == "freeautoviews"){
		$update_q = mysql_query('UPDATE automatic_likes_packages SET `'.$col.'`='.$value.' WHERE id='.$id.' LIMIT 1');
	}else{
		$update_q = mysql_query('UPDATE packages SET `'.$col.'`='.$value.' WHERE id='.$id.' LIMIT 1');

	}
}


// tpl
$table_tpl = tpl_get('table',$tpl);
$row_tpl = tpl_get('row',$table_tpl);
$i = 1;

if($category == "premiumfollowers" || $category == "premiumlikes" || $category == "premiumviews"){
	$premiumCatergory = $category;
	$premium = 1;
	$category = str_replace('premium', '', $category);
}else{
	$premium = 0;
}

$q = mysql_query('SELECT * FROM packages WHERE `brand`="'.$brand.'" AND `socialmedia`="'.$socialMedia.'" AND `type`="'.$category.'" AND `premium`= '. $premium .' ORDER BY amount ASC');

if($category == "freeautolikes"  || $category == "freeautoviews"){
	$q = mysql_query('SELECT * FROM automatic_likes_packages WHERE `brand`="'.$brand.'" ORDER BY amount ASC');
}

while($info = mysql_fetch_array($q)){
	// create rows for 3 different tables
	$info['row_num'] = $i;
	$row_jap1.=create_row('jap1',$row_tpl,$info);

	$i++;
	$info['row_num'] = $i;
	$row_jap2.=create_row('jap2',$row_tpl,$info);

	$i++;
	$info['row_num'] = $i;
	$row_jap3.=create_row('jap3',$row_tpl,$info);

	if($category == "freeautoviews"){
		$i++;
		$info['row_num'] = $i;
		$row_jap4.=create_row('views_jap1',$row_tpl,$info);
	}
	
	$i++;

}
// create 3 tables
$table.=create_tbl('jap1',$table_tpl,$row_jap1);
$table.=create_tbl('jap2',$table_tpl,$row_jap2);
$table.=create_tbl('jap3',$table_tpl,$row_jap3);
if($category == "freeautoviews"){
	$table.=create_tbl('views_jap1',$table_tpl,$row_jap4);
}
$tpl = tpl_replace('table',$table,$tpl);


if(!empty($_POST['submit'])){

	$msg = addslashes($_POST['noticeMsg']);
	$query = mysql_query("DELETE FROM notice_msg WHERE brand = '$brand'");
	$insert = mysql_query("INSERT INTO notice_msg SET `message`='{$msg}', brand = '$brand'");
}

if(!empty($_POST['delete'])){

	$deleteId = addslashes($_POST['deleteId']);
	$delete = mysql_query('DELETE FROM notice_msg WHERE id ='. $deleteId . ' AND brand = "' .$brand. '"');
}

$query = mysql_query("SELECT * FROM notice_msg WHERE brand = '$brand' LIMIT 1");
$count = mysql_num_rows($query);
$data = mysql_fetch_array($query);

$tpl = str_replace('{msgId}', $data['id'],$tpl);

if($category == "freeautolikes"){
	$japOption = '<option class="option-jap1" value="jap1">Jap1</option>';
}else if($category == "freeautoviews"){
	$japOption = '<option class="option-views_jap1" value="jap1">Views Jap1</option>';
}else{
	$japOption = ' <option class="option-jap1" value="jap1">Jap1</option>';
}

					
$tpl = str_replace('{japOption}', $japOption,$tpl);

if(!empty($premiumCatergory)){
	$tpl = str_replace('class="category-'.$premiumCatergory.'"', 'class="category-'.$premiumCatergory.'" selected',$tpl);

}else{
	$tpl = str_replace('class="category-'.$category.'"', 'class="category-'.$category.'" selected',$tpl);

}
$tpl = str_replace('class="option-'.$jap_filter.'"', 'class="option-'.$jap_filter.'" selected',$tpl);
$tpl = str_replace('class="option-'.$socialMedia.'"', 'class="option-'.$socialMedia.'" selected',$tpl);
$tpl = str_replace('{noticeMsgDisplay}', $data['message'],$tpl);

output($tpl, $options);


function create_row($jap,$row,$package){
	$row = str_replace('{jap_type}',$jap,$row);
	$row = str_replace('{jap}',$package[$jap],$row);
	$row = str_replace('{id}',$package['id'],$row);
	$row = str_replace('{i}',$package['row_num'],$row);
	$package['type'] = !empty($package['type']) ? $package['type'] : 'likes';
	$row = str_replace('{name}',$package['amount'].' '.$package['type'],$row);
	$row = str_replace('{type}',$package['type'],$row);
	return $row;
}

function create_tbl($jap,$tbl,$row){
	global $jap_filter;
	
	$tbl = str_replace('{jap}',$jap,$tbl);
	$tbl = str_replace('{tbl_id}',$jap,$tbl);
	if($jap !== $jap_filter){$tbl = str_replace('{display_tbl}','tbl-hide',$tbl);}


	$tbl = tpl_replace('row',$row,$tbl);
	return $tbl;
}
