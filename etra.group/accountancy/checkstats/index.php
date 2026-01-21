<?php

require_once '../../sm-db.php';
session_start();

session_start();


if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {

    // success
    $email  = $_SESSION['user_email'];
} else {


    header('location: /accountancy/?type=login');
}


$from = addslashes($_POST['from']);
$to = addslashes($_POST['to']);

$query = "SELECT (SUM(price)/100) AS total_price, 
                        `billing_country`, 
                        COUNT(id) AS total_order,
                        (   SELECT (SUM(price) / 100) 
                            FROM 
                                orders 
                            WHERE  
                                added 
                            BETWEEN 
                                UNIX_TIMESTAMP('$from 00:00:00') 
                            AND 
                                UNIX_TIMESTAMP('$to 23:59:59') 
                        ) AS grand_total 
                        FROM
                            orders 
                        WHERE  
                            added 
                        BETWEEN 
                            UNIX_TIMESTAMP('$from 00:00:00') 
                        AND 
                            UNIX_TIMESTAMP('$to 23:59:59') 
                        GROUP BY billing_country
                        ORDER BY total_order DESC;
";

$runQuery = mysql_query($query);

$tblHtm = "<tr><td colspan = 3>Pleasae select date range.</td></tr>";
$exportHtm = "";
if ($_POST['submit'] == "Check") {
    if (mysql_num_rows($runQuery) > 0) {
        $tblHtm = "";
        while ($data = mysql_fetch_array($runQuery)) {

            $prcntgCvrd =  ($data['total_price'] / $data['grand_total']) * 100;

            $tblHtm .= ' <tr>
                            <td class="td">' . $data['billing_country'] . '</td>
                            <td class="td">' . $data['total_price'] . '</td>
                            <td class="td">' . $data['total_order'] . '</td>
                            <td class="td">' . number_format($prcntgCvrd, 2) . '%</td>
                        </tr>';
        }
        $exportHtm = '
        <form action="" method="post">
                <span style="display:inline-block;margin-right:10px;">Export to csv:</span>
                <input type="date" name="from" style="display:none;" value="' . $from . '">
                <input type="date" name="to" style="display:none;" value="' . $to . '">
                <input type="submit" name="submit" value="Export" style="border-radius: 5px;padding: 10px;background:white;width: 100px;">
    
        </form>
        <br>
        <form action="" method="post">
                <span style="display:inline-block;margin-right:10px;">Export to xml:</span>
                <input type="date" name="from" style="display:none;" value="' . $from . '">
                <input type="date" name="to" style="display:none;" value="' . $to . '">
                <input type="submit" name="submit" value="ExportXML" style="border-radius: 5px;padding: 10px;background:white;width: 100px;">
    
        </form>';
    } else {
        $tblHtm = '<tr><td colspan = 3>No data.</td></tr>';
    }
}


if ($_POST['submit'] == 'Export') {
    // Create a CSV file and set headers
    $filename = $from . '_' . $to . time();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '.csv"');

    // Open output stream for writing
    $output = fopen('php://output', 'w');

    // Write CSV header row
    fputcsv($output, array('From:', $from, 'To:', $to));

    fputcsv($output, array('Country', 'Amount', 'Total Orders', '% Amount Covered'));



    while ($data = mysql_fetch_array($runQuery)) {
        $prcntgCvrd = ($data['total_price'] / $data['grand_total']) * 100;

        $total_order += $data['total_order'];
        $total_prcntg += $prcntgCvrd;
        $total_amount += $data['total_price'];

        $row = [
            $data['billing_country'],
            $data['total_price'],
            $data['total_order'],
            number_format($prcntgCvrd, 2) . '%'
        ];

        fputcsv($output, $row);
    }
    fputcsv($output, array('Total:', $total_amount, $total_order, $total_prcntg . '%'));

    fclose($output);
    // Ensure all output is flushed and completed
    ob_flush();
    flush();

    die;
}

if ($_POST['submit'] == 'ExportXML') {
    // Set headers for XML file download
    $filename = $from . '_' . $to . time();
    header('Content-Type: text/xml');
    header('Content-Disposition: attachment;filename="' . $filename . '.xml"');

    // Create XML structure
    $xml = new SimpleXMLElement('<root/>');

    // Add 'From' and 'To' elements
    $xml->addChild('From', $from);
    $xml->addChild('To', $to);

    // Create 'Orders' container
    $orders = $xml->addChild('Orders');

    $total_order = 0;
    $total_prcntg = 0;
    $total_amount = 0;

    // Loop through the query result to add data to XML
    while ($data = mysql_fetch_array($runQuery)) {
        $prcntgCvrd = ($data['total_price'] / $data['grand_total']) * 100;

        $total_order += $data['total_order'];
        $total_prcntg += $prcntgCvrd;
        $total_amount += $data['total_price'];

        // Create 'Order' node for each record
        $order = $orders->addChild('Order');
        $order->addChild('Country', $data['billing_country']);
        $order->addChild('Amount', $data['total_price']);
        $order->addChild('TotalOrders', $data['total_order']);
        $order->addChild('AmountCoveredPercentage', number_format($prcntgCvrd, 2) . '%');
    }

    // Add totals to the XML
    $totals = $xml->addChild('Totals');
    $totals->addChild('TotalAmount', $total_amount);
    $totals->addChild('TotalOrders', $total_order);
    $totals->addChild('TotalPercentageCovered', $total_prcntg . '%');

    // Print the XML
    print($xml->asXML());

    // Ensure all output is flushed and completed
    ob_flush();
    flush();
    die;
}


if ($_GET['action'] == 'logout') {
    session_destroy();
    header('location: /accountancy/?type=login');
}


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Stats</title>

    <style>
        .th {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .td {
            border: 1px solid #ddd;
            padding: 10px;
        }
    </style>
</head>

<body style="font-family: Arial, sans-serif;">
    <span style="float: right;">
        Welcome <b><?= $email ?></b><br>
        <a href="?action=logout">Logout</a>
    </span>
    <h1 style="text-align: center;">VAT Payment Viewer</h1>

    <hr>
    <hr>
    <div style="float: right;margin-right: 10px;">
        <form action="" method="post">
            Select From:
            <input type="date" name="from" id="" style="padding: 5px;" value="<?= $from ?>">
            Select To:
            <input type="date" name="to" id="" style="padding: 5px;" value="<?= $to ?>">
            <input type="submit" name="submit" value="Check" style="border-radius: 5px;padding: 5px;background:white;">
        </form>
        <br><br><br><br>
        <?= $exportHtm ?>
    </div>

    <table style="border-collapse: collapse; width: 50%;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th class="th">Country</th>
                <th class="th">Amount</th>
                <th class="th">Total Orders</th>
                <th class="th">% Amount Covered</th>
            </tr>
        </thead>
        <tbody>

            <?= $tblHtm ?>

        </tbody>
    </table>

</body>

</html>