<?php
function getUserRoles($accessToken) {
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'X-Emby-Token: ' . $accessToken,
    ];

    $ch = curl_init($GLOBALS['jellyfinBaseUrl'] . '/Users/Current');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch) . '<hr />';
        return [];
    } else {
        $userData = json_decode($response, true);
        return $userData['Roles'] ?? [];
    }

    curl_close($ch);
}

function hasLibraryAccess($accessToken, $filePath) {
    $libraries = getUserLibraries($accessToken);
    foreach ($libraries as $library) {
        if (startsWith($filePath, $library['Path'])) {
            return true;
        }
    }
    return false;
}

function getUserLibraries($accessToken) {
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'X-Emby-Token: ' . $accessToken,
    ];

    $url = $jellyfinBaseUrl . '/Users/Current';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if ($response === false) {
        // Handle error
        return [];
    }

    $userData = json_decode($response, true);

    if (isset($userData['ItemData']['User']['Policy']['EnableUserPreferenceAccess']) && $userData['ItemData']['User']['Policy']['EnableUserPreferenceAccess']) {
        // The user has access to libraries
        return $userData['ItemData']['User']['Policy']['BlockedFolders'];
    }

    return [];
}

function isRegularUser($accessToken, $username) {
    $userRoles = getUserRoles($accessToken);
    
    // You can customize this logic based on how Jellyfin represents user roles
    return in_array('RegularUser', $userRoles);
}

function startsWith($haystack, $needle) {
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

function sendDiscordWebhook($username, $artist, $genres, $image, $overview, $ratings) {
    include('creds.php');
    $webhookUrl = $webhook;

    $payload = [
        'content' => null,
        'embeds' => [
            [
                'title' => 'New Artist Added!',
                'color' => '1127128',
                'fields' => [
                    [
                        'name' => 'Artist',
                        'value' => $artist
                    ],
					[
						'name' => 'Genre',
						'value' => $genres
					],
					[
						'name' => 'Overview',
						'value' => substr($overview, 0, 200) . '...'
					],

                    [
                        'name' => 'Rating',
                        'value' => $ratings . '/10'
                    ]
                ],
                'author' => [
                    'name' => 'Listenarr Web',
                    'url' => $discordurl,
                    'icon_url' => $discordico
                ],
				'footer' => [
					'text' => 'Requester: ' . $username
				],
				'timestamp' => date('c'),
				'thumbnail' => [
					'url' => $image
				]
            ]
        ],
        'attachments' => []
    ];

    $payload = json_encode($payload, JSON_UNESCAPED_SLASHES);

    $options = [
        CURLOPT_URL => $webhookUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $options);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        die('Curl error: ' . curl_error($ch));
    }

    // Check for HTTP errors
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode >= 400) {
        die('HTTP error: ' . $httpCode);
    }

    curl_close($ch);
}


function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function implode_all($glue, $arr){            
    for ($i=0; $i<count($arr); $i++) {
        if (@is_array($arr[$i])) 
            $arr[$i] = implode_all ($glue, $arr[$i]);
    }            
    return implode($glue, $arr);
}
?>