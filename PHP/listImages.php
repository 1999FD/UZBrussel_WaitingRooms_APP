<?php
// Directories to search for images
$directories = [
    'horizontal' => '../img/horizontal/',
    'vertical' => '../img/vertical/'
];

// Array to store images, separated by directory
$images = [
    'horizontal' => [],
    'vertical' => []
];

// File types to search for
$fileTypes = '*.{jpg,jpeg,png,gif}';

// Loop through each directory and collect image files
foreach ($directories as $key => $directory) {
    // Collect all images in the current directory and store them in the appropriate sub-array
    $images[$key] = glob($directory . $fileTypes, GLOB_BRACE);
}

// Output all images in JSON format, grouped by 'horizontal' and 'vertical'
echo json_encode($images);
?>
