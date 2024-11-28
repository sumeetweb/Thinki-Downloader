<?php

load_env(__DIR__ . '/.env');

$clientdate = $_ENV['CLIENT_DATE'];
$cookiedata = $_ENV['COOKIE_DATA'];
$video_download_quality = $_ENV['VIDEO_DOWNLOAD_QUALITY'];
$FFMPEG_PRESENTATION_MERGE_FLAG = false;
$current_directory_path = getcwd();
$msg = '';

if($cookiedata == '' || $clientdate == '')
    $msg .= "Cookie data and Client Date not set. Use the ReadMe file first before using this script.\n";
if(!extension_loaded('curl'))
	$msg .= 'Curl not installed or enabled in php.ini\n';
if(!extension_loaded('mbstring'))
	$msg .= 'Mbstring extension not enabled. Remove ; from php.ini config in the line ;extension=mbstring\n';
if(!extension_loaded('openssl'))
	$msg .= 'Openssl not enabled in php.ini\n';
if(!$current_directory_path)
	$msg .= 'Unable to get current working directory. Check if PHP or current terminal has permission to access the directory\n';
if(!is_writable($current_directory_path))
	$msg .= 'Current directory is not writable. Check if PHP or current terminal has permission to write in the directory\n';
if($msg != '')
    die($msg);


function load_env($filePath){
	if (!file_exists($filePath)) {
		throw new Exception("The .env file does not exist.");
	}

	$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

	foreach ($lines as $line) {
		// Skip comments
		if (strpos(trim($line), '#') === 0) {
			continue;
		}

		// Split by '=' to separate key and value
		[$name, $value] = array_map('trim', explode('=', $line, 2));

		// Remove surrounding quotes from the value
		$value = trim($value, "'\"");

		// Set the environment variable
		$_ENV[$name] = $value;
		$_SERVER[$name] = $value;
		putenv("$name=$value");
	}
}