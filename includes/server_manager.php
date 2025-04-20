<?php
// Server Manager class for the Minecraft Server Control Panel

class ServerManager {
    private $servers = [];
    private $useApi = false;
    
    public function __construct() {
        $this->loadServers();
    }
    
    /**
     * Load server configurations from JSON file
     */
    public function loadServers() {
        if (file_exists(CONFIG_FILE)) {
            $data = json_decode(file_get_contents(CONFIG_FILE), true);
            if (isset($data['servers'])) {
                $this->servers = $data['servers'];
            }
            if (isset($data['use_api'])) {
                $this->useApi = (bool)$data['use_api'];
            }
        }
    }
    
    /**
     * Save server configurations to JSON file
     */
    public function saveServers() {
        $data = [
            'servers' => $this->servers,
            'use_api' => $this->useApi
        ];
        
        $dir = dirname(CONFIG_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents(CONFIG_FILE, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get all servers
     */
    public function getAllServers() {
        return $this->servers;
    }
    
    /**
     * Get a specific server by name
     */
    public function getServer($name) {
        foreach ($this->servers as $server) {
            if ($server['name'] === $name) {
                return $server;
            }
        }
        return null;
    }
    
    /**
     * Add a new server
     */
    public function addServer($server) {
        $this->servers[] = $server;
        $this->saveServers();
        return true;
    }
    
    /**
     * Update an existing server
     */
    public function updateServer($oldName, $newServer) {
        foreach ($this->servers as $key => $server) {
            if ($server['name'] === $oldName) {
                $this->servers[$key] = $newServer;
                $this->saveServers();
                return true;
            }
        }
        return false;
    }
    
    /**
     * Remove a server
     */
    public function removeServer($name) {
        foreach ($this->servers as $key => $server) {
            if ($server['name'] === $name) {
                unset($this->servers[$key]);
                $this->servers = array_values($this->servers); // Re-index array
                $this->saveServers();
                return true;
            }
        }
        return false;
    }
    
    /**
     * Set whether to use API for server creation
     */
    public function setUseApi($useApi) {
        $this->useApi = (bool)$useApi;
        $this->saveServers();
    }
    
    /**
     * Get API usage setting
     */
    public function getUseApi() {
        return $this->useApi;
    }
    
    /**
     * Get available server types from API
     */
    public function getServerTypes() {
        global $DEFAULT_SERVER_TYPES;
        
        try {
            // Try primary API endpoint
            $apiUrl = MCJARS_API_URL . '/versions';
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout
            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Check if primary API endpoint successful
            if ($statusCode === 200 && !empty($response)) {
                $data = json_decode($response, true);
                if (isset($data['versions']) && !empty($data['versions'])) {
                    return $data['versions'];
                }
            }
            
            // Try alternate API endpoint if primary failed
            $apiUrl = ALTERNATE_API_URL . '/versions';
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout
            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Check if alternate API endpoint successful
            if ($statusCode === 200 && !empty($response)) {
                $data = json_decode($response, true);
                if (isset($data['versions']) && !empty($data['versions'])) {
                    return $data['versions'];
                }
            }
            
            // Use hardcoded values if both API calls fail
            error_log("API calls failed. Status code: $statusCode, Error: $curlError");
            return array_keys($DEFAULT_SERVER_TYPES);
        } catch (Exception $e) {
            error_log('Error fetching server types: ' . $e->getMessage());
            return array_keys($DEFAULT_SERVER_TYPES);
        }
    }
    
    /**
     * Get available versions for a server type
     */
    public function getVersionsForType($serverType) {
        global $DEFAULT_VERSIONS;
        
        try {
            // Try primary API endpoint
            $apiUrl = MCJARS_API_URL . "/builds/$serverType";
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout
            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Check if primary API endpoint successful
            if ($statusCode === 200 && !empty($response)) {
                $data = json_decode($response, true);
                if (isset($data['versions']) && !empty($data['versions'])) {
                    return $data['versions'];
                }
            }
            
            // Try alternate API endpoint if primary failed
            $apiUrl = ALTERNATE_API_URL . "/builds/$serverType";
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout
            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Check if alternate API endpoint successful
            if ($statusCode === 200 && !empty($response)) {
                $data = json_decode($response, true);
                if (isset($data['versions']) && !empty($data['versions'])) {
                    return $data['versions'];
                }
            }
            
            // Use fallback data if both API calls fail
            error_log("API calls failed for type $serverType. Status code: $statusCode, Error: $curlError");
            return $DEFAULT_VERSIONS;
        } catch (Exception $e) {
            error_log('Error fetching versions: ' . $e->getMessage());
            return $DEFAULT_VERSIONS;
        }
    }
    
    /**
     * Create a server using the API
     */
    public function createServerFromApi($name, $path, $serverType, $version, $minMemory, $maxMemory, $port) {
        try {
            // Create directory if it doesn't exist
            if (!is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    return [false, "Failed to create directory: $path"];
                }
            }
            
            // Download server JAR from API
            $jarFile = "server.jar";
            $downloadUrl = MCJARS_API_URL . "/downloads/$serverType/$version";
            
            $jarPath = "$path/$jarFile";
            
            $fp = fopen($jarPath, 'w+');
            if (!$fp) {
                return [false, "Failed to create JAR file: $jarPath"];
            }
            
            $ch = curl_init($downloadUrl);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);
            
            if ($statusCode !== 200) {
                unlink($jarPath);
                return [false, "Failed to download server JAR file (HTTP $statusCode)"];
            }
            
            // Create eula.txt file
            file_put_contents("$path/eula.txt", "eula=true\n");
            
            // Add server to configuration
            $server = [
                'name' => $name,
                'path' => $path,
                'jar_file' => $jarFile,
                'min_memory' => $minMemory,
                'max_memory' => $maxMemory,
                'port' => $port,
                'server_type' => $serverType,
                'version' => $version
            ];
            
            $this->addServer($server);
            
            return [true, "Server created successfully"];
        } catch (Exception $e) {
            error_log('Error creating server from API: ' . $e->getMessage());
            return [false, $e->getMessage()];
        }
    }
    
    /**
     * Start a Minecraft server
     */
    public function startServer($serverName) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [false, "Server not found"];
        }
        
