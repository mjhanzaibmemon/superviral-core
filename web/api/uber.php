<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
set_time_limit(300); // 5 minutes

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';

echo '<pre>';
$cat_query = mysql_query('SELECT * FROM category WHERE uber_done = 0 limit 1');
while ($cat_data = mysql_fetch_array($cat_query)) {

    $category = $cat_data['name'];

    $loc_query = mysql_query('SELECT * FROM locations');
    while ($loc_data = mysql_fetch_array($loc_query)) {
        $address = $loc_data['area'] . ' ' . $loc_data['town'] . ' ' . $loc_data['postcode'];
        $placeQuery = $category . ' in ' . $address;

        echo "Address: $address - $category<br>";

        $datas = getUberData($category, $address, $foodie_rapid_api_host, $foodie_rapid_api_key);
        if ($datas) {
            // echo $response;
            foreach ($datas['returnvalue']['data'] as $data) {
                // print_r($data);
                $endorsements = [];
                $address = addslashes($data['location']['address']);
                $name = addslashes($data['title']) ?? '';
                $phone = $data['phoneNumber'];
                $city = addslashes($data['location']['city']);
                $country = $data['location']['country'];
                $pincode = $data['location']['postalCode'];
                $latitude = number_format($data['location']['latitude'], 4);
                $longitude = number_format($data['location']['longitude'], 5);
                $website = $data['url'];
                $categories = implode(',', $data['categories']);
                $endorsements_data = $data['menu'];
               foreach ($endorsements_data as $item) {
                    foreach ($item['catalogItems'] as $endorsement) {
                        if (isset($endorsement['endorsement'])) {
                            $endorsements[$endorsement['endorsement']] = true;
                        }
                    }
                }

                $endorsements = array_keys($endorsements); //

                // print_r($endorsements_data);die;
                print_r($endorsements);
                echo '<br>';
                $endorsementString = implode(', ', $endorsements);
                echo "es: ". $endorsementString . '<br>';
                // menu
                $menus = [];
                $i=0;
                foreach ($endorsements_data as $item) {
                    foreach ($item['catalogItems'] as $menu) {
                        $menus[$i]['title'] = $menu['title'];
                        $menus[$i]['itemDescription'] = $menu['itemDescription'];
                        $menus[$i]['price'] = intval($menu['price']);
                        $menus[$i]['priceTagline'] = $menu['priceTagline'];
                        $i++;
                    }
                }

                // print_r($menus);die;
                // debug 
                // echo "Name: $name<br>";
                // echo "Address: $address<br>";
                // echo "Phone: $phone<br>";
                // echo "City: $city<br>";
                // echo "Country: $country<br>";
                // echo "Pincode: $pincode<br>";
                // echo "Latitude: $latitude<br>";
                // echo "Longitude: $longitude<br>";
                // echo "Uber ID: $uber_id<br>";
                // echo "Website: $website<br>";
                // echo "Categories: $categories<br>";
                // // print_r($endorsements) . "<br>";
                // echo "Endorsements: $endorsementString<br>";

                $checkif_exists = mysql_query("SELECT * FROM ext_uber_restaurants WHERE
                                                (`name`= '$name' AND `address` ='$address') OR 
                                                (ROUND(latitude, 4) = $latitude AND ROUND(longitude, 5) = $longitude) LIMIT 1;
                                            ");
                if (mysql_num_rows($checkif_exists) > 0) {
                    $id = mysql_fetch_array($checkif_exists)['id'];
                    // echo "Restaurant already exists: $name<br>";

                    $updateEndorsements = mysql_query("UPDATE ext_uber_restaurants SET 
                        tags = '". addslashes($endorsementString) ."' 
                        WHERE id = $id");

                    $updateEndorsements = mysql_query("UPDATE ext_uber_menu SET 
                    tags = '". addslashes($endorsementString) ."' 
                    WHERE id = $id");
                    continue;
                }


                $insert_restaurant = mysql_query("INSERT INTO ext_uber_restaurants (name, address, phone, city, 
                country, pincode, latitude, longitude, source, links, tags ) 
                VALUES (
                    '" . $name . "',
                    '" . $address . "',
                    '" . $phone . "',
                    '" . $city . "',
                    '" . $country . "',
                    '" . $pincode . "',
                    '" . $latitude . "',
                    '" . $longitude . "',
                    'uber',
                    '" . addslashes($website) . "',
                    '" . addslashes($endorsementString) . "'
                )");

                $result_id = mysql_insert_id();

                // insert menu

                foreach ($menus as $menu) {
                    $menu_title = addslashes($menu['title']);
                    $menu_description = addslashes($menu['itemDescription']);
                    $menu_price = intval($menu['price']);
                    $menu_priceTagline = addslashes($menu['priceTagline']);

                    $check_menu = mysql_query("SELECT * FROM ext_uber_menu WHERE restaurant_id = '$result_id' AND title = '$menu_title' LIMIT 1");
                    if (mysql_num_rows($check_menu) > 0) {
                        // echo "Menu item already exists: $menu_title<br>";
                        continue;
                    }
                    $insert_menu = mysql_query("INSERT INTO ext_uber_menu (restaurant_id, title, description, price, price_tagline, tags) 
                                                VALUES ('$result_id', '$menu_title', '$menu_description', '$menu_price', '$menu_priceTagline', '$endorsementString')");
                }

                $category_list = explode(',', $categories);

                foreach($category_list as $category)
                {
                    $category = trim(addslashes($category));
                    $check_category = mysql_query("SELECT * FROM uber_category WHERE name = '$category' LIMIT 1");
                    if (mysql_num_rows($check_category) == 0) {
                        $insert_category = mysql_query("INSERT INTO uber_category (name) VALUES ('$category')");
                    } else {
                        // echo "Category already exists: $category<br>";
                        continue;
                    }
                }

                $endorsements = explode(',', $endorsementString);

                foreach($endorsements as $endorsement)
                {
                    $endorsement = trim(addslashes($endorsement));
                    $check_endorsement = mysql_query("SELECT * FROM uber_tags WHERE tag = '$endorsement' LIMIT 1");
                    if (mysql_num_rows($check_endorsement) == 0) {
                        $insert_endorsement = mysql_query("INSERT INTO uber_tags (tag) VALUES ('$endorsement')");
                    } else {
                       continue; 
                    }
                }
                
                echo '<hr>';

                unset($menus);
                unset($endorsements);
                unset($category_list);
                unset($insert_restaurant);
                unset($insert_menu);
                unset($insert_category);
                unset($insert_endorsement);
                unset($endorsementString);
            }

        } else {
            echo "No matching business found for: " . htmlspecialchars($placeQuery) . "<br>";
        }
    }
    mysql_query("UPDATE category SET uber_done = 1 WHERE id = " . $cat_data['id']);
    die;
}

function getUberData($category, $address, $foodie_rapid_api_host, $foodie_rapid_api_key)
{

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://uber-eats-scraper-api.p.rapidapi.com/api/job",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 50,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'scraper' => [
                'maxRows' => 10,
                'query' => $category,
                'address' => $address,
                'locale' => 'en-GB',
                'page' => 1
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-rapidapi-host: $foodie_rapid_api_host",
            "x-rapidapi-key: $foodie_rapid_api_key"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);



    $decode_response = json_decode($response, true);

    // print_r($decode_response);

    curl_close($curl);

    return $decode_response;
}
