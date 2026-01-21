<?php

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';
$googleApiKey = $google_place_key;
$serp_api_key = $serp_api_key;

echo '<pre>';

$cat_query = mysql_query('SELECT * FROM category WHERE done = 0');
while($cat_data = mysql_fetch_array($cat_query)){

    $category = $cat_data['name'];

    $loc_query = mysql_query('SELECT * FROM locations');
    while($loc_data = mysql_fetch_array($loc_query)){

        $address = $loc_data['area'] . ' ' . $loc_data['town'] . ' ' . $loc_data['postcode'];
        $placeQuery = $category . ' in ' . $address;

        echo "Address: $address - $category<br>";

       
        $links = getPlaceData($placeQuery, $googleApiKey);
        if ($links) {
            // print_r($links);
            foreach ($links['places'] as $link) {
                // print_r($link);die;

                $address = $link['formattedAddress'];
                $google_url = !empty($link['googleMapsLinks']['placeUri']) ? $link['googleMapsLinks']['placeUri']: $link['googleMapsLinks']['directionsUri'];

                $addressParts = extractAddressParts($address);

                $name = addslashes($link['displayName']['text']) ?? '';
                $address = addslashes($addressParts['address']) ?? '';
                $phone = $link['internationalPhoneNumber'] ?? '';
                $city = addslashes($addressParts['city']) ?? '';
                $country = $addressParts['country'] ?? '';
                $pincode = $addressParts['postcode'] ?? '';
                $latitude = $link['location']['latitude'] ?? '';
                $longitude = $link['location']['longitude'] ?? '';
                $google_place_id = $link['id'];

                $website = getDomainOnly($link['websiteUri']);

                // debug 
                // echo "Name: $name<br>";
                // echo "Address: $address<br>";
                // echo "Phone: $phone<br>";
                // echo "City: $city<br>";
                // echo "Country: $country<br>";
                // echo "Pincode: $pincode<br>";
                // echo "Latitude: $latitude<br>";
                // echo "Longitude: $longitude<br>";
                // echo "Google Place ID: $google_place_id<br>";
                // echo "Website: $website<br>";

                $checkif_exists = mysql_query("SELECT * FROM ext_restaurants WHERE google_place_id = '$google_place_id' limit 1");
                if (mysql_num_rows($checkif_exists) > 0) {
                    echo "Restaurant already exists: $name<br>";
                    continue; // Skip to the next iteration if it exists
                }

               
                $insert_restaurant = mysql_query("INSERT INTO ext_restaurants (name, address, phone, city, 
                country, pincode, latitude, longitude, google_place_id, source, google_url ) 
                VALUES (
                    '" . $name . "',
                    '" . $address . "',
                    '" . $phone . "',
                    '" . $city . "',
                    '" . $country . "',
                    '" . $pincode . "',
                    '" . $latitude . "',
                    '" . $longitude . "',
                    '" . $google_place_id . "',
                    'google',
                    '" . $google_url . "'
                )");

                $result_id = mysql_insert_id();

                if(empty($website)) continue;
                $insert_website = mysql_query("INSERT INTO ext_restaurants_website (restaurant_id, website, source) VALUES ($result_id, '$website','google')");
                echo "<hr>";
               
            }

            
            // echo "Place ID: " . htmlspecialchars($placeId) . "<br>";
            // Get place details by ID
            // $details = getPlaceDetailsById($placeId, $googleApiKey);
            // print_r($details);
        } else {
            echo "No matching business found for: " . htmlspecialchars($placeQuery) . "<br>";
        }
    }

    mysql_query("UPDATE category SET done = 1 WHERE id = " . $cat_data['id']);
    // die;
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


function extractAddressParts($address) {
    $parts = explode(',', $address);
    $parts = array_map('trim', $parts); // Trim extra spaces

    $country = $parts[count($parts) - 1] ?? '';
    $postcodeCity = $parts[count($parts) - 2] ?? '';
    $street = implode(', ', array_slice($parts, 0, count($parts) - 2));

    // Try to split postcode and city
    $postcode = '';
    $city = '';
    if (preg_match('/(.+)\s+([A-Z]{1,2}\d{1,2}\s?\d[A-Z]{2})$/i', $postcodeCity, $matches)) {
        $city = trim($matches[1]);
        $postcode = trim($matches[2]);
    } else {
        $city = $postcodeCity;
    }

    return [
        'address' => $address,
        'street' => $street,
        'city' => $city,
        'postcode' => $postcode,
        'country' => $country
    ];
}


function getPlaceDetailsById($placeId, $apiKey) {
    $fields = 'name,formatted_address,website,international_phone_number,rating,url,geometry';
    $detailsUrl = "https://maps.googleapis.com/maps/api/place/details/json?place_id=$placeId&fields=$fields&key=$apiKey";

    $response = curlGet($detailsUrl);

    if (!$response) return false;

    $data = json_decode($response, true);
    return $data['result'] ?? null;
}

function curlGet($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return $response;
}

function getPlaceData($query, $apiKey) {
    $url = "https://places.googleapis.com/v1/places:searchText";

    $postData = json_encode([
        'textQuery' => $query
    ]);

    $headers = [
        "Content-Type: application/json",
        "X-Goog-Api-Key: $apiKey",
        "X-Goog-FieldMask: places.id,places.displayName,places.formattedAddress,places.location,places.googleMapsLinks,places.websiteUri,places.internationalPhoneNumber",
        // "X-Goog-FieldMask: *"
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    return json_decode($response, true);
}


function getPlaceIdFromQuery($query, $apiKey) {
    $textSearchUrl = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=" . urlencode($query) . "&key=$apiKey";

    // $textSearchUrl = "https://places.googleapis.com/v1/places:" . urlencode($query) . "&key=$apiKey";
    $response = curlGet($textSearchUrl);
    // if (!$response) return false;

    $datas = json_decode($response, true);

    $i = 0;
    foreach ($datas['results'] as $data){
        
        // print_r($data);
        echo "<hr>";
            $businessName = $data['name'];
            $location = $data['formatted_address'];
            // if ($count >= $maxLinks) break;
            $socials = getSocialMediaLinks($businessName, $location, $apiKey);
        
          
            // $count++;
            $details[$i]['socials'] = $socials;
            $details[$i]['businessName'] = $businessName;
            $details[$i]['location'] = $location;
            // $details[] = getPlaceDetailsById($placeId, $apiKey);

            $i++;
    }
    

    return $details;
}



function getSocialMediaLinks($businessName, $location, $serpApiKey) {
    $query = urlencode("$businessName $location instagram tiktok");
    $url = "https://serpapi.com/search.json?q=$query&engine=google&api_key=$serpApiKey";

    $response = curlGet($url);
    if (!$response) return [];

    $data = json_decode($response, true);
    $links = [];

    if (!empty($data['organic_results'])) {
        foreach ($data['organic_results'] as $result) {
           

            $link = $result['link'];

            if (strpos($link, 'instagram.com') !== false) {
                $links['instagram'][] = $link;
            } elseif (strpos($link, 'facebook.com') !== false) {
                $links['facebook'][] = $link;
            } elseif (strpos($link, 'twitter.com') !== false || strpos($link, 'x.com') !== false) {
                $links['twitter'][] = $link;
            }
        }
    }

    return $links;
}

// $details = getPlaceDetailsById('ChIJIY7CWli8cEgRlQr852wf0Dw', $googleApiKey);

// print_r($details);

// $businessName = 'The Birmingham Botanical Gardens';
// $location = 'Westbourne Rd, Birmingham B15 3TR, UK';
// $socials = getSocialMediaLinks($businessName, $location, $serp_api_key);

// print_r($socials);

// $links = getPlaceIdFromQuery($placeQuery, $googleApiKey);
// $links = getPlaceData($placeQuery, $googleApiKey);
// print_r($links);
// die;
// if ($links) {

//    foreach ($links as $link) {
//         echo "Business Name: " . htmlspecialchars($link['businessName']) . "<br>";
//         echo "Location: " . htmlspecialchars($link['location']) . "<br>";
//         echo "Social Media Links:<br>";
//         print_r($link['socials']);

//         echo "<hr>";
//     }

// } else {
//     echo "No matching business found for: " . htmlspecialchars($placeQuery);
// }
