<?php
header('Content-Type: application/json');

// Read JSON input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);  // decode JSON to an associative array

$gebruikersnaam = $input['gebruikersnaam'];
$wachtwoord = $input['wachtwoord'];
$secret = '3upqsvswtqdwkc6zviurak';

// Check if the gebruikersnaam is "admin" and the wachtwoord is "Azerty-123!"
if ($gebruikersnaam === 'admin' && $wachtwoord === 'Azerty-123!') {
    // If credentials are correct, return a success message or perform further actions
    echo json_encode(array('ok' => true, 'message' => 'Login successful', 'username' => $gebruikersnaam, 'password' => $wachtwoord, 'secret' => $secret));
} else {
    // If credentials are incorrect, return an error message
    echo json_encode(array('ok' => false, 'message' => 'Invalid username or password'));
}
?>
