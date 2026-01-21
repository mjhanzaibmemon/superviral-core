<?php

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$url = 'https://en.wikipedia.org/wiki/B_postcode_area';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$html = curl_exec($ch);
if (curl_errno($ch)) {
    die('cURL error: ' . curl_error($ch));
}
curl_close($ch);

if (empty($html)) {
    die("Failed to fetch HTML content.\n");
}

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);

$xpath = new DOMXPath($dom);
$tables = $xpath->query('//table[contains(@class, "wikitable")]');

foreach ($tables as $tableIndex => $table) {
    echo "Table " . ($tableIndex + 1) . ":<br>";

    $rows = $table->getElementsByTagName('tr');

    foreach ($rows as $i => $row) {
        if ($i === 0) continue; // Skip header row

        $cells = [];
        foreach ($row->childNodes as $cell) {
            if ($cell->nodeName === 'th' || $cell->nodeName === 'td') {
                $cells[] = trim($cell->textContent);
            }
        }

        if (count($cells) < 3) continue;

        $postcode = addslashes($cells[0]);
        $town = addslashes($cells[1]);
        $areas = explode(',', $cells[2]);

        foreach ($areas as $area) {
            $area = trim(addslashes($area));
            
            $checkStmt = mysql_query("SELECT * FROM locations WHERE postcode = '$postcode' AND area = '$area' AND town = '$town' limit 1");
            if(mysql_num_rows($checkStmt) > 0) {
                echo "Duplicate entry found for postcode: $postcode, area: $area, town: $town. Skipping insert.<br>";
                continue; // Skip if duplicate found
            }

            // Insert into DB
            $stmt = mysql_query("INSERT INTO locations (postcode, town, area) VALUES ('$postcode', '$town', '$area')");
            if (!$stmt) {
                die("Error inserting data:");
            }
        }
    }
    echo "Data inserted successfully.\n";
    echo "<br>";
}

