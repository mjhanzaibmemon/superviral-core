<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$host = $_SERVER['HTTP_HOST']; 
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

$tpl = file_get_contents('uploadarticles.html');

function readDocxParagraphsAdvanced($tmpFile) {
    $zip = new ZipArchive;
    $paragraphs = [];

    if ($zip->open($tmpFile) === TRUE) {
        $xmlData = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xmlData) {
            $xml = simplexml_load_string($xmlData);
            $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            foreach ($xml->xpath('//w:p') as $paragraph) {
                $texts = [];
                foreach ($paragraph->xpath('.//w:t') as $node) {
                    $texts[] = (string) $node;
                }
                $text = trim(implode('', $texts));
                if ($text === '') continue; // skip empty

                $style = (string) ($paragraph->xpath('.//w:pPr/w:pStyle/@w:val')[0] ?? '');

                $isHeading = false;
                $headingLevel = null;

                // Check for Heading1 to Heading6
                if (preg_match('/Heading([1-6])/', $style, $match)) {
                    $isHeading = true;
                    $headingLevel = (int)$match[1];
                }

                // Check for other elements like lists
                $isListItem = false;
                $listType = null;

                if ($paragraph->xpath('.//w:numPr')) {
                    $isListItem = true;
                    $listType = (string)($paragraph->xpath('.//w:numPr/w:ilvl')[0] ?? ''); // Level of list item
                }

                $paragraphs[] = [
                    'text' => $text,
                    'isHeading' => $isHeading,
                    'headingLevel' => $headingLevel,
                    'isListItem' => $isListItem,
                    'listType' => $listType,
                ];
            }
        }
    }
    return $paragraphs;
}

function extractStructuredDocDataAdvanced($paragraphs) {
    $data = [];
    $summaries = [];

    $paragraphs = array_values($paragraphs);

    // Title: first Heading1
    foreach ($paragraphs as $index => $para) {
        if ($para['isHeading'] && $para['headingLevel'] === 1) {
            $data['title'] = $para['text'];
            $titleIndex = $index;
            break;
        }
    }
    if (!isset($data['title'])) {
        $data['title'] = $paragraphs[0]['text'] ?? 'Title not found';
        $titleIndex = 0;
    }

    // Summaries: up to 3 Heading2s after title
    $heading2Count = 0;
    for ($i = $titleIndex + 1; $i < count($paragraphs); $i++) {
        if ($paragraphs[$i]['isHeading'] && $paragraphs[$i]['headingLevel'] === 2) {
            $summaries[] = $paragraphs[$i]['text'];
            $heading2Count++;
            if ($heading2Count >= 3) break;
        }
    }
    $data['summaries'] = $summaries;

    // Short description and indexes to exclude
    $descParagraphs = [];
    $excludeIndexes = [$titleIndex];
    $descWordCount = 0;
    for ($i = $titleIndex + 1; $i < count($paragraphs); $i++) {
        if (!$paragraphs[$i]['isHeading']) {
            $descParagraphs[] = $paragraphs[$i]['text'];
            $excludeIndexes[] = $i;
            $descWordCount += str_word_count($paragraphs[$i]['text']);
            if ($descWordCount > 40) break;
        } else {
            break;
        }
    }
    $data['description'] = trim(implode(' ', $descParagraphs));

    // Article content in order (excluding title + description)
    $articleParts = [];
    $inList = false;

    foreach ($paragraphs as $i => $para) {
        if (in_array($i, $excludeIndexes)) continue;

        if ($para['isHeading']) {
            if ($inList) {
                $articleParts[] = '</ul>';
                $inList = false;
            }

            $level = min(max($para['headingLevel'], 2), 3);
            $articleParts[] = "<h{$level}>{$para['text']}</h{$level}>";

        } elseif ($para['isListItem']) {
            if (!$inList) {
                $articleParts[] = "<ul>";
                $inList = true;
            }
            $articleParts[] = "<li>{$para['text']}</li>";

        } else {
            if ($inList) {
                $articleParts[] = "</ul>";
                $inList = false;
            }
            $articleParts[] = "<p>{$para['text']}</p>";
        }
    }

    if ($inList) {
        $articleParts[] = '</ul>';
    }

    $data['article'] = implode("\n", $articleParts);
    return $data;
}

function getUploadDates($startDate, $gap, $fileCount) {
    $dates = [];
    $currentDate = DateTime::createFromFormat('Y-m-d', $startDate, new DateTimeZone('UTC'));

    if (!$currentDate) {
        throw new Exception("Invalid start date format: $startDate. Expected format: YYYY-MM-DD");
    }

    for ($i = 0; $i < $fileCount; $i++) {
        // Skip weekends
        while (in_array($currentDate->format('N'), [6, 7])) {
            $currentDate->modify('+1 day');
        }

        $dates[] = $currentDate->format('Y-m-d'); 

        $daysAdded = 0;
        while ($daysAdded < ($gap+1)) {
            $currentDate->modify('+1 day');
            if (!in_array($currentDate->format('N'), [6, 7])) {
                $daysAdded++;
            }
        }
    }

    return $dates;
}

$startDate = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d');
$gap = 2; 


