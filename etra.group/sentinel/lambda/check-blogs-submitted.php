<?php
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php'; 

use Aws\CloudWatch\CloudWatchClient;

// Modify the article query to check for articles not submitted in the last 24 hours
$article_query = "SELECT COUNT(*) as article_count 
                 FROM articles 
                 WHERE written >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR))";

$article_run = mysql_query($article_query);
$article_count = mysql_fetch_array($article_run)['article_count'];

echo "\nArticle submitted in the last 24 hours: " . $article_count;

 // Initialize CloudWatch client
 $cloudWatchClient = new CloudWatchClient([
    'region' => 'us-east-2',
    'version' => 'latest',
    'credentials' => [
        'key'    => $cloudwatchkey,
        'secret' => $cloudwatchpassword,
    ],
]);

// If article count is 0, it means no articles were submitted in the last 24 hour
try {
    // Send metric for article submission monitoring
  $cloudWatchClient->putMetricData([
    'Namespace' => 'Sentinel/Lambda',
    'MetricData' => [
        [
            'MetricName' => 'DailyArticleSubmissionMonitor',
            'Dimensions' => [
                [
                    'Name' => 'DailyArticleSubmission',
                    'Value' => 'daily-article-check'
                ],
            ],
            'Unit' => 'Count',
            'Value' => $article_count,
        ],
    ],
]);

    error_log('Article submission metric data sent successfully');
} catch (Exception $e) {
    error_log('Error sending article submission metric data: ' . $e->getMessage());
}

echo "\nArticle submission metric data sent to CloudWatch";