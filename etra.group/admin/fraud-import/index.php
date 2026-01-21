<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$import = addslashes($_POST['fraudImport']);

$now = time();

$checkedVal  = $_POST['chkboxVal'];

$dispNone = 'display:none;';

if (!empty($checkedVal)) {

    $checkedArr = explode(',', $checkedVal);

    $i = 0;
    $uniqueSessions = [];
    while ($i < count($checkedArr)) {
        // Get the value of the "Order ID" column
        $orderId = $checkedArr[$i];

        // Extract the order session
        $orderSession = trim(explode('-', $orderId)[0]);

        // Add to unique sessions if not already present
        if (!in_array($orderSession, $uniqueSessions)) {
            $uniqueSessions[] = $orderSession;
            $checkOsQuery = mysql_query('SELECT * FROM `order_session` WHERE `order_session` = "' . $orderSession . '" ORDER BY id DESC limit 1');
            $dataOS = mysql_fetch_array($checkOsQuery);
            // echo "Order ID: $orderSession :email: {$dataOS['emailaddress']}<br>";
            if (mysql_num_rows($checkOsQuery) > 0) {

                $checkExist = mysql_query("SELECT * FROM `blacklist` WHERE emailaddress = '{$dataOS['emailaddress']}' OR igusername = '{$dataOS['igusername']}'OR ipaddress = '{$dataOS['ipaddress']}'");
                if (mysql_num_rows($checkExist) == 0) {

                    mysql_query("INSERT INTO blacklist SET emailaddress = '{$dataOS['emailaddress']}', igusername = '{$dataOS['igusername']}',ipaddress = '{$dataOS['ipaddress']}', `billingname` = '{$dataOS['payment_billingname_crdi']}', added = '$now', brand = 'sv', `source` = 'admin-fraud-import' ");
                }
            }
        }

        $i++;
    }
    $msg = "Fraud Imported Successfully!!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($import)) {
    if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csvFile']['tmp_name'];
        $fileType = mime_content_type($fileTmpPath);
        $fileName = $_FILES['csvFile']['name'];

        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Validate file type
        if (strtolower($fileExtension) === 'csv') {
            // Open the file and read line by line
            if (($handle = fopen($fileTmpPath, 'r')) !== false) {
                $header = fgetcsv($handle); // Read the first line for column names

                if ($header !== false) {
                    // Define columns to exclude (zero-based index)
                    $excludeColumns = [1, 5, 6, 7, 8, 14, 15, 16, 18, 20]; // Example: Exclude the 2nd and 4th columns

                    $htm = "<table id='transactionTable' border = 2 style='outline:1px solid #000'>";
                    $htm .= "<thead><tr>";
                    foreach ($header as $index => $col) {
                        if (!in_array($index, $excludeColumns)) {
                            $htm .= "<th>" . htmlspecialchars($col) . "</th>";
                        }
                        if (strtolower($col) == 'order id') {
                            $oIdK = $index;
                            $htm .= "<th>Username</th>";
                            $htm .= "<th>Email</th>";
                            $htm .= "<th>Ip address</th>";
                        }
                    }

                    $htm .= "<th style='width: 70px;'>Action</th></tr></thead>";

                    $htm .= "<tbody>";
                    while (($row = fgetcsv($handle)) !== false) {
                        $htm .= "<tr>";
                        foreach ($row as $index => $cell) {
                            if (!in_array($index, $excludeColumns)) {
                                $htm .= "<td>" . htmlspecialchars($cell) . "</td>";
                            }

                            if ($index == $oIdK) {
                                $orderSession = trim(explode('-', $cell)[0]);;
                                $checkOsQuery = mysql_query('SELECT * FROM `order_session` WHERE `order_session` = "' . $orderSession . '" ORDER BY id DESC limit 1');
                                $dataOS = mysql_fetch_array($checkOsQuery);
                                $htm .= "<td><b>" . $dataOS['igusername'] . "</b></td>";
                                $htm .= "<td><b>" . $dataOS['emailaddress'] . "</b></td>";
                                $htm .= "<td><b>" . $dataOS['ipaddress'] . "</b></td>";
                            }
                        }
                        $htm .= "<td><input class='input chkClass' name='chkInp' type='checkbox'></td></tr>";
                    }
                    $htm .= "</tbody>";
                    $htm .= "</table>";
                } else {
                    $msg = "The CSV file is empty.";
                }

                $dispNone = '';
                fclose($handle);
            } else {
                $msg = "Failed to open the uploaded file.";
            }
        } else {
            $msg = "Only CSV files are allowed.";
        }
    } else {
        $msg = "Error uploading the file.";
    }
}

$tpl = str_replace('{msg}', $msg, $tpl);
$tpl = str_replace('{htm}', $htm, $tpl);
$tpl = str_replace('{dispNone}', $dispNone, $tpl);

output($tpl, $options);
