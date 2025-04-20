<?php
// Configuration settings for the Minecraft Server Control Panel

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Directory paths with proper DIRECTORY_SEPARATOR for Windows compatibility
define('ROOT_DIR', dirname(__DIR__));
define('INCLUDES_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');
define('PAGES_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'pages');
define('ASSETS_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'assets');

// Configuration file for server data
define('CONFIG_FILE', ROOT_DIR . DIRECTORY_SEPARATOR . 'minecraft_servers.json');

// MCJars API configuration
// Using versions.mcjars.app directly for API access
define('MCJARS_API_URL', 'https://mcjars.com/api/v2');
define('ALTERNATE_API_URL', 'https://versions.mcjars.app/api/v2');

// Local fallback if API can't be reached
define('USE_API_FALLBACK', true);

// Default settings
define('DEFAULT_MIN_MEMORY', '1G');
define('DEFAULT_MAX_MEMORY', '2G');
define('DEFAULT_PORT', 25565);

// OS Detection
define('IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

// Function to handle relative URLs properly
function base_url($path = '') {
    $base_url = '';
    
    // Get the directory name relative to the document root
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    
    // If we're in a subdirectory, include it in the base URL
    if ($script_name !== '/' && $script_name !== '\\') {
        $base_url = $script_name;
    }
    
    // Add a leading slash to $path if it doesn't have one and it's not empty
    if (!empty($path) && $path[0] !== '/' && $path[0] !== '?') {
        $path = '/' . $path;
    }
    
    return $base_url . $path;
}

// Function to convert slashes based on OS
function convert_path($path) {
    if (IS_WINDOWS) {
        // Convert forward slashes to backslashes for Windows
        return str_replace('/', '\\', $path);
    }
    return $path;
}

// Function to ensure directory exists
function ensure_dir($dir) {
    if (!file_exists($dir)) {
        return mkdir($dir, 0755, true);
    }
    return true;
}

// Hardcoded server types and default versions for fallback
// when API is not available
$DEFAULT_SERVER_TYPES = [
    'VANILLA' => 'Official Minecraft Server',
    'PAPER' => 'Paper Server (Performance)',
    'SPIGOT' => 'Spigot Server (Plugins)',
    'FABRIC' => 'Fabric Server (Mods)',
    'FORGE' => 'Forge Server (Mods)',
    'BUKKIT' => 'CraftBukkit Server (Plugins)'
];

$DEFAULT_VERSIONS = [
    ['id' => '1.20.4', 'type' => 'RELEASE', 'java' => '17'],
    ['id' => '1.19.4', 'type' => 'RELEASE', 'java' => '17'],
    ['id' => '1.18.2', 'type' => 'RELEASE', 'java' => '17'],
    ['id' => '1.17.1', 'type' => 'RELEASE', 'java' => '16'],
    ['id' => '1.16.5', 'type' => 'RELEASE', 'java' => '8']
];
?>