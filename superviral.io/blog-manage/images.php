<?php
ob_start();

require_once  '../db.php';
global $openAiKey;

$apiKey = $openAiKey;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if (!$id) exit;

    $articleQ = mysql_query("SELECT * FROM `articles` WHERE id = $id LIMIT 1");
    if ($row = mysql_fetch_array($articleQ)) {
        $article_title = $row['title'] ?? 'Guide to Using Instagram Hashtags for More Followers';

        $prompt = 'You are an expert vector designer for a marketing company. Create a single white appropriate stock vector to use in a modern digital banner about social media growth. 
            The title of the guide is “'. $article_title .'”. 
            Strictly follow these conditions:
            - Only return the vector
            - Make it white
            - Do not include the title
            - Do include any text, special characters or numbers
            - Use clean, minimal, tech-inspired design, flat illustrations.
        ';

        $randomNumber = rand(1, 7);
        if (stripos($article_title, 'tiktok') !== false){
            $imagePath = "mask/tt_{$randomNumber}.png";
        }else if (stripos($article_title, 'instagram') !== false){
            $imagePath = "mask/ig_{$randomNumber}.png";
        }else{
            $imagePath = "mask/{$randomNumber}.png";
        }


        $data = [
            'model' => 'gpt-image-1',
            'prompt' => $prompt,
            'image' => new CURLFile($imagePath, 'image/png', $imagePath),
            'quality' => 'high',
            'background' => 'transparent',
            'size' => '1024x1024',
            'n' => 3
        ];

        $ch = curl_init('https://api.openai.com/v1/images/edits');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['data'])) {
            foreach ($result['data'] as $imgData) {
                if (isset($imgData['b64_json'])) {
                    mysql_query("INSERT INTO `articles_vector_image` 
                                SET `blob` = 'data:image/png;base64,{$imgData['b64_json']}',
                                    `article_id` = '$id'");
                }
            }
        } else {
            error_log("Error generating images for ID $id: " . print_r($result, true));
        }
    }

    echo "Done for ID $id";
    exit;
}

$ids = isset($_GET['ids']) ? array_filter(array_map('intval', explode(',', $_GET['ids']))) : [];
$lowestId = min($ids);
$total = count($ids);
$redirectUrl = '/blog-manage/edit.php?id='.$lowestId.'&ids=' . urlencode(implode(',', $ids)) . '&edit=';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Processing Images</title>
    <script>
        let total = <?= $total ?>;
        let completed = 0;

        function markDone(id) {
            document.getElementById('status-' + id).innerText = '✅ Done ID ' + id;
            completed++;
            if (completed === total) {
                window.location.href = <?= json_encode($redirectUrl) ?>;
            }
        }
    </script>
</head>
<body>
    <h2>Processing <?= $total ?> articles images...</h2>
    <div id="progress-area">
        <?php foreach ($ids as $id): ?>
            <div id="status-<?= $id ?>">⏳ Processing article ID <?= $id ?>...</div>
            <iframe 
                src="?id=<?= $id ?>" 
                width="0" 
                height="0" 
                style="border:0;" 
                onload="markDone(<?= $id ?>)">
            </iframe>
        <?php endforeach; ?>
    </div>
</body>
</html>
