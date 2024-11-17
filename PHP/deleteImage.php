<?php
    // Get JSON input and decode it
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true); // decode as an associative array

    if (isset($data['imagePath'])) {
        $file = $data['imagePath'];
        if (file_exists($file)) {
            unlink($file); // Delete the file
            echo json_encode(['success' => true, 'message' => 'Deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'File does not exist']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No image specified']);
    }
?>
