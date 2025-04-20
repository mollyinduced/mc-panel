<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
}

$type = $_POST['type'] ?? null;
$version = $_POST['version'] ?? null;
$server_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $_POST['server_name'] ?? 'default_server');

if (!$type || !$version) {
    die('Missing server type or version.');
}

// API URL to get the download link
$api_url = "https://versions.mcjars.app/api/v2/builds/$type/$version?fields=installation";
$response = json_decode(file_get_contents($api_url), true);

// Extract the download URL
$download_url = $response['builds'][0]['installation'][0][0]['url'] ?? null;
if (!$download_url) {
    die('Failed to retrieve download URL.');
}

// Define the storage path in the 'servers' directory
$storage_dir = __DIR__ . "/servers/$server_name";

if (!is_dir($storage_dir)) {
    mkdir($storage_dir, 0777, true);
}

// Generate the local file path
$local_path = $storage_dir . "/server.jar";

// Download the file
$downloaded_file = file_get_contents($download_url);
if ($downloaded_file === false) {
    die('Failed to download server file.');
}

// Save the file
if (file_put_contents($local_path, $downloaded_file) === false) {
    die('Failed to save server file.');
}

// Confirm the file is stored
if (!file_exists($local_path)) {
    die('File saving error: The file was not found after writing.');
}

header("Location: ./");
exit;
?>
