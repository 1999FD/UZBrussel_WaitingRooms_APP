<?php
// Path to the JSON file
$jsonFilePath = "../data.json";

// Read the existing data from the file
$currentData = json_decode(file_get_contents($jsonFilePath), true);
if (!$currentData) {
    $currentData = array(); // Initialize as an empty array if the file is empty or decoding failed
}

// Read JSON input from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Access the displayId from the request data
$displayId = $data['displayId'];

// Check if the displayId exists and remove the corresponding object
$found = false;
foreach ($currentData as $key => $value) {
    if (isset($value['displayId']) && $value['displayId'] === $displayId) {
        unset($currentData[$key]);
        $found = true;
        $currentData = array_values($currentData);
        break;
    }
}

if ($found) {
    // Save the modified data preserving keys
    file_put_contents($jsonFilePath, json_encode($currentData, JSON_PRETTY_PRINT));
    // Return ok status
    header('HTTP/1.1 200 OK');
    echo json_encode(['status' => 'success', 'message' => 'Data deleted successfully.']);
} else {
    // Return not found status
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['status' => 'error', 'message' => 'Data not found.']);
}

exit;
?>
