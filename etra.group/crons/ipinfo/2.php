<?php
set_time_limit(0);

echo '<br> 2.php Started <br>';

$i = 1;
$now = time();

// Open the CSV file
if (($handle = fopen($csvFile, "r")) !== false) {
    $headers = fgetcsv($handle);

    $existingIPs = [];
    $res = mysql_query("SELECT start_ip, end_ip FROM ipinfo");
    while ($r = mysql_fetch_array($res)) {
        $existingIPs[$r['start_ip'] . '-' . $r['end_ip']] = true;
    }
    // print_r($existingIPs) . '<br>';
    // Process each row
    while (($row = fgetcsv($handle)) !== false) {
        // if($i == 10){die;}
        $data = array_combine($headers, $row); // Convert to associative array
        // print_r($data);
        echo '<br>';
        echo $data['start_ip'] . ' - ' . $data['end_ip'] . '<br>';
        // $ipList = getIPRange($data['start_ip'], $data['end_ip']);

        // $ip_address = $data['start_ip'] . ' ' . $data['end_ip'];
        // print_r($ipList);
        // $ip = '1.6.7.13';
        // $var = checkIfExist($ip , $ipList);
        // echo $var . 'aj<br>';
        $key = $data['start_ip'] . '-' . $data['end_ip'];

        // echo $key . '<br>';
        if (isset($existingIPs[$key])) {
            echo 'IP Already Exist';
            continue;
        }
        $q = mysql_query("INSERT INTO `ipinfo` SET `start_ip`='{$data['start_ip']}', `end_ip`='{$data['end_ip']}', `country_code`='" . $data['country'] . "', `added`='" . $now . "'");
        echo  '<hr>';
        $i++;
    }


    fclose($handle);
    echo "CSV data successfully inserted into the database!";
} else {
    echo "Error opening the CSV file.";
}

// function checkIfExist($ip , $ipList){
//     if(in_array($ip, $ipList)){
//         return 'IP Found';
//     }else{
//         return 'IP Not Found';
//     }
// }

function getIPRange($startIP, $endIP)
{
    $start = ip2long($startIP);
    $end = ip2long($endIP);

    if ($start === false || $end === false) {
        return "Invalid IP address.";
    }

    $ips = [];
    for ($ip = $start; $ip <= $end; $ip++) {
        $ips[] = long2ip($ip);
    }

    return $ips;
}
