<?php



namespace Dompdf;

require_once 'db.php';

require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/dompdf/vendor/autoload.php';


$packageType = addslashes($_POST['packageType']);
$premium = addslashes($_POST['premium']);
$orderDate = addslashes($_POST['orderDate']);
$orderID = addslashes($_POST['orderID']);
$billingName = addslashes($_POST['billingName']);
$billingCard = addslashes($_POST['billingCard']);
$billingEmail = addslashes($_POST['billingEmail']);
$orderPrice = addslashes($_POST['orderPrice']);
$orderAmount = addslashes($_POST['orderAmount']);
$orderCountry = addslashes($_POST['orderCountry']);
$socialmedia = addslashes($_POST['socialmedia']);

$priceSymbol = "$";

$priceSymbol = "$";
if($orderCountry == 'uk'){
    $priceSymbol = "&pound;";

}
else if ($orderCountry == 'us' || $orderCountry == 'ww'){
    $priceSymbol = "$";
}

switch($socialmedia){
    case 'ig':
        $SM = 'Instagram';
        break;
    case 'tt':
        $SM = 'Tiktok';
        break;    
}

if($premium == '1') {
    $premium = 'Premium';
}
else {
    $premium = '';
}

$path = $siteDomain.'/imgs/receiptlogo.png';

$type = pathinfo($path, PATHINFO_EXTENSION);

$data = file_get_contents($path);

$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); // logo 



$htm = '<html>

<head>

<link href="style.css" type="text/css" rel="stylesheet"/>

</head>

<body style="font-family: \'Montserrat\', sans-serif;">

<div style="width: 90%;margin:auto"> 

    <div>

        <img src="'. $base64 .'" />

    </div>

    <div>

        <h1>Receipt of '. $SM .' Order: #'. $orderID .'</h1>

    </div>

    <br>

    <br>

    <br>

    <div>

        <h3 style="color:#1e3cab;">Customer and transaction:</h3>

    </div>

    <br>

    <div>

        <table style="border: 1px solid black;width:100%">

            <tbody >

                <tr ><td>Order date</td><td>'. $orderDate .'</td></tr>

                <tr><td>Transaction amount</td><td>'. $priceSymbol . $orderPrice .'</td></tr>

                <tr><td>Card used</td><td>*** '. $billingCard .'</td></tr>

                <tr><td>Email address</td><td>'. $billingName .'</td></tr>

                <tr><td>Billing name</td><td>'. $billingEmail .'</td></tr>

            </tbody>

        </table>

    </div>

    <br>

    <br>

    <br>

    <div>

        <h3 style="color:#1e3cab;">Order Details:</h3>

    </div>

    <br>

    <div>

        <table style="border: 1px solid black;width:100%">

            <tbody >

                <tr ><td>Service</td><td>'. $orderAmount . ' ' .$SM .' '. $premium. ' ' .$packageType .'</td></tr>

                <tr><td>Total (VAT included)</td><td>'. $priceSymbol . $orderPrice .'</td></tr>

            </tbody>

        </table>

    </div>

    <br>

    <div>

        <h5>VAT Number: 356 2393 86</h5>

    </div>

    <br>

    <br>

        <h5>2012 - 2022 ITH Retail Group Ltd trading as Superviral</h5>

        <h5>Company number: 11598533</h5>

        <h5>12-22 Newhall Street, Birmingham, B3 3AS</h5>

</div>

</body>

</html>';



$dompdf = new Dompdf(); 

$html = $htm; 

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream("Superviral_Receipt",array("Attachment" => true));
header('Location: /'.$loclinkforward.'account/orders/');
exit(0);

