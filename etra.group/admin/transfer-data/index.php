<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


if(isset($_POST['submit'])){

        $tableName = $_POST['table'];

		switch($tableName){
			case 'ig_thumbs':
				$sql_table = 'ig_thumbs';
				break;
			case 'ig_api_stats':
				$sql_table = 'ig_api_stats';
				break;
			default:
				$sql_table = '';
				$error = 'table not found';
				break;
        }

		if(!$error){
			// Step 1: Get cutoff ID (5,000th latest)
			$cutoffId = null;
			$mysqli = new mysqli('localhost', $dbUser, $dbPass, $dbName);

			if ($mysqli->connect_error) die("DB Error: " . $mysqli->connect_error);

			$res = $mysqli->query("SELECT id FROM ".addslashes($sql_table)." ORDER BY id DESC LIMIT 1 OFFSET 4999");
			if ($row = $res->fetch_assoc()){$cutoffId = $row['id'];}
			$mysqli->close();

			if (!$cutoffId) die("Could not determine cutoff ID.");

			// Step 2: Build and run shell command
			$dumpCmd = sprintf(
			'/bin/mysqldump -u%s -p%s %s %s --where="id >= %d" | /bin/mysql -h%s -u%s -p%s %s',
			escapeshellarg($dbUser),
			escapeshellarg($dbPass),
			escapeshellarg($dbName),
			escapeshellarg($tableName),
			(int)$cutoffId,
			escapeshellarg($testTransferDbHost),
			escapeshellarg($testTransferDbUser),
			escapeshellarg($testTransferDbPass),
			escapeshellarg($testTransferDbName)
			);

			// Run the command and capture the output
			$output = shell_exec($dumpCmd . ' 2>&1');
		}
}


$tpl = str_replace('{msg}',$error,$tpl);

output($tpl, $options);
