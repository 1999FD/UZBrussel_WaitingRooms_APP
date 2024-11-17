<?php
// Handling text upload
$jsonFilePath = "../waiting_rooms_data.json";

// Read the existing data from the file
$currentData = json_decode(file_get_contents($jsonFilePath), true);
if (!$currentData) {
    $currentData = array(); // Initialize as an empty array if the file is empty or decoding failed
}

// Read JSON input from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Access data fields
$waiting_room_id = $data['waiting_room_id'];
$displayId = $data['displayId'];
$displayName = $data['displayName'];
$orientationName = $data['orientationName'];
$service = $data['service'];

// First find the waiting room
$found_wr = false;
$found_display = false;
foreach ($currentData as $key => $value) {
    if ((string) $key === (string) $waiting_room_id) {
        $found_wr = true;
        // Loop through all the displays in the waiting room
        foreach ($currentData[$key] as $key2 => $value2) {
            if ((string) $key2 === (string) $displayId) {
                $found_display = true;
                $currentData[$key][$key2]['displayName'] = $displayName;
                $currentData[$key][$key2]['orientationName'] = $orientationName;
                $currentData[$key][$key2]['service'] = $service;
                break;
            }
        }
        break;
    }
}

if (!$found_wr || !$found_display) {
    if (!$found_wr) {
        // Add new waiting room with new display
        $currentData[$waiting_room_id][$displayId] = [
            'displayName' => $displayName,
            'orientationName' => $orientationName,
            'service' => $service
        ];
    } else {
        // Add new display to the existing waiting room without overwriting
        $currentData[$waiting_room_id][$displayId] = [
            'displayName' => $displayName,
            'orientationName' => $orientationName,
            'service' => $service
        ];
    }
}

// save the modified data
file_put_contents($jsonFilePath, json_encode($currentData, JSON_PRETTY_PRINT));
// Return ok status
header('HTTP/1.1 200 OK');

// Redirect back to the referring page
exit;
