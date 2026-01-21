<?php

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';
$pineconeApiKey = $pinecone_key;
$input = json_decode(file_get_contents("php://input"), true);

$type = $input['type'];

switch ($type) {
    case 'get_posts':
        $lat = $input['latitude'];
        $long = $input['longitude'];
        $likesPostIds = isset($input['postsLiked']) ? $input['postsLiked'] : [];
        $sharePostIds = isset($input['postsShared']) ? $input['postsShared'] : [];

        $apiKey = $google_place_key;

        $userLat = $lat;
        $userLon = $long;

        $posts = [];
        // print_r($input);die;

        if (empty($likesPostIds)) {

            $query = "SELECT 
                            sp.id,
                            video_url,
                            shortcode,
                            sp.username,
                            sp.media_type,
                            user_share_count,
                            user_like_count,
                            profile_pic_url,
                            r.id AS restaurant_id,
                            r.latitude,
                            r.longitude,
                            sp.post_embed,
                            (6371 * acos(
                                cos(radians($userLat)) *
                                cos(radians(r.latitude)) *
                                cos(radians(r.longitude) - radians($userLon)) +
                                sin(radians($userLat)) *
                                sin(radians(r.latitude))
                            )) AS distance_km
                        FROM socialmedia_posts sp
                        JOIN restaurants r ON r.id = sp.restaurant_id
                        WHERE sp.post_embed IS NOT NULL
                        HAVING distance_km <= 250
                        ORDER BY rand() ASC
                        LIMIT 5";
                    // echo $query;die;    
            $result = mysql_query($query);

            if (mysql_num_rows($result) > 0) {
                while ($data = mysql_fetch_array($result)) {

                    if (!empty($data['latitude']) && !empty($data['longitude'])) {
                        $restLat = $data['latitude'];
                        $restLon = $data['longitude'];
                    }

                    // get map data
                    $resultMap = getDriveDistanceTime($userLat, $userLon, $restLat, $restLon, $apiKey);

                    if ($resultMap['status'] == 'OK') {
                        // Process the result
                        $distance = $resultMap['rows'][0]['elements'][0]['distance']['text'];
                        $duration = $resultMap['rows'][0]['elements'][0]['duration']['text'];

                        $drive = $duration . ' drive';
                    } else {
                        $drive = 'N/A';
                    }

                    $postItem = [
                        'id'       => $data['id'],
                        'visual'   => $data['video_url'],
                        'type'     => $data['media_type'],
                        'shortcode' => $data['shortcode'],
                        'username' => $data['username'],
                        'share_post_count' => $data['user_share_count'],
                        'like_post_count' => $data['user_like_count'],
                        'profile_pic_url' => $data['profile_pic_url'],
                        'category' => 'For You',
                        'drive' => $drive
                    ];


                    $posts[] = $postItem;
                }
            }
        }else{
            //fetch from pinecone

            // get first id from likesPostIds

            $firstId = $likesPostIds[0];
            $stmt = mysql_query("SELECT * FROM socialmedia_posts WHERE id = '$firstId' LIMIT 1");
            $data = mysql_fetch_array($stmt);
            $embedString = $data['post_embed'];
            $denseEmbed = array_map('floatval', explode(',', $embedString));
            // echo $denseEmbed;die;
            // echo '<pre>';
            $result = fetch_pinecone($denseEmbed, $pineconeApiKey, 'posts', 50);
            $data = $result['matches'];
            foreach ($data as $item) {
                $id = $item['id'];
                $metadata = $item['metadata'];
                $post_id =  $metadata['post_id'] ?? '';
                // $post_url =  $metadata['post_url'] ?? '';
                // $media_type =  $metadata['media_type'] ?? '';
                // $location =  $metadata['location'] ?? '';
                // $video_length =  floatval($metadata['video_length']) ?? 0;
                // $posted_at =  $metadata['posted_at'] ?? '';
                // $latitude =  $metadata['latitude'] ?? '';
                // $longitude =  $metadata['longitude'] ?? ''; 

                $posts_ids[] = $post_id;
                


                // print_r($metadata);
            }
            
            $posts_ids = '(' . implode(',', $posts_ids) . ')';
            $query = "SELECT 
                            sp.id,
                            sp.video_url,
                            sp.shortcode,
                            sp.username,
                            sp.media_type,
                            sp.user_share_count,
                            sp.user_like_count,
                            sp.profile_pic_url,
                            r.id AS restaurant_id,
                            r.latitude,
                            r.longitude,
                            sp.post_embed,
                            (6371 * acos(
                                cos(radians($userLat)) *
                                cos(radians(r.latitude)) *
                                cos(radians(r.longitude) - radians($userLon)) +
                                sin(radians($userLat)) *
                                sin(radians(r.latitude))
                            )) AS distance_km
                        FROM algo_post_finetune af
                        JOIN ext_socialmedia_posts es ON af.post_id = es.id
                        JOIN socialmedia_posts sp ON sp.shortcode = es.shortcode
                        JOIN restaurants r ON r.id = sp.restaurant_id
                        WHERE es.id IN {$posts_ids}
                        HAVING distance_km <= 250
                        ORDER BY distance_km ASC
                        LIMIT 5
                        ";

                    //   echo $query;die;
            $result = mysql_query($query);

            if (mysql_num_rows($result) > 0) {
                while ($data = mysql_fetch_array($result)) {

                     if (!empty($data['latitude']) && !empty($data['longitude'])) {
                        $restLat = $data['latitude'];
                        $restLon = $data['longitude'];
                    }

                    // get map data
                    $resultMap = getDriveDistanceTime($userLat, $userLon, $restLat, $restLon, $apiKey);

                    if ($resultMap['status'] == 'OK') {
                        // Process the result
                        $distance = $resultMap['rows'][0]['elements'][0]['distance']['text'];
                        $duration = $resultMap['rows'][0]['elements'][0]['duration']['text'];

                        $drive = $duration . ' drive';
                    } else {
                        $drive = 'N/A';
                    }

                    $postItem = [
                        'id'       => $data['id'],
                        'visual'   => $data['video_url'],
                        'type'     => $data['media_type'],
                        'shortcode' => $data['shortcode'],
                        'username' => $data['username'],
                        'share_post_count' => $data['user_share_count'],
                        'like_post_count' => $data['user_like_count'],
                        'profile_pic_url' => $data['profile_pic_url'],
                        'category' => 'For You',
                        'drive' => $drive
                    ];

                    $posts[] = $postItem;
                }
            }

            // die;
        }
       
        if (!empty($posts)) {
            echo json_encode([
                'status' => 'success',
                'data' => json_encode($posts)
            ]);
        }
         else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No posts found'
            ]);
        }

        break;

    case 'share_post_count':

        $id = $input['id'];
        $query = "UPDATE socialmedia_posts SET user_share_count = user_share_count +1 WHERE id = $id LIMIT 1";
        $result = mysql_query($query);

        if ($result) {
           
            echo json_encode([
                'status' => 'success',
                'data' => 'Post shared successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server Error'
            ]);
        }

    break;    
    case 'like_post_count':

        $id = $input['id'];
        $query = "UPDATE socialmedia_posts SET user_like_count = user_like_count +1 WHERE id = $id LIMIT 1";
        $result = mysql_query($query);

        if ($result) {
           
            echo json_encode([
                'status' => 'success',
                'message' => 'Post liked successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server Error'
            ]);
        }

    break;    

    case 'dislike_post_count':

        $id = $input['id'];
        $query = "UPDATE socialmedia_posts SET user_like_count = user_like_count - 1 WHERE id = $id AND user_like_count > 0 LIMIT 1";
        $result = mysql_query($query);

        if ($result) {
           
            echo json_encode([
                'status' => 'success',
                'message' => 'Post disliked successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server Error'
            ]);
        }

    break;    

    case 'get_post':

        $id = $input['id'];
        $query = "SELECT id,video_url,shortcode,username,media_type,user_share_count,user_like_count,profile_pic_url  FROM socialmedia_posts WHERE id = $id LIMIT 1";
        $result = mysql_query($query);

        if (mysql_num_rows($result) > 0) {
            $data = mysql_fetch_array($result);

            $postItem = [
                'id'       => $data['id'],
                'visual'   => $data['video_url'],
                'type'     => $data['media_type'],
                'shortcode' => $data['shortcode'],
                'username' => $data['username'],
                'share_post_count' => $data['user_share_count'],
                'like_post_count' => $data['user_like_count'],
                'profile_pic_url' => $data['profile_pic_url'],
                'category' => 'For You' 
            ];
            
            echo json_encode([
                'status' => 'success',
                'data' => json_encode($postItem)
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No posts found'
            ]);
        }


    break;    
}


function getDriveDistanceTime($originLat, $originLon, $destLat, $destLon, $apiKey) {
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$originLat},{$originLon}&destinations={$destLat},{$destLon}&mode=driving&key={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return response as string
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL check if needed (optional)

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    return $data;
}

function fetch_pinecone($embedding, $apiKey, $namespace = 'posts', $topK = 5)
{
   $pineconeUrl = "https://posts-mqpevh9.svc.aped-4627-b74a.pinecone.io/query";

    $postData = json_encode([
        'vector' => $embedding,
        'topK' => $topK,
        'includeValues' => false,
        'includeMetadata' => true,
        'namespace' => $namespace
    ]);

    $ch = curl_init($pineconeUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Api-Key: ' . $apiKey,
        'X-Pinecone-API-Version: 2025-04'
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
        return null;
    } else {
        curl_close($ch);
        return json_decode($response, true);
    }

}