        // Check if the server is already running
        if ($this->getServerStatus($serverName) === 'running') {
            return [false, "Server is already running"];
        }
        
        // Convert to OS-appropriate path
        $serverPath = convert_path($server['path']);
        $jarFile = $server['jar_file'];
        $jarPath = $serverPath . DIRECTORY_SEPARATOR . $jarFile;
        
        if (!file_exists($jarPath)) {
            return [false, "Server JAR file not found: $jarPath"];
        }
        
        if (IS_WINDOWS) {
            // Create batch file for Windows
            $batchPath = $serverPath . DIRECTORY_SEPARATOR . 'start.bat';
            $batchContent = "@echo off\r\n";
            $batchContent .= "cd /d \"" . $serverPath . "\"\r\n";
            $batchContent .= "java -Xms{$server['min_memory']} -Xmx{$server['max_memory']} -jar \"$jarFile\" nogui\r\n";
            $batchContent .= "pause\r\n";
            
            file_put_contents($batchPath, $batchContent);
            
            // Use start command to run in background on Windows
            // Create a simple PID file to track
            $pidFile = $serverPath . DIRECTORY_SEPARATOR . 'server.pid';
            $command = "start /b cmd /c \"cd /d \"" . $serverPath . "\" && ";
            $command .= "java -Xms{$server['min_memory']} -Xmx{$server['max_memory']} ";
            $command .= "-jar \"$jarFile\" nogui > server.log 2>&1 && echo Process ended > server.end\"";
            
            // Generate a timestamp as a simple identifier 
            $processId = time();
            file_put_contents($pidFile, $processId);
            
            // Windows doesn't return error codes correctly from start, so we just check if the command ran
            pclose(popen($command, 'r'));
            
            // Check if log file is created within a few seconds
            sleep(2);
            $logFile = $serverPath . DIRECTORY_SEPARATOR . 'server.log';
            if (!file_exists($logFile)) {
                unlink($pidFile);
                return [false, "Failed to start server. Java might not be installed or in your PATH."];
            }
            
            return [true, "Server starting in background. Check server logs for details."];
        } else {
            // Unix/Linux version
            $scriptPath = $serverPath . '/start.sh';
            $scriptContent = "#!/bin/bash\n";
            $scriptContent .= "cd " . escapeshellarg($serverPath) . "\n";
            $scriptContent .= "java -Xms{$server['min_memory']} -Xmx{$server['max_memory']} -jar " . 
                            escapeshellarg($jarFile) . " nogui\n";
            
            file_put_contents($scriptPath, $scriptContent);
            chmod($scriptPath, 0755);
            
            // Start the server with nohup for Unix/Linux
            $command = "cd " . escapeshellarg($serverPath) . " && " .
                    "nohup java -Xms{$server['min_memory']} -Xmx{$server['max_memory']} " .
                    "-jar " . escapeshellarg($jarFile) . " nogui > server.log 2>&1 & echo $! > server.pid";
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                return [false, "Failed to start server. Return code: $returnCode"];
            }
            
