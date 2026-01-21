<?php
$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
  $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';
$key = $brightdata_key;

echo '<pre>';

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.brightdata.com/datasets/snapshots?dataset_id=gd_l1vikfch901nx3by4",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer $key",
    "Content-Type: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
// echo $response;

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  $response = json_decode($response, true);
  //  print_r($response);
  if (isset($response)) {
    foreach ($response as $snapshot) {
      echo "<b>Snapshot ID: " . $snapshot['id'] . " - " . $snapshot['status'] . "</b><br>";
      // echo "Created At: " . $snapshot['created_at'] . "<br>";
      // echo "Status: " . $snapshot['status'] . "<br>";
      // echo "Dataset size: " . $snapshot['dataset_size'] . "<br>";

      if ($snapshot['status'] == 'ready') {
        
        $returnData =  downloadData($snapshot['id'], $key);
        if ($returnData != "CURL Error") {
          // echo "Data downloaded successfully.<br>";
          
          foreach ($returnData as $data) {
            // echo 'account (username) -' . $data['account'] . '<br>';
            // echo 'full_name - ' . $data['full_name']  . '<br>';
            // echo 'is_business_account -' . $data['is_business_account'] . '<br>';
            // echo 'is_professional -' . $data['is_professional_account'] . '<br>';
            // echo 'followers (count) -' . $data['followers'] . '<br>';
            // echo 'following (count) -' . $data['following'] . '<br>';
            // echo 'business_category_name -' . $data['business_category_name'] . '<br>';
            // echo 'category_name -' . $data['category_name'] . '<br>';
            // echo 'biography -' . $data['biography'] . '<br>';
            // echo 'business_address -' . $data['business_address'] . '<br>';
            // echo 'email_address -' . $data['email_address'] . '<br>';

            if (is_array($data['external_url'])) {
              $external_url = implode(',', $data['external_url']);
            } else {
              $external_url = $data['external_url'];
              $external_url = getDomainOnly($external_url);
            }
            // echo 'external_url -' . $external_url . '<br>';

            $check_profile = mysql_query("SELECT * FROM `ext_instagram_profiles` 
            WHERE `username` = '" . addslashes($data['account']) . "' limit 1");

            if (mysql_num_rows($check_profile) > 0) {
              echo '<b>Profile already exists in the database.</b><br>';
              continue; // Skip to the next iteration if profile exists
            }

            $insert_profiles = mysql_query("INSERT INTO `ext_instagram_profiles` 
                                                (`username`, `full_name`, `is_business_account`, 
                                                `is_professional_account`, `followers`, `following`, 
                                                `business_category_name`, `category_name`, `biography`, 
                                                `business_address`, `email_address`, `external_url`) 
                      VALUES ('" . addslashes($data['account']) . "', '" . addslashes($data['full_name']) . "', 
                      '" . addslashes($data['is_business_account']) . "', '" . addslashes($data['is_professional_account']) . "',
                       '" . addslashes($data['followers']) . "', '" . addslashes($data['following']) . "', 
                       '" . addslashes($data['business_category_name']) . "', '" . addslashes($data['category_name']) . "', 
                       '" . addslashes($data['biography']) . "', '" . addslashes($data['business_address']) . "', 
                       '" . addslashes($data['email_address']) . "', '" . addslashes($external_url) . "')");

            $inserted_id = mysql_insert_id();           

            if(!empty($data['related_accounts'])){

              foreach ($data['related_accounts'] as $relatedAccount) {

                $insert_related = mysql_query("INSERT INTO `ext_instagram_related_accounts` (`profile_name`, `user_name`, `ig_id`) 
                                                VALUES ('" . addslashes($relatedAccount['profile_name']) . "', '" . addslashes($relatedAccount['user_name']) . "', '" . addslashes($inserted_id) . "')");

                echo '<br><b>Related Account:</b><br>';

                echo 'profile_name - ' . $relatedAccount['profile_name'] . '<br>';
                echo 'user_name - ' . $relatedAccount['user_name'] . '<br>';
              }

            }

            echo '<br>================================<br>';
          }

         
          // print_r($returnData);

        } else {
          echo "Error downloading data for snapshot ID: " . $snapshot['id'] . "<br>";
        }
      }

      echo "<br>------------------------<br>";
    }
  } else {
    echo "No snapshots found or invalid response format.<br>";
  }
}

function downloadData($snapshotId, $token)
{
  $url = "https://api.brightdata.com/datasets/filter";
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.brightdata.com/datasets/snapshots/$snapshotId/download",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
      "Authorization: Bearer $token",
      "Content-Type: application/json"

    ],
  ]);

  $response = curl_exec($curl);
  $err = curl_error($curl);
  // echo $response;

  $lines = explode("\n", trim($response));

  $data = [];
  foreach ($lines as $line) {
    $decoded = json_decode($line, true);
    if (json_last_error() === JSON_ERROR_NONE) {
      $data[] = $decoded;
    } else {
      echo "JSON error on line: $line\n";
    }
  }

  curl_close($curl);
  if ($err) {
    return "CURL Error";
  } else {
    return $data;
  }
}

function getDomainOnly($url) {
    // Add scheme if missing
    if (!preg_match('#^https?://#', $url)) {
        $url = 'http://' . $url;
    }

    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return '';

    // Remove leading www.
    $host = preg_replace('/^www\./i', '', $host);

    // Split domain into parts
    $parts = explode('.', $host);

    // Handle domain like: example.co.uk, site.com, etc.
    $count = count($parts);
    if ($count >= 2) {
        $tld = $parts[$count - 1];
        $sld = $parts[$count - 2];

        // Check for 2-level TLDs like co.uk, com.au, etc.
        $common2LevelTLDs = ['co.uk', 'org.uk', 'gov.uk', 'ac.uk', 'com.au', 'co.in'];
        $last2 = $parts[$count - 2] . '.' . $parts[$count - 1];
        if (in_array($last2, $common2LevelTLDs) && $count >= 3) {
            return $parts[$count - 3] . '.' . $last2;
        }

        return $sld . '.' . $tld;
    }

    return $host; // fallback
}
