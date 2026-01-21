<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/sm-db.php';

$pageArr = ['index.php', 'about-us', 'contact-us', 'buy-instagram-followers', 'buy-instagram-likes', 'buy-instagram-views', 'buy-instagram-comments', 'automatic-instagram-likes',
'uk', 'uk/about-us', 'uk/contact-us', 'uk/buy-instagram-followers', 'uk/buy-instagram-likes', 'uk/buy-instagram-views', 'uk/buy-instagram-comments', 'uk/automatic-instagram-likes' ];

foreach ($pageArr as $page) {
    $url = 'https://superviral.io/' . $page .'/';
    echo $url . '<br>';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (only if needed)
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'); // Mimic a browser request
    
    $tpl = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
    }
    
    curl_close($ch);
    
    // $tpl =  htmlspecialchars($tpl);

    if($page == 'index.php') {
        $page = 'home';
    }

    checkHreflang($tpl, $page);
}

function checkHreflang($tpl, $page) {
    $html = $tpl;
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $links = $dom->getElementsByTagName('link');
    $requiredHreflangs = ['en-us', 'en-gb'];
    $foundHreflangs = [];
    foreach ($links as $link) {
        if ($link->hasAttribute('hreflang') && $link->hasAttribute('href')) {
            $hreflang = $link->getAttribute('hreflang');
            $href = $link->getAttribute('href');
            $foundHreflangs[$hreflang] = $href;
        }
    }
    $missingHreflangs = array_diff($requiredHreflangs, array_keys($foundHreflangs));
    if (empty($missingHreflangs)) {
        // echo "Both required hreflang tags ('en-us' and 'en-gb') are present.\n";
        sendCloudwatchData('Superviral', 'hreflang-'. $page .'-sucess', 'HrefLang', 'hreflang-'. $page .'-sucess-function', 1);
    } else {
        // echo "Missing hreflang tags: " . implode(', ', $missingHreflangs) . "\n";
        foreach ($missingHreflangs as $missingHreflang) {
            sendCloudwatchData('Superviral', $missingHreflang .'-hreflang-'. $page .'-missing', 'HrefLang', $missingHreflang .'-hreflang-'. $page .'-missing-function', 1);
        }
    }
}
