<?php
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .user-section {
            margin-bottom: 40px;
        }

        .capture-area {
            padding: 20px;
            background: white;
        }

        .followers-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 20px;
            justify-items: center;
        }

        .follower-card {
            text-align: center;
            width: 100px;
        }

        .follower-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        .username {
            font-weight: bold;
            font-size: 14px;
            margin-top: 6px;
        }

        .fullname {
            font-size: 12px;
            color: gray;
        }

        .title {
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
            text-align: center;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>

<body>
<script>
function allImagesLoaded(containerSelector, callback) {
    const container = document.querySelector(containerSelector);
    const images = container.querySelectorAll('img');
    let loadedCount = 0;

    if (images.length === 0) {
        callback();
        return;
    }

    images.forEach(img => {
        if (img.complete) {
            loadedCount++;
            if (loadedCount === images.length) callback();
        } else {
            img.onload = img.onerror = () => {
                loadedCount++;
                if (loadedCount === images.length) callback();
            };
        }
    });
}

function downloadImage() {
    const element = document.querySelector('.capture-area');
    html2canvas(element, { useCORS: true }).then(canvas => {
        const link = document.createElement('a');
        link.download = 'followers-grid.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}

window.addEventListener('load', () => {
    allImagesLoaded('.capture-area', downloadImage);
});
</script>

<?php
$q = "SELECT * FROM `orders` WHERE `packagetype`='followers' AND `socialmedia`='ig' AND `curr_engagement` != '' AND `new_engagement` != '' AND curr_engagement != new_engagement AND curr_engagement <= 150 AND `added` >= unix_timestamp(CURRENT_DATE - interval 24 hour) GROUP BY packageid ORDER BY `new_engagement` ASC";
$result = mysql_query($q);

while ($row = mysql_fetch_array($result)) {
    $igusername = $row['igusername'];
    $data = getFollowers($igusername);
    echo "<div class='user-section'>";
    echo "<div class='capture-area'>";
    echo "<div class='title'>Followers of @$igusername</div>";

    if (is_array($data) && count($data) > 0) {
        echo "<div class='followers-grid'>";
        foreach ($data as $follower) {
            $followerUsername = $follower['followerUsername'];
            $followerFullName = $follower['followerFullName'];
            $profilePic = $follower['profilePic'];

            $ch = curl_init($profilePic);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $imgData = curl_exec($ch);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            $base64 = base64_encode($imgData);
            $imgSrc = "data:$contentType;base64,$base64";

            echo "<div class='follower-card'>";
            echo "<img src='$imgSrc'>";
            echo "<div class='username'>$followerUsername</div>";
            echo "<div class='fullname'>$followerFullName</div>";
            echo "</div>";
        }
        echo "</div>"; 
    } else {
        echo "No followers found for @$igusername";
    }

    echo "</div>"; 
    echo "</div>"; 
}

function getFollowers($username)
{
    global $superviralsocialscrapekey;
    $url = 'https://i.supernova-493.workers.dev/api/v3/profile?username=' . $username;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $get = curl_exec($curl);
    $resp = json_decode($get, true);
    $users = $resp['data'];
    $userId = $users['user']['pk_id'];

    $url = 'https://i.supernova-493.workers.dev/api/v3/followers?userId=' . $userId;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $get = curl_exec($curl);
    $get = json_decode($get, true);
    $i = 0;
    $data = [];

    foreach ($get['data']['response']['users'] as $follower) {
        if ($i >= 50) continue;
        $data[] = array(
            'followerId' => $follower['pk'],
            'followerUsername' => $follower['username'],
            'followerFullName' => $follower['full_name'],
            'isprivate' => $follower['is_private'],
            'profilePic' => $follower['profile_pic_url']
        );
        $i++;
    }

    return $data;
}
?>
</body>

</html>
