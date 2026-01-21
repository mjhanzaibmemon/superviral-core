<?php
$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}

require_once '../sm-db.php';
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

$s3 = new S3($amazonLoggingKey, $amazonLoggingPass);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image'], $_POST['filename'])) {
    $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $_POST['image']);
    $imageData = base64_decode($base64);

    $bucket = 'etra-live-japqc'; // Replace with your bucket name

    
    $list = $s3->getBucket($bucket);
    if ($list === false) {
        echo "Could not access bucket: $bucket <hr>";
    }else{
        echo "bucket found: $bucket <hr>";
    }

    $filename = basename($_POST['filename']);
    $key = 'media/'. $filename;
    // echo $key . "<br>";
    
    $upload = S3::putObject($imageData, $bucket, $key, S3::ACL_PUBLIC_READ);
    var_dump($upload);
    echo $upload ? "Uploaded: $key" : "Upload failed";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Followers Snapshot</title>
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

        iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }

        form.upload-form {
            display: none;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>

<?php if (!isset($_GET['frame'])): ?>
    <!-- Outer loader -->
    <h2>Followers Snapshot (Rendered in Iframe)</h2>
    <iframe src="?frame=1"></iframe>
<?php else: ?>
    <form class="upload-form" method="POST">
        <input type="hidden" name="image" id="imageData">
        <input type="hidden" name="filename" id="filenameField">
    </form>

    <script>
        function allImagesLoaded(container, callback) {
            const images = container.querySelectorAll('img');
            let loaded = 0;
            if (images.length === 0) return callback();

            images.forEach(img => {
                if (img.complete) {
                    loaded++;
                    if (loaded === images.length) callback();
                } else {
                    img.onload = img.onerror = () => {
                        loaded++;
                        if (loaded === images.length) callback();
                    };
                }
            });
        }

        function submitImage(imageData, filename) {
            document.getElementById('imageData').value = imageData;
            document.getElementById('filenameField').value = filename;
            document.querySelector('.upload-form').submit();
        }

        function captureAndUpload() {
    const sections = document.querySelectorAll('.capture-area');
    let i = 0;

    function next() {
        if (i >= sections.length) return;

        const section = sections[i];
        const jap = section.getAttribute('data-jap') || 'unknown';

        allImagesLoaded(section, () => {
            html2canvas(section, { useCORS: true }).then(canvas => {
                const base64Image = canvas.toDataURL('image/png');
                document.getElementById('imageData').value = base64Image;
                document.getElementById('filenameField').value = `followers_${jap}.png`;

                const form = document.querySelector('.upload-form');
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    console.log(xhr.responseText);
                    i++;
                    next(); // move to next after current is done
                };

                const params = new URLSearchParams(new FormData(form)).toString();
                xhr.send(params);
            });
        });
    }

    next();
}


        window.addEventListener('load', captureAndUpload);
    </script>

    <?php
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
        curl_setopt($curl, CURLOPT_URL, $url);
        $get = curl_exec($curl);
        curl_close($curl);
        $get = json_decode($get, true);

        $i = 0;
        $data = [];

        foreach ($get['data']['response']['users'] as $follower) {
            if ($i >= 50) continue;
            $data[] = [
                'followerId' => $follower['pk'],
                'followerUsername' => $follower['username'],
                'followerFullName' => $follower['full_name'],
                'isprivate' => $follower['is_private'],
                'profilePic' => $follower['profile_pic_url']
            ];
            $i++;
        }

        return $data;
    }

    $q = "SELECT id, `jap1` as jap FROM `packages` GROUP BY `jap1` ORDER BY id DESC";
    $result = mysql_query($q);

    while ($dataP = mysql_fetch_array($result)) {
        $jap = htmlspecialchars($dataP['jap']);
        $q2 = "SELECT * FROM `orders` WHERE packageid = '{$dataP['id']}' ORDER BY id DESC LIMIT 1";
        $result2 = mysql_query($q2);

        while ($row = mysql_fetch_array($result2)) {
            $igusername = $row['igusername'];
            if (empty($igusername)) continue;

            $data = getFollowers($igusername);

            echo "<div class='user-section'>";
            echo "<div class='capture-area' data-jap='" . $jap . "'>";
            echo "<div class='title'>Followers of @$igusername</div>";

            if (is_array($data) && count($data) > 0) {
                echo "<div class='followers-grid'>";
                foreach ($data as $follower) {
                    $username = htmlspecialchars($follower['followerUsername']);
                    $fullname = htmlspecialchars($follower['followerFullName']);
                    $picUrl = $follower['profilePic'];

                    $ch = curl_init($picUrl);
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
                    echo "<div class='username'>$username</div>";
                    echo "<div class='fullname'>$fullname</div>";
                    echo "</div>";
                }
                echo "</div>";
            } else {
                echo "<div>No followers found for @$igusername</div>";
            }

            echo "</div>";
            echo "</div>";
        }

        echo "<hr><br>";
    }
    ?>
<?php endif; ?>
</body>
</html>
