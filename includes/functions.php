<?php
// Helper functions for the Minecraft Server Control Panel

/**
 * Redirect to a specific route
 */
function redirect($route) {
    $url = base_url("?route=$route");
    header("Location: $url");
    exit;
}

/**
 * Set a flash message to be displayed on the next page
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear the flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Process form submissions based on the current route
 */
function processFormSubmission($route, $serverManager) {
    switch ($route) {
        case 'api/settings':
            $data = json_decode(file_get_contents('php://input'), true);
            $useApi = isset($data['use_api']) ? (bool)$data['use_api'] : false;
            $serverManager->setUseApi($useApi);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'use_api' => $useApi]);
            exit;

        case 'add-server':
            $name = $_POST['name'] ?? '';
            $path = $_POST['path'] ?? '';
            $minMemory = $_POST['min_memory'] ?? DEFAULT_MIN_MEMORY;
            $maxMemory = $_POST['max_memory'] ?? DEFAULT_MAX_MEMORY;
            $port = (int)($_POST['port'] ?? DEFAULT_PORT);
            
            // Check if using API
            $useApi = isset($_POST['use_api']);
            
            if (empty($name) || empty($path)) {
                setFlashMessage('error', 'Name and path are required');
                redirect('add-server');
            }
            
            if ($useApi) {
                $serverType = $_POST['server_type'] ?? '';
                $version = $_POST['version'] ?? '';
                
                if (empty($serverType) || empty($version)) {
                    setFlashMessage('error', 'Server type and version are required when using API');
                    redirect('add-server');
                }
                
                // Create server using API
                list($success, $message) = $serverManager->createServerFromApi(
                    $name, $path, $serverType, $version, $minMemory, $maxMemory, $port
                );
                
                if ($success) {
                    setFlashMessage('success', "Server $name added successfully using API");
                } else {
                    setFlashMessage('error', "Failed to create server: $message");
                    redirect('add-server');
                }
            } else {
                $jarFile = $_POST['jar_file'] ?? '';
                
                if (empty($jarFile)) {
                    setFlashMessage('error', 'JAR file name is required');
                    redirect('add-server');
                }
                
                // Create server
                $server = [
                    'name' => $name,
                    'path' => $path,
                    'jar_file' => $jarFile,
                    'min_memory' => $minMemory,
                    'max_memory' => $maxMemory,
                    'port' => $port
                ];
                
                $serverManager->addServer($server);
                setFlashMessage('success', "Server $name added successfully");
            }
            
            redirect('dashboard');
            break;
            
        case 'edit-server':
            $serverName = $_GET['name'] ?? '';
            $name = $_POST['name'] ?? '';
            $path = $_POST['path'] ?? '';
            $jarFile = $_POST['jar_file'] ?? '';
            $minMemory = $_POST['min_memory'] ?? DEFAULT_MIN_MEMORY;
            $maxMemory = $_POST['max_memory'] ?? DEFAULT_MAX_MEMORY;
            $port = (int)($_POST['port'] ?? DEFAULT_PORT);
            
            if (empty($name) || empty($path) || empty($jarFile)) {
                setFlashMessage('error', 'All fields are required');
                redirect("edit-server&name=$serverName");
            }
            
            $server = [
                'name' => $name,
                'path' => $path,
                'jar_file' => $jarFile,
                'min_memory' => $minMemory,
                'max_memory' => $maxMemory,
                'port' => $port
            ];
            
            $serverManager->updateServer($serverName, $server);
            setFlashMessage('success', "Server $name updated successfully");
            redirect('dashboard');
            break;
            
        case 'delete-server':
            $serverName = $_GET['name'] ?? '';
            
            // Make sure the server is stopped first
            if ($serverManager->getServerStatus($serverName) === 'running') {
                $serverManager->stopServer($serverName);
            }
            
            $success = $serverManager->removeServer($serverName);
            
            if ($success) {
                setFlashMessage('success', "Server $serverName deleted successfully");
            } else {
                setFlashMessage('error', "Failed to delete server $serverName");
            }
            
            redirect('dashboard');
            break;
            
        case 'server-properties':
            $serverName = $_GET['name'] ?? '';
            
            // Extract properties from form
            $properties = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'property_') === 0) {
                    $propertyName = substr($key, 9); // Remove 'property_' prefix
                    $properties[$propertyName] = $value;
                }
            }
            
            // Update properties
            list($success, $message) = $serverManager->updateServerProperties($serverName, $properties);
            
            if ($success) {
                setFlashMessage('success', 'Server properties updated successfully');
            } else {
                setFlashMessage('error', "Failed to update server properties: $message");
            }
            
            redirect("server-properties&name=$serverName");
            break;
    }
}

/**
 * Handle API requests
 */
function handleApiRequest($endpoint, $serverManager) {
    switch ($endpoint) {
        case 'server_types':
            $serverTypes = $serverManager->getServerTypes();
            echo json_encode(['server_types' => $serverTypes]);
            break;
            
        case 'versions':
            $serverType = $_GET['type'] ?? '';
            if (empty($serverType)) {
                echo json_encode(['error' => 'Server type is required']);
                break;
            }
            
            $versions = $serverManager->getVersionsForType($serverType);
            echo json_encode(['versions' => $versions]);
            break;
            
        case 'server/start':
            $serverName = $_GET['name'] ?? '';
            list($success, $message) = $serverManager->startServer($serverName);
            echo json_encode(['success' => $success, 'message' => $message]);
            break;
            
        case 'server/stop':
            $serverName = $_GET['name'] ?? '';
            list($success, $message) = $serverManager->stopServer($serverName);
            echo json_encode(['success' => $success, 'message' => $message]);
            break;
            
        case 'server/status':
            $serverName = $_GET['name'] ?? '';
            $status = $serverManager->getServerStatus($serverName);
            echo json_encode(['status' => $status]);
            break;
            
        case 'server/logs':
            $serverName = $_GET['name'] ?? '';
            $logs = $serverManager->getServerLogs($serverName);
            echo json_encode(['logs' => $logs]);
            break;
            
        case 'server/command':
            $data = json_decode(file_get_contents('php://input'), true);
            $serverName = $_GET['name'] ?? '';
            $command = $data['command'] ?? '';
            
            if (empty($command)) {
                echo json_encode(['success' => false, 'message' => 'No command provided']);
                break;
            }
            
            list($success, $message) = $serverManager->executeCommand($serverName, $command);
            echo json_encode(['success' => $success, 'message' => $message]);
            break;
            
        case 'server/backup/create':
            $serverName = $_GET['name'] ?? '';
            list($success, $message) = $serverManager->createBackup($serverName);
            echo json_encode(['success' => $success, 'message' => $message]);
            break;
            
        case 'server/backup/restore':
            $data = json_decode(file_get_contents('php://input'), true);
            $serverName = $_GET['name'] ?? '';
            $backupName = $data['backup_name'] ?? '';
            
            if (empty($backupName)) {
                echo json_encode(['success' => false, 'message' => 'No backup specified']);
                break;
            }
            
            list($success, $message) = $serverManager->restoreBackup($serverName, $backupName);
            echo json_encode(['success' => $success, 'message' => $message]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid API endpoint']);
            break;
    }
}
?>