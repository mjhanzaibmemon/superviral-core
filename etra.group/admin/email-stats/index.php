<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$categories = addslashes(trim($_GET['categories']));

if($categories){
    $categories = urldecode($categories);

    if (empty($categories)) {
        $errorMsg = "please select categories";
    }
    
    if (!empty($categories)) {
        $sql_time_30days = time() - (30 * 24 * 60 * 60); // 30 days
        $q = mysql_query("SELECT * FROM email_sent_stats WHERE category = '$categories'  AND `sent_date` >= $sql_time_30days ORDER BY id DESC LIMIT 50");
    }
    
    $brandName = getBrandSelectedName($brand);
    $domain = getBrandSelectedDomain($brand);
    if ($q) {
    
    
        while ($info = mysql_fetch_array($q)) {

            $results .= '<tr>

                             <td>

                             '. date('d/m/y',$info['sent_date']) .'

                             </td>

                             <td>
                             '. $info['category'] .'

                             </td>

                             <td>
                             '. $info['recipient_email'] .'

                             </td>

                             <td>
                             <a href="/admin/email-stats/view/?id='. $info['id'] .'" target="_blank" onclick class="btn btn3 report"
                             >View Email</a>
                             </td>

  
                        <tr>';
        }
    
        if (!empty($results)) {
            $results = '' . $results . '';
        }

        if (!empty($categories) && mysqli_num_rows($q) < 1)
        $errorMsg = '<div class="emailsuccess emailfailed">No Data Found</div>';

    }
    

}else{

    $sql_time_7days = time() - (7 * 24 * 60 * 60); // 7 days
    $sql_time_30days = time() - (30 * 24 * 60 * 60); // 30 days
    
    $q = mysql_query("
        SELECT 
            category, 
            SUM(CASE WHEN `sent_date` >= {$sql_time_7days} THEN 1 ELSE 0 END) AS count_last_7_days,
            SUM(CASE WHEN `sent_date` >= {$sql_time_30days} THEN 1 ELSE 0 END) AS count_last_30_days
        FROM 
            email_sent_stats
        WHERE 
            `sent_date` >= {$sql_time_30days}
        GROUP BY 
            category
        ORDER BY 
            count_last_7_days DESC, count_last_30_days DESC
    ");
    
    while ($info = mysql_fetch_array($q)) {

        $results .= '<tr>
                        <td>'. $info['category'] .'</td>
                        <td>'. $info['count_last_7_days'] .'</td>
                        <td>'. $info['count_last_30_days'] .'</td>
                        <td>
                            <a href="/admin/email-stats/?categories='. urlencode($info['category']) .'" class="btn btn3 report">View</a>
                        </td>


                    <tr>';
    }
}


$catQuery = "SELECT category FROM email_sent_stats GROUP BY category; ";
$queryRun = mysql_query($catQuery);
$categoriesOption = "<option value=''>Select</option>";
while($res = mysql_fetch_array($queryRun)){
   $categoriesOption .= "<option>". $res['category'] ."</option>";
}

$tpl = str_replace('{categoriesOption}', $categoriesOption, $tpl);
$tpl = str_replace('{summaryresults}', $summaryresults, $tpl);
$tpl = str_replace('{results}', $results, $tpl);
$tpl = str_replace('{errorMsg}', $errorMsg, $tpl);


$tpl = str_replace('{display_categories}', ($_GET['categories'] ? 'display:none' : ''), $tpl);
$tpl = str_replace('{display_emails}', ($_GET['categories'] ? '' : 'display:none'), $tpl);
output($tpl, $options);
