<?php
header('Content-Type: application/json');

// Define the directory path
$directoryPath = '../Shares';

// Check if the directory exists
if (!is_dir($directoryPath)) {
    echo json_encode(["error" => "Directory not found"]);
    exit;
}

// Step 1: Get all filenames in the directory
$allFiles = array();
foreach (scandir($directoryPath) as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'xml') {
        $allFiles[] = $file;
    }
}

// Step 2: Filter for files starting with "Waitingroom_" and extract names
$waitingroomFiles = array();
$waitingroomNames = array();
$ids = array();
foreach ($allFiles as $file) {
    if (strpos($file, 'Waitingroom_') === 0) {
        $waitingroomFiles[] = $file; // Add to Waitingroom files list

        // Remove prefix and suffix to get the name
        $nameWithoutPrefixAndSuffix = str_replace(['Waitingroom_', '.xml'], '', $file);
        $waitingroomNames[] = $nameWithoutPrefixAndSuffix;

        // Attempt to extract ID if it exists
        if (preg_match('/^Waitingroom_(\d+)\.xml$/', $file, $matches)) {
            $ids[] = $matches[1]; // Add only the ID part to the array
        }
    }
}

// Output all lists: all filenames, Waitingroom filenames, extracted IDs, and Waitingroom names
echo json_encode([
    "all_files" => $allFiles,
    "waitingroom_files" => $waitingroomFiles,
    "waitingroom_ids" => $waitingroomNames
]);
?>
