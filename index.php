<?php
// Minecraft Server Control Panel - PHP Implementation for XAMPP
// Main entry point

// Initialize session
session_start();

// Display all errors for debugging during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include required files
require_once 'includes/config.php';
require_once 'includes/server_manager.php';
require_once 'includes/functions.php';

// Initialize server manager
$serverManager = new ServerManager();

// Handle routes
$route = isset($_GET['route']) ? $_GET['route'] : 'dashboard';

// Common variables for all pages
$statuses = $serverManager->getAllServerStatuses();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    processFormSubmission($route, $serverManager);
}

// Handle API requests
if (strpos($route, 'api/') === 0) {
    header('Content-Type: application/json');
    handleApiRequest(substr($route, 4), $serverManager);
    exit;
}

// Render page
include 'includes/header.php';

switch ($route) {
    case 'dashboard':
        $servers = $serverManager->getAllServers();
        $useApi = $serverManager->getUseApi();
        include 'pages/dashboard.php';
        break;
        
    case 'add-server':
        $serverTypes = $serverManager->getServerTypes();
        $useApi = $serverManager->getUseApi();
        include 'pages/server_config.php';
        break;
        
    case 'edit-server':
        $serverName = isset($_GET['name']) ? $_GET['name'] : '';
        $server = $serverManager->getServer($serverName);
        if (!$server) {
            setFlashMessage('error', 'Server not found');
            redirect('dashboard');
        }
        include 'pages/server_config.php';
        break;
        
    case 'server-logs':
        $serverName = isset($_GET['name']) ? $_GET['name'] : '';
        $server = $serverManager->getServer($serverName);
        if (!$server) {
            setFlashMessage('error', 'Server not found');
            redirect('dashboard');
        }
        $logs = $serverManager->getServerLogs($serverName);
        include 'pages/server_logs.php';
        break;
        
    case 'player-management':
        $serverName = isset($_GET['name']) ? $_GET['name'] : '';
        $server = $serverManager->getServer($serverName);
        if (!$server) {
            setFlashMessage('error', 'Server not found');
            redirect('dashboard');
        }
        $onlinePlayers = [];
        $whitelist = [];
        $bannedPlayers = [];
        if ($serverManager->getServerStatus($serverName) === 'running') {
            $onlinePlayers = $serverManager->getPlayers($serverName);
        }
        $whitelist = $serverManager->getWhitelist($serverName);
        $bannedPlayers = $serverManager->getBannedPlayers($serverName);
        include 'pages/player_management.php';
        break;
        
    case 'server-properties':
        $serverName = isset($_GET['name']) ? $_GET['name'] : '';
        $server = $serverManager->getServer($serverName);
        if (!$server) {
            setFlashMessage('error', 'Server not found');
            redirect('dashboard');
        }
        $properties = $serverManager->getServerProperties($serverName);
        include 'pages/server_properties.php';
        break;
        
    case 'backups':
        $serverName = isset($_GET['name']) ? $_GET['name'] : '';
        $server = $serverManager->getServer($serverName);
        if (!$server) {
            setFlashMessage('error', 'Server not found');
            redirect('dashboard');
        }
        $backups = $serverManager->getBackups($serverName);
        include 'pages/backups.php';
        break;
        
    default:
        include 'pages/error.php';
        break;
}

include 'includes/footer.php';
?>