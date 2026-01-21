<?php
require_once '../sm-db.php';


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');


require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/diffChecker/lib/Diff.php';
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/diffChecker/lib/Diff/Renderer/Html/SideBySide.php';

// live connection DB:
$dbServer1 = '';
$dbUser1 = "etra_devteam";
$dbPass1 = 'sd2"93*3\'a3';
$dbName1 = "etra_superviral";

$conn1 = mysql_connect($dbServer1, $dbUser1, $dbPass1) or die(mysql_error());
mysql_select_db($dbName1, $conn1);

die;

echo "======================================START========================================================<br><br>";

$QueryRun = mysql_query("SELECT * FROM content WHERE name='test' ORDER BY ID DESC LIMIT 1;");

while ($testData = mysql_fetch_array($QueryRun)) {
    
    $testContent = $testData['content'];
    $testPage = $testData['page'];
    $testName = $testData['name'];
    $testCountry = $testData['country'];

    $liveQueryRun = mysqli_query($conn1,"SELECT * FROM content WHERE `country` = '$testCountry' AND `name` = '$testName' AND `page` = '$testPage' ORDER BY ID DESC LIMIT 1;");
    $liveData = mysqli_fetch_assoc($liveQueryRun);
    print_r($testData);
    print_r($liveData);
    die;
    $liveContent = $liveData['content'];

    $a = explode("\n", $c1);

    $b = explode("\n", $c2);

    $options = array(

        'ignoreWhitespace' => true,

        //   'ignoreCase' => true,

    );

    $diff = new Diff($a, $b, $options);

    $renderer = new Diff_Renderer_Html_SideBySide;

    $differenceData = $diff->Render($renderer);

    if(!empty($differenceData)){

        echo $differenceData .'<br><br><br>';

        // mysql_query("UPDATE content SET difference = 1 WHERE `country` = '$testCountry' AND `name` = '$testName' AND `page` = '$testPage' LIMIT 1;");

    }

    // echo $differenceData;
}

echo "======================================END========================================================<br><br>";
