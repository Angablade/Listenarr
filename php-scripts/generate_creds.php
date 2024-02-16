<?php
session_start();

$content = <<<EOD
<?php
	session_start();

	//Jellyfin information
	\$jellyfinBaseUrl  = '{$_ENV['JELLYFIN_BASE_URL']}';
	\$jellyapiKey      = '{$_ENV['JELLYFIN_API_KEY']}';

	//Lidarr Information
	\$lidarrApiUrl     = '{$_ENV['LIDARR_API_URL']}';
    \$lidarrApiKey     = '{$_ENV['LIDARR_API_KEY']}';
	\$lidarrrootfolder = '{$_ENV['LIDARR_ROOT_FOLDER']}';
	\$lidarrQuality    = {$_ENV['LIDARR_QUALITY']};
	\$lidarrmetadata   = {$_ENV['LIDARR_METADATA']};

	//Discord Information
	\$enablehook       = {$_ENV['ENABLE_DISCORD_HOOK']};
	\$webhook          = '{$_ENV['DISCORD_WEBHOOK']}';
	\$discordico       = '{$_ENV['DISCORD_ICON']}';
	\$discordurl       = '{$_ENV['DISCORD_URL']}';
?>
EOD;

file_put_contents('/var/www/html/creds.php', $content);
?>
