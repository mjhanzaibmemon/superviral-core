<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';

$url = "https://ipinfo.io/data/free/country.csv.gz?token=".$ipinfoToken;
$outputFile = "location.csv.gz";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Equivalent to `-L` in curl

$data = curl_exec($ch);
if (curl_errno($ch)) {
    echo "Curl error: " . curl_error($ch);
} else {
    file_put_contents($outputFile, $data);
    echo "File downloaded successfully.";
}

curl_close($ch);

$gzFile = "location.csv.gz";
$csvFile = "location.csv";
$tempFile = "location_temp.csv"; // Temporary file to store filtered data

// Step 1: Decompress location.csv.gz into temp.csv
$gz = gzopen($gzFile, "rb");
$csv = fopen($tempFile, "w");

if ($gz && $csv) {
    while (!gzeof($gz)) {
        fwrite($csv, gzread($gz, 4096)); // Read in chunks
    }
    gzclose($gz);
    fclose($csv);
} else {
    die("Error extracting file.");
}

// Step 2: Open temp.csv and filter records where country_name = "India"
$inputHandle = fopen($tempFile, "r");
$outputHandle = fopen($csvFile, "w");

if ($inputHandle && $outputHandle) {
    $header = fgetcsv($inputHandle); // Read header
    fwrite($outputHandle, implode(",", $header) . "\n"); // Write header to output file

    $allowedCountries = [
        "India", "Indonesia", "Pakistan", "Egypt",
        "Philippines", "Morocco", "Algeria", "Nepal",
        "Brazil", "Sri Lanka", "Bangladesh", "United States", "Canada", "United Kingdom"
    ];

    while (($row = fgetcsv($inputHandle)) !== false) {
        $assocRow = array_combine($header, $row); // Convert row to associative array

        if (in_array($assocRow["country_name"], $allowedCountries)) {
            fwrite($outputHandle, implode(",", $row) . "\n"); // Write matching row
        }
    }

    fclose($inputHandle);
    fclose($outputHandle);
    unlink($tempFile); // Delete temp file after processing

    echo "Filtered records saved to <a href = '$csvFile' download>download</a>.";

    include '2.php';
    die;
    
} else {
    echo "Error processing CSV file.";
}