            // Wait a bit to let the server start
            sleep(2);
            
            return [true, "Server started successfully"];
        }
    }
    
    /**
     * Stop a Minecraft server
     */
    public function stopServer($serverName) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [false, "Server not found"];
        }
        
        // Check if the server is running
        if ($this->getServerStatus($serverName) !== 'running') {
            return [false, "Server is not running"];
        }
        
        // Send stop command to the server
        $result = $this->executeCommand($serverName, 'stop');
        
        // Give it some time to stop
        sleep(5);
        
        // If still running, try more aggressive methods
        if ($this->getServerStatus($serverName) === 'running') {
            $serverPath = convert_path($server['path']);
            $pidFile = $serverPath . DIRECTORY_SEPARATOR . 'server.pid';
            
            if (file_exists($pidFile)) {
                if (IS_WINDOWS) {
                    // Windows doesn't have a reliable way to kill by PID from PHP
                    // Instead, we can try using taskkill to kill java processes
                    exec("taskkill /F /IM java.exe", $output, $returnCode);
                    
                    // Delete the PID file
                    if (file_exists($pidFile)) {
                        unlink($pidFile);
                    }
                } else {
                    // Unix/Linux - use kill command
                    $pid = trim(file_get_contents($pidFile));
                    if (is_numeric($pid)) {
                        exec("kill -15 $pid", $output, $returnCode);
                        sleep(2);
                        
                        // If still running, force kill
                        if ($this->getServerStatus($serverName) === 'running') {
                            exec("kill -9 $pid", $output, $returnCode);
                        }
                        
                        // Remove PID file
                        if (file_exists($pidFile)) {
                            unlink($pidFile);
                        }
                    }
                }
            }
        }
        
        return [true, "Server stopped successfully"];
    }
    
    /**
     * Get the status of a server
     */
    public function getServerStatus($serverName) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return 'unknown';
        }
        
        $serverPath = convert_path($server['path']);
        $pidFile = $serverPath . DIRECTORY_SEPARATOR . 'server.pid';
        $endFile = $serverPath . DIRECTORY_SEPARATOR . 'server.end';
        
        // If end file exists, server has terminated
        if (file_exists($endFile)) {
            // Clean up the files
            unlink($endFile);
            if (file_exists($pidFile)) {
                unlink($pidFile);
            }
            return 'stopped';
        }
        
        // If PID file doesn't exist, server is not running
        if (!file_exists($pidFile)) {
            return 'stopped';
        }
        
        if (IS_WINDOWS) {
            // In Windows, check if the log file is being modified
            $logFile = $serverPath . DIRECTORY_SEPARATOR . 'server.log';
            
            if (!file_exists($logFile)) {
                // No log file, probably not running
                if (file_exists($pidFile)) {
                    unlink($pidFile);
                }
                return 'stopped';
            }
            
            // Check modification time - if log hasn't been modified in 30 seconds,
            // assume server is frozen or not running properly
            $lastModified = filemtime($logFile);
            $timeNow = time();
            
            // If log file is older than 5 minutes and there's no activity,
            // consider it stopped
            if ($timeNow - $lastModified > 300) {
                // Clean up
                if (file_exists($pidFile)) {
                    unlink($pidFile);
                }
                return 'stopped';
            }
            
            // Otherwise, consider it running
            return 'running';
        } else {
            // Unix/Linux systems
            $pid = trim(file_get_contents($pidFile));
            if (!is_numeric($pid)) {
                return 'stopped';
            }
            
            // Check if the process is running
            exec("ps -p $pid -o pid=", $output, $returnCode);
            
            return $returnCode === 0 ? 'running' : 'stopped';
        }
    }
    
    /**
     * Get status for all servers
     */
    public function getAllServerStatuses() {
        $statuses = [];
        foreach ($this->servers as $server) {
            $statuses[$server['name']] = $this->getServerStatus($server['name']);
        }
        return $statuses;
    }
    
    /**
     * Get recent logs from a server
     */
    public function getServerLogs($serverName, $lines = 100) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [];
        }
        
        $serverPath = convert_path($server['path']);
        $logFile = $serverPath . DIRECTORY_SEPARATOR . 'server.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        // Try to get the last N lines in a cross-platform way
        if (IS_WINDOWS) {
            // Windows doesn't have tail, so read file and get last lines manually
            $fileContents = file($logFile, FILE_IGNORE_NEW_LINES);
            if ($fileContents === false) {
                // Error reading file
                return [];
            }
            
            // Get the last N lines
            $totalLines = count($fileContents);
            $startLine = max(0, $totalLines - $lines);
            $output = array_slice($fileContents, $startLine);
            
            return $output;
        } else {
            // Unix/Linux - use tail command
            exec("tail -n $lines " . escapeshellarg($logFile), $output);
            return $output;
        }
    }
    
    /**
     * Execute a command on the server
     */
    public function executeCommand($serverName, $command) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [false, "Server not found"];
        }
        
        // Check if the server is running
        if ($this->getServerStatus($serverName) !== 'running') {
            return [false, "Server is not running"];
        }
        
        $serverPath = convert_path($server['path']);
        
        if (IS_WINDOWS) {
            // Windows approach - append command to a commands.txt file
            // and monitor server log for response
            
            // First approach - try using popen to send directly to the screen session
            $commandFile = $serverPath . DIRECTORY_SEPARATOR . 'command.txt';
            file_put_contents($commandFile, $command . "\r\n");
            
            // Log the command in a history file for reference
            $commandLogFile = $serverPath . DIRECTORY_SEPARATOR . 'command_history.log';
            file_put_contents($commandLogFile, date('Y-m-d H:i:s') . " - " . $command . "\r\n", FILE_APPEND);
            
            // For Windows, we wait for Java to read the command file
            // The server should clear the file after reading
            sleep(1);
            
            return [true, "Command sent: $command (via command file)"];
        } else {
            // Unix/Linux approach - use named pipes
            $serverInput = $serverPath . '/server-input.pipe';
            
            // Create a named pipe if it doesn't exist
            if (!file_exists($serverInput)) {
                exec("mkfifo " . escapeshellarg($serverInput));
                
                // Redirect to server's stdin
                $pidFile = $serverPath . '/server.pid';
                if (file_exists($pidFile)) {
                    $pid = trim(file_get_contents($pidFile));
                    if (is_numeric($pid)) {
                        exec("cat " . escapeshellarg($serverInput) . " > /proc/$pid/fd/0 &");
                    }
                }
            }
            
            // Write the command to the pipe
            file_put_contents($serverInput, $command . "\n");
            
            return [true, "Command executed: $command"];
        }
    }
    
    /**
     * Get the server whitelist
     */
    public function getWhitelist($serverName) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [];
        }
        
        $serverPath = convert_path($server['path']);
        $whitelistFile = $serverPath . DIRECTORY_SEPARATOR . 'whitelist.json';
        
        if (!file_exists($whitelistFile)) {
            return [];
        }
        
        $content = file_get_contents($whitelistFile);
        if ($content === false) {
            return [];
        }
        
        $whitelist = json_decode($content, true);
        return $whitelist ?: [];
    }
    
    /**
     * Get list of banned players
     */
    public function getBannedPlayers($serverName) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [];
        }
        
        $serverPath = convert_path($server['path']);
        $bannedPlayersFile = $serverPath . DIRECTORY_SEPARATOR . 'banned-players.json';
        
        if (!file_exists($bannedPlayersFile)) {
            return [];
        }
        
        $content = file_get_contents($bannedPlayersFile);
        if ($content === false) {
            return [];
        }
        
        $bannedPlayers = json_decode($content, true);
        return $bannedPlayers ?: [];
    }
    
    /**
     * Get list of online players
     */
    public function getPlayers($serverName) {
        // Execute 'list' command and parse the response from the logs
        $this->executeCommand($serverName, 'list');
        sleep(1); // Wait for command to execute
        
        $logs = $this->getServerLogs($serverName, 10);
        $players = [];
        
        foreach ($logs as $line) {
            if (strpos($line, 'There are') !== false && strpos($line, 'players online:') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $playerList = trim($parts[1]);
                    if ($playerList !== '') {
                        $players = array_map('trim', explode(',', $playerList));
                    }
                }
                break;
            }
        }
        
        return $players;
    }
    
    /**
     * Create a backup of the server
     */
    public function createBackup($serverName) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [false, "Server not found"];
        }
        
        $serverPath = convert_path($server['path']);
        
        // Create backup directory if it doesn't exist
        $backupDir = $serverPath . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($backupDir)) {
            if (!mkdir($backupDir, 0755, true)) {
                return [false, "Failed to create backup directory"];
            }
        }
        
        // Generate backup name with timestamp
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "backup_$timestamp";
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $backupName . ".zip";
        
        if (IS_WINDOWS) {
            // Use built-in PHP functionality or Windows zip command
            // Check if ZipArchive is available
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($backupPath, ZipArchive::CREATE) === TRUE) {
                    // Add files to the zip recursively
                    $this->addFilesToZip($zip, $serverPath, '', ['backups', 'server.pid', 'server.log', 'server-input.pipe', 'command.txt']);
                    $zip->close();
                    return [true, "Backup created successfully: $backupName"];
                } else {
                    return [false, "Failed to create ZIP archive"];
                }
            } else {
                // Try to use PowerShell's Compress-Archive
                $source = $serverPath . DIRECTORY_SEPARATOR . '*';
                $exclude = 'backups,server.pid,server.log,server-input.pipe,command.txt';
                $command = "powershell -Command \"Get-ChildItem -Path '$source' -Exclude $exclude | Compress-Archive -DestinationPath '$backupPath' -Force\"";
                
                exec($command, $output, $returnCode);
                
                if ($returnCode !== 0) {
                    return [false, "Failed to create backup. Check if PowerShell is available."];
                }
                
                return [true, "Backup created successfully: $backupName"];
            }
        } else {
            // Unix/Linux approach with zip command
            $excludes = ['backups/*', 'server.pid', 'server.log', 'server-input.pipe'];
            $excludeArgs = '';
            foreach ($excludes as $exclude) {
                $excludeArgs .= " --exclude=" . escapeshellarg($exclude);
            }
            
            $command = "cd " . escapeshellarg($serverPath) . " && " .
                      "zip -r " . escapeshellarg($backupPath) . " .$excludeArgs";
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                return [false, "Failed to create backup. Return code: $returnCode"];
            }
            
            return [true, "Backup created successfully: $backupName"];
        }
    }
    
    /**
     * Add files to a ZipArchive recursively
     */
    private function addFilesToZip($zip, $baseFolder, $relativePath, $excludes = []) {
        $folder = $baseFolder;
        if ($relativePath !== '') {
            $folder .= DIRECTORY_SEPARATOR . $relativePath;
        }
        
        $files = scandir($folder);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            // Skip excluded files/folders
            if (in_array($file, $excludes)) {
                continue;
            }
            
            $filePath = $folder . DIRECTORY_SEPARATOR . $file;
            $zipPath = $relativePath !== '' ? $relativePath . DIRECTORY_SEPARATOR . $file : $file;
            
            if (is_dir($filePath)) {
                $this->addFilesToZip($zip, $baseFolder, $zipPath, $excludes);
            } else {
                $zip->addFile($filePath, $zipPath);
            }
        }
    }
    
    /**
     * Get list of available backups for a server
     */
    public function getBackups($serverName) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [];
        }
        
        $serverPath = convert_path($server['path']);
        $backupDir = $serverPath . DIRECTORY_SEPARATOR . 'backups';
        
        if (!is_dir($backupDir)) {
            return [];
        }
        
        $backups = [];
        $pattern = $backupDir . DIRECTORY_SEPARATOR . 'backup_*.zip';
        $files = glob($pattern);
        
        if ($files === false) {
            // Handle glob error
            error_log("Error using glob on pattern: $pattern");
            return [];
        }
        
        foreach ($files as $file) {
            $fileName = basename($file);
            $size = filesize($file);
            $time = filemtime($file);
            
            $backups[] = [
                'name' => substr($fileName, 0, -4), // Remove .zip extension
                'file' => $fileName,
                'size' => $this->formatSize($size),
                'time' => date('Y-m-d H:i:s', $time)
            ];
        }
        
        // Sort by time (newest first)
        usort($backups, function($a, $b) {
            return strcmp($b['time'], $a['time']);
        });
        
        return $backups;
    }
    
    /**
     * Restore a server from a backup
     */
    public function restoreBackup($serverName, $backupName) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [false, "Server not found"];
        }
        
        // Check if server is running
        if ($this->getServerStatus($serverName) === 'running') {
            return [false, "Server must be stopped before restoring a backup"];
        }
        
        $backupFile = $server['path'] . "/backups/$backupName.zip";
        if (!file_exists($backupFile)) {
            return [false, "Backup file not found"];
        }
        
        // Create temporary directory
        $tempDir = $server['path'] . "/temp_" . uniqid();
        if (!mkdir($tempDir, 0755, true)) {
            return [false, "Failed to create temporary directory"];
        }
        
        // Extract backup to temporary directory
        $command = "unzip -q " . escapeshellarg($backupFile) . " -d " . escapeshellarg($tempDir);
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            // Clean up
            $this->recursiveDelete($tempDir);
            return [false, "Failed to extract backup. Return code: $returnCode"];
        }
        
        // Preserve certain files
        $preserveFiles = ['server.pid', 'server.log', 'backups'];
        foreach ($preserveFiles as $file) {
            $sourcePath = $server['path'] . "/$file";
            $destPath = $tempDir . "/$file";
            
            if (file_exists($sourcePath)) {
                if (is_dir($sourcePath)) {
                    // For directories like backups
                    $this->recursiveDelete($destPath);
                    rename($sourcePath, $destPath);
                } else {
                    // For files
                    copy($sourcePath, $destPath);
                }
            }
        }
        
        // Delete original server directory contents (except for temp directory)
        $entries = scandir($server['path']);
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || $entry === basename($tempDir)) {
                continue;
            }
            
            $path = $server['path'] . "/$entry";
            if (is_dir($path)) {
                $this->recursiveDelete($path);
            } else {
                unlink($path);
            }
        }
        
        // Move files from temp directory to server directory
        $tempEntries = scandir($tempDir);
        foreach ($tempEntries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            
            $sourcePath = $tempDir . "/$entry";
            $destPath = $server['path'] . "/$entry";
            
            rename($sourcePath, $destPath);
        }
        
        // Clean up
        rmdir($tempDir);
        
        return [true, "Server restored successfully from backup: $backupName"];
    }
    
    /**
     * Get server.properties as a dictionary
     */
    public function getServerProperties($serverName) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [];
        }
        
        $propertiesFile = $server['path'] . '/server.properties';
        if (!file_exists($propertiesFile)) {
            return [];
        }
        
        $properties = [];
        $lines = file($propertiesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $properties[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        return $properties;
    }
    
    /**
     * Update server.properties with new values
     */
    public function updateServerProperties($serverName, $properties) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return [false, "Server not found"];
        }
        
        $propertiesFile = $server['path'] . '/server.properties';
        if (!file_exists($propertiesFile)) {
            return [false, "server.properties file not found"];
        }
        
        // Read existing properties
        $existingProperties = $this->getServerProperties($serverName);
        
        // Update properties
        $existingProperties = array_merge($existingProperties, $properties);
        
        // Write back to file
        $content = '';
        foreach ($existingProperties as $key => $value) {
            $content .= "$key=$value\n";
        }
        
        if (file_put_contents($propertiesFile, $content) === false) {
            return [false, "Failed to write to server.properties"];
        }
        
        return [true, "Server properties updated successfully"];
    }
    
    /**
     * Format file size
     */
    private function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Recursively delete a directory
     */
    private function recursiveDelete($path) {
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    $objPath = $path . '/' . $object;
                    if (is_dir($objPath)) {
                        $this->recursiveDelete($objPath);
                    } else {
                        unlink($objPath);
                    }
                }
            }
            rmdir($path);
        }
    }
}
?>