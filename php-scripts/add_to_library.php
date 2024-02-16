<?php
require('creds.php');

require('func.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is logged in
    if (isset($_SESSION['accessToken'])) {
        $accessToken = $_SESSION['accessToken'];

        $postData = file_get_contents("php://input");

        $artistMetadata = json_decode($postData, true);
		$username = $artistMetadata['usernameFromSession'];
		
		$artistMetadata['qualityProfileId'] = $lidarrQuality; 
		$artistMetadata['metadataProfileId'] = $lidarrmetadata; 
		$artistMetadata['addOptions'] = [
			'monitor' => 'all',
			'addType' => 'automatic',
			'monitored' => true,
			'searchForMissingAlbums' => true
		];
		$artistMetadata['rootFolderPath'] = $lidarrrootfolder;
        $artistMetadata['rootFolderPath'] = $lidarrrootfolder;
        $result = addToLibrary($artistMetadata, $lidarrApiUrl, $lidarrApiKey, $username);

        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['error' => 'User not logged in']);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid request method']);
}

function addToLibrary($artistMetadata, $lidarrApiUrl, $lidarrApiKey, $name) {
    $url = $lidarrApiUrl . 'artist';
    $headers = [
        'X-Api-Key: ' . $lidarrApiKey,
        'Content-Type: application/json',
    ];

    $existingArtist = getExistingArtist($artistMetadata['foreignArtistId'], $lidarrApiUrl, $lidarrApiKey);

    if ($existingArtist) {
        return ['success' => false, 'message' => 'Artist with ForeignArtistId already exists'];
    }

    $ch = curl_init($url);
    $payload = json_encode($artistMetadata);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) {

        $artistId = json_decode($response, true)['id'];
        
		if($enablehook = true){
			sendDiscordWebhook($name, $artistMetadata['artistName'], implode_all(',',$artistMetadata['genres']), end($artistMetadata['images'])['remoteUrl'], $artistMetadata['overview'], $artistMetadata['ratings']['value']);
		};
        
        setArtistMonitored($artistId, $lidarrApiUrl, $lidarrApiKey);

        return ['success' => true, 'message' => 'Artist added to library successfully'];
    } else {
        // Error
        return ['success' => false, 'message' => 'Failed to add artist to library'];
    }
}


function setArtistMonitored($artistId, $lidarrApiUrl, $lidarrApiKey) {
    $url = $lidarrApiUrl . 'artist/' . $artistId . '/monitored?apikey=' . $lidarrApiKey;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_PUT, 1);
    // Include other cURL options as needed
    $response = curl_exec($ch);
    $refreshUrl = $lidarrApiUrl . 'artist/' . $artistId . '/refresh?apikey=' . $lidarrApiKey;
    $chRefresh = curl_init($refreshUrl);
    curl_setopt($chRefresh, CURLOPT_POST, 1);
    $responseRefresh = curl_exec($chRefresh);
    return json_decode($responseRefresh, true);
}

function getExistingArtist($foreignArtistId, $lidarrApiUrl, $lidarrApiKey) {
    $url = $lidarrApiUrl . 'artist/foreignid/' . urlencode($foreignArtistId);
    $headers = [
        'X-Api-Key: ' . $lidarrApiKey,
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    } else {
        return null;
    }
}
?>
