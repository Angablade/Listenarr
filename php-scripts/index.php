<?php
session_start();

require('creds.php');

// Check if the user is already logged in
if (isset($_SESSION['accessToken']) && !empty($_SESSION['accessToken'])) {
    header('Location: secure_page.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the login form

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'X-Emby-Authorization: MediaBrowser Client="Listenarr", Device="Listenarr Web", DeviceId="Listenarr Web", Version="1.0.0"',
        'X-Emby-Token: ' . $jellyapiKey,
    ];

    $data = [
        'Username' => $username,
        'Pw' => $password,
    ];

    $ch = curl_init($jellyfinBaseUrl . '/Users/AuthenticateByName');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_HTTPGET, False);

	$response = curl_exec($ch);

    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch) . '<hr />';
    } else {
        $responseData = json_decode($response, true);

        if (isset($responseData['AccessToken'])) {
            echo 'Login successful!<hr />';

            // Store user data in session
            $_SESSION['accessToken'] = $responseData['AccessToken'];
            $_SESSION['username'] = $username;

            header('Location: secure_page.php');
            exit();
        } else {
            if (isset($responseData['Error'])) {
                echo 'Login failed. Error: ' . $responseData['Error']['Message'] . '<hr />';
            } else {
                echo 'Unexpected response: ' . print_r($responseData, true) . '<hr />';
            }
        }
    }

    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jellyfin Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
<div class="login-container">

    <h2>Login with Jellyfin</h2>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Login">
    </form>
</div>

</body>
</html>