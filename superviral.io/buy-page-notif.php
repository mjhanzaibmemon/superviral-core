<?php

include_once('header.php');

$arr = array();
$page = $_GET['page'];
$offset = $_GET['offset'] ? ',100' : '';

switch($page){
    case 'ig-followers':
        $packagetype = 'followers';
        $socialmedia = 'ig';
        break;
    case 'ig-likes':
        $packagetype = 'likes';
        $socialmedia = 'ig';
        break;
    case 'ig-views':
        $packagetype = 'views';
        $socialmedia = 'ig';
        break;
    case 'ig-comments':
        $packagetype = 'comments';
        $socialmedia = 'ig';
        break;
    case 'tt-followers':
        $packagetype = 'followers';
        $socialmedia = 'tt';
        break;
    case 'tt-likes':
        $packagetype = 'likes';
        $socialmedia = 'tt';
        break;
    case 'tt-views':
        $packagetype = 'views';
        $socialmedia = 'tt';
        break;
    default :
        break;
}




function getRandomTime() {
    // Define the primary and secondary ranges in minutes
    $primaryRange = range(3, 15); // 3–15 minutes ago
    $secondaryRangeShort = range(16, 45); // 16–45 minutes ago
    $secondaryRangeLong = range(60, 180); // 1–3 hours ago

    // Weighted probabilities
    $weights = [
        'primary' => 80,  // 80% chance for primary range
        'secondaryShort' => 15, // 15% chance for secondary short range
        'secondaryLong' => 5   // 5% chance for secondary long range
    ];

    // Randomly pick a range based on weights
    $randomWeight = rand(1, array_sum($weights));
    $range = null;

    if ($randomWeight <= $weights['primary']) {
        $range = $primaryRange;
    } elseif ($randomWeight <= $weights['primary'] + $weights['secondaryShort']) {
        $range = $secondaryRangeShort;
    } else {
        $range = $secondaryRangeLong;
    }

    // Select a random value from the chosen range (minutes ago)
    $minutesAgo = $range[array_rand($range)];

    // Calculate the Unix timestamp
    $currentTimestamp = time(); // Current time in Unix timestamp
    $randomTimestamp = $currentTimestamp - ($minutesAgo * 60); // Subtract minutes ago in seconds

    return $randomTimestamp;
}



$arr = ['data' => []];
$sql = 'SELECT amount, added FROM orders WHERE packagetype="'.$packagetype.'" AND socialmedia="'.$socialmedia.'" AND brand="sv" ORDER BY id DESC LIMIT 100'.$offset;
$q = mysql_query($sql);
while($row = mysql_fetch_array($q)){

    $row['added'] = getRandomTime();
    
    $arr['data'][] = [
        'amount' => $row['amount'],
        'added' => $row['added']
    ];
}



echo json_encode($arr);
