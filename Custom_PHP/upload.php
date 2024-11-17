<?php
// Handling text upload
$jsonFilePath = "../data.json";

// Read the existing data from the file
$currentData = json_decode(file_get_contents($jsonFilePath), true);
if (!$currentData) {
    $currentData = array(); // Initialize as an empty array if the file is empty or decoding failed
}

// Read JSON input from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Access data fields
$displayId = $data['displayId'];
$locationName = $data['locationName'];
$orientationName = $data['orientationName'];
$content = $data['content'];
$loketIDs = $data['loketIDs'];

// Check if the displayId already exists, update if exists, add if new
$found = false;
foreach ($currentData as $key => $value) {
    // Key is a number but displayId is a string, so we need to compare them as strings
    if ($currentData[$key]['displayId'] === $displayId) {
        $currentData[$key]['locationName'] = $locationName;
        $currentData[$key]['orientationName'] = $orientationName;
        $currentData[$key]['content'] = $content;
        $currentData[$key]['loketIDs'] = $loketIDs;
        $found = true;
        break;
    }
}
if (!$found) {
    // Add new data
    $currentData[] = array(
        'displayId' => (string) $displayId,
        'locationName' => $locationName,
        'orientationName' => $orientationName,
        'content' => $content,
        'loketIDs' => $loketIDs
    );
}

// save the modified data
file_put_contents($jsonFilePath, json_encode($currentData, JSON_PRETTY_PRINT));
// Return ok status
header('HTTP/1.1 200 OK');

// Redirect back to the referring page
exit;
?>
