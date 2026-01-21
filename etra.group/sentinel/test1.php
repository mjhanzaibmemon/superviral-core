<?php
// Specify the file path
$file = '../test-sentinel.txt';

// The text to add to the file
//$textToAdd = "This is message 1 - from Rabban.\n";

// Open the file in append mode
$fileHandle = fopen($file, 'a');

// Check if the file was opened successfully
if ($fileHandle) {
    // Write the text to the file
    fwrite($fileHandle, $textToAdd);

    // Close the file handle
    fclose($fileHandle);

    //echo "Text added successfully! 1";
} else {
    //echo "Failed to open the file.";
}
?>