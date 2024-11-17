<?php
$uploadSuccess = false;
$selectedOrientation = $_POST['orientation'];
// Handling image upload
if (isset($_FILES["fileToUpload"]["tmp_name"]) && !empty($_FILES["fileToUpload"]["tmp_name"])) {
    $target_dir = "../img/" . $selectedOrientation . "/"; // Directory where images will be saved according to orientation
    $fileBaseName = pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_FILENAME);  // Get file base name
    $imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION)); // Get file extension
    $target_file = $target_dir . $fileBaseName . '_' . time() . '.' . $imageFileType; // Append time() to make filename unique

    $uploadOk = 1;

    // Validate image file is actual image
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, array("jpg", "png", "jpeg", "gif"))) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Attempt to upload file if checks passed
    if ($uploadOk == 1 && move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        // Set full permissions (777) to the uploaded file
        // Change ownership to IIS_IUSRS group using icacls command
        exec('icacls "' . $target_file . '" /grant:r "IIS_IUSRS:(OI)(CI)F"');

        echo "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}


// Redirect back to the referring page
header("Location: " . $_SERVER['HTTP_REFERER'] . "?uploadSuccess=" . ($uploadSuccess ? 'true' : 'false'));
exit;
?>
