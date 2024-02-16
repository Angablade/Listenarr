<?php
require('creds.php');
require('func.php');
session_start();

if (isset($_SESSION['accessToken'])) {
    $accessToken = $_SESSION['accessToken'];
	$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listenarr</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="script.js" defer></script>
	<script>
		var usernameFromSession = "<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>";
	</script>
</head>
<body>
    <div id="myModal">
        <h2 id="modalTitle"></h2>
        <p id="modalMessage"></p>
        <button id="closeBtn">Close</button>
    </div>
	<div id="confirmationModal">
        <h2>Confirmation</h2>
        <p id="confirmationMessage"></p>
        <button id="confirmButton">Confirm</button>
        <button id="cancelButton">Cancel</button>
    </div>
    <div class="secure-container">
		<h2>Welcome <?php echo($username); ?>! to Listenarr</h2>
		<div class="search-container">
			<input type="text" id="artistName" name="artistName" placeholder="Artist name..." onkeydown="handleEnterKey(event)">
			<button onclick="handleArtistRequest()">Search Artist</button>
		</div>
        <div id="resultsContainer" class="results-container"></div>
        <?php
        } else {
            echo 'Error retrieving Lidarr status.';
        }
        ?>

		<br />
        <a href="logout.php">Log Out</a><br />
		<?php
        $lidarrResponse = makeLidarrRequest($lidarrApiUrl . 'system/status', $lidarrApiKey);

        if ($lidarrResponse !== null) {
            echo '<h6>Lidarr Status: Connected...</h6>';
        ?>
    </div>
</body>
</html>

<?php
} else {
    header('Location: login.php');
    exit();
}

function makeLidarrRequest($url, $apiKey) {
    $headers = [
        'X-Api-Key: ' . $apiKey,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $response = curl_exec($ch);

    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch) . '<hr />';
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) {
        echo 'HTTP error: ' . $httpCode . '<hr />';
        return null;
    }

    curl_close($ch);

    return json_decode($response, true);
}
?>