// Track current file index for assigning dates
$dateIndex = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['article_files'])) {
    $messages = [];
    $insertedIds = [];
    
    $fileCount = count(array_filter($_FILES['article_files']['tmp_name']));

    $uploadDates = getUploadDates($startDate, $gap, $fileCount);
    
    foreach ($_FILES['article_files']['name'] as $index => $name) {
        $tmpName = $_FILES['article_files']['tmp_name'][$index];
        $relativePath = $_FILES['article_files']['name'][$index];
        $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
        $mime = $_FILES['article_files']['type'][$index];

        if($tmpName === '') {
            continue;
        }
        // Validate DOCX file
        if (strtolower($ext) !== 'docx' || !in_array($mime, [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/octet-stream'
        ])) {
            $messages[] = "❌ Skipped invalid file: $relativePath";
            continue;
        }

        // Process the file
        $paragraphs = readDocxParagraphsAdvanced($tmpName);
        if ($paragraphs) {
            $data = extractStructuredDocDataAdvanced($paragraphs);

            ob_start();
            // echo "<h3>📄 <code>{$relativePath}</code></h3>";

            $meta_title = trim($paragraphs[0]['text'] ?? '');
            $meta_title = str_replace(['‘', '’'], "'", $meta_title);
            $meta_title = str_replace('Meta Title: ', '', $meta_title);
            $short_desc = trim($paragraphs[1]['text'] ?? '');
            $short_desc = str_replace(['‘', '’'], "'", $short_desc);
            $short_desc = str_replace('Meta Description: ', '', $short_desc);
            $full_url_line = trim($paragraphs[2]['text'] ?? '');
            $url_slug = '';         

            $paragraphs = array_slice($paragraphs, 3);

            $data = extractStructuredDocDataAdvanced($paragraphs);
            $title = $data['title'];
            $title = str_replace(['‘', '’'], "'", $title);  
            $title = str_replace('Meta Title: ', '', $title);

            $meta_title = $meta_title ?? $title;
            $short_desc = $short_desc ?? $data['description'];

            if (preg_match('/\/blog\/([^ ]+)/', $full_url_line, $matches)) {
                $url_slug = $matches[1];
            } else {
                $url_slug = create_seo_link($title);
            }           

            $url = cleanUrlString(strtolower($url_slug));      

            // echo $url . '<br>';
            // echo $short_desc . '<br>';
            // echo $meta_title . '<br>';
            // echo $title . '<br>';
            // die;
            // $summary1 = $data['summaries'][0] ?? '';
            // $summary2 = $data['summaries'][1] ?? '';
            // $summary3 = $data['summaries'][2] ?? '';

            $summary1 = "";
            $summary2 = "";
            $summary3 = "";

            
            // echo "<pre>Raw article content:\n" . htmlspecialchars($article) . "</pre>"; // debug

            $article = ($data['article']);
            $article = str_replace(['‘', '’'], "'", $article);
            $article = str_replace('—', ' ', $article);

            $_POST['article'] = preg_replace('/\s*style\s*=\s*"[^"]*"/i', '', $_POST['article']);

            date_default_timezone_set('UTC');
            $writtenDate = $uploadDates[$dateIndex] ?? date('Y-m-d');
            $written = strtotime($writtenDate);
            $dateIndex++;

            $q = mysql_query("INSERT INTO `articles` 
                                SET 
                                `country` = 'us',
	                            `title` = '". addslashes($title) ."',
	                            `meta_title` = '". addslashes($meta_title) ."',
                                `h1` = '". addslashes($title) ."',
	                            `shortdesc` = '" .addslashes($short_desc) ."',
	                            `url` = '$url',
                                `published`='1',
	                            `summary1` = '". addslashes($summary1) ."',
	                            `summary2` = '". addslashes($summary2) ."',
	                            `summary3` = '". addslashes($summary3) ."',
	                            `article` = '" . addslashes($article) . "', 
	                            `author` = 'The Superviral Team', 
	                            `author_description` = '',
                                `brand`='sv', 
                                `added_by`='" .addslashes($_SESSION['first_name']). "',`written` = '$written', superadmin_approve = 0,
                                `article_type`='private'");
            if(!$q) 
                $messages[] = "<pre style='color: red;'>MySQL Error: " . mysqli_error($conn) . "</pre>";
            else
                $messages[] = "<div style='color: green;'>✅ Upload successful: <b>$relativePath</b></div>";
                
            $insertedIds[] = mysql_insert_id();
            $messages[] = ob_get_clean();
        } else {
            $messages[] = "❌ Failed to parse DOCX: $relativePath";
        }
    }

    $idList = implode(',', $insertedIds);
    header("Location: /admin/articles/new/images/openai.php?ids=" . urlencode($idList));
    exit;
    $message = implode("\n", $messages);
}


$tpl = str_replace('{message}', $message, $tpl);
output($tpl, $options);

function create_seo_link($text)
{
    $letters = array('–', '—', '\'', '\'', '\'', '«', '»', '&', '÷', '>', '<', '/');
    $nospace = array(':', ';', ',', '"', '"', '"', '$', '£', '|', '(', ')', "'", '’');

    $text = str_replace($letters, " ", $text);
    $text = str_replace($nospace, "", $text);
    $text = str_replace("&", "and", $text);
    $text = str_replace("?", "", $text);
    $text = strtolower(str_replace(" ", "-", $text));

    return cleanUrlString($text);
}

function cleanUrlString($string)
{
    $pattern = '/[^a-zA-Z0-9-_.~]+/';
    return preg_replace($pattern, '', $string);
}
