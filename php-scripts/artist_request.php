<?php
require('creds.php');
require('func.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artistName = $_POST['artistName'] ?? '';

    $headers = [
        'X-Api-Key: ' . $lidarrApiKey,
    ];

    $ch = curl_init($lidarrApiUrl . 'artist/lookup?term=' . urlencode($artistName));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    header('Content-Type: application/json');
    echo $response;
    exit();
}

http_response_code(400);
echo 'Invalid request';
?>