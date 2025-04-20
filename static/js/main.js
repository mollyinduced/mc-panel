/**
 * Minecraft Server Control Panel - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize toasts
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl);
    });

    // Server status refresh
    refreshServerStatuses();
    setInterval(refreshServerStatuses, 10000);

    // Start server buttons
    document.querySelectorAll('.btn-start-server').forEach(button => {
        button.addEventListener('click', function() {
            const serverName = this.getAttribute('data-server');
            startServer(serverName);
        });
    });

    // Stop server buttons
    document.querySelectorAll('.btn-stop-server').forEach(button => {
        button.addEventListener('click', function() {
            const serverName = this.getAttribute('data-server');
            stopServer(serverName);
        });
    });

    // Refresh logs button
    const refreshLogsBtn = document.getElementById('refresh-logs');
    if (refreshLogsBtn) {
        refreshLogsBtn.addEventListener('click', function() {
            const serverName = this.getAttribute('data-server');
            refreshLogs(serverName);
        });
    }

    // Auto-refresh logs if on the logs page
    if (document.getElementById('server-logs')) {
        const serverName = document.getElementById('server-logs').getAttribute('data-server');
        setInterval(() => refreshLogs(serverName), 5000);
    }

    // Execute command form
    const commandForm = document.getElementById('command-form');
    if (commandForm) {
        commandForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const serverName = this.getAttribute('data-server');
            const command = document.getElementById('command-input').value;
            executeCommand(serverName, command);
        });
    }

    // Create backup button
    const createBackupBtn = document.getElementById('create-backup');
    if (createBackupBtn) {
        createBackupBtn.addEventListener('click', function() {
            const serverName = this.getAttribute('data-server');
            createBackup(serverName);
        });
    }

    // Restore backup buttons
    document.querySelectorAll('.btn-restore-backup').forEach(button => {
        button.addEventListener('click', function() {
            const serverName = this.getAttribute('data-server');
            const backupName = this.getAttribute('data-backup');
            if (confirm(`Are you sure you want to restore from backup ${backupName}? This will overwrite the current server files.`)) {
                restoreBackup(serverName, backupName);
            }
        });
    });

    // Player management commands
    document.querySelectorAll('.player-action').forEach(button => {
        button.addEventListener('click', function() {
            const serverName = this.getAttribute('data-server');
            const player = this.getAttribute('data-player');
            const action = this.getAttribute('data-action');
            
            let command = '';
            switch (action) {
                case 'kick':
                    command = `kick ${player}`;
                    break;
                case 'ban':
                    command = `ban ${player}`;
                    break;
                case 'op':
                    command = `op ${player}`;
                    break;
                case 'deop':
                    command = `deop ${player}`;
                    break;
                case 'whitelist-add':
                    command = `whitelist add ${player}`;
                    break;
                case 'whitelist-remove':
                    command = `whitelist remove ${player}`;
                    break;
                case 'pardon':
                    command = `pardon ${player}`;
                    break;
            }
            
            if (command) {
                executeCommand(serverName, command);
            }
        });
    });
});

/**
 * Refresh server statuses
 */
function refreshServerStatuses() {
    fetch('/api/server/status')
        .then(response => response.json())
        .then(data => {
            for (const [server, status] of Object.entries(data)) {
                updateServerStatus(server, status);
            }
        })
        .catch(error => console.error('Error fetching server statuses:', error));
}

/**
 * Update server status UI
 */
function updateServerStatus(serverName, status) {
    const statusBadge = document.querySelector(`.server-status[data-server="${serverName}"]`);
    const startBtn = document.querySelector(`.btn-start-server[data-server="${serverName}"]`);
    const stopBtn = document.querySelector(`.btn-stop-server[data-server="${serverName}"]`);
    
    if (statusBadge) {
        statusBadge.textContent = status === 'running' ? 'Online' : 'Offline';
        statusBadge.classList.remove('bg-success', 'bg-danger');
        statusBadge.classList.add(status === 'running' ? 'bg-success' : 'bg-danger');
    }
    
    if (startBtn) {
        startBtn.disabled = status === 'running';
    }
    
    if (stopBtn) {
        stopBtn.disabled = status === 'stopped';
    }
}

/**
 * Start a Minecraft server
 */
function startServer(serverName) {
    showToast('Starting server...', 'info');
    
    fetch(`/api/server/start/${serverName}`, {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            updateServerStatus(serverName, 'running');
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error starting server:', error);
        showToast('Failed to start server', 'danger');
    });
}

/**
 * Stop a Minecraft server
 */
function stopServer(serverName) {
    showToast('Stopping server...', 'info');
    
    fetch(`/api/server/stop/${serverName}`, {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            updateServerStatus(serverName, 'stopped');
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error stopping server:', error);
        showToast('Failed to stop server', 'danger');
    });
}

/**
 * Refresh server logs
 */
function refreshLogs(serverName) {
    fetch(`/api/server/logs/${serverName}`)
        .then(response => response.json())
        .then(data => {
            const logsContainer = document.getElementById('server-logs');
            if (logsContainer) {
                logsContainer.innerHTML = '';
                data.logs.forEach(log => {
                    const logLine = document.createElement('div');
                    logLine.className = 'log-line';
                    
                    // Apply styling based on log level
                    if (log.includes('ERROR') || log.includes('SEVERE')) {
                        logLine.classList.add('text-danger');
                    } else if (log.includes('WARN')) {
                        logLine.classList.add('text-warning');
                    } else if (log.includes('INFO')) {
                        logLine.classList.add('text-info');
                    }
                    
                    logLine.textContent = log;
                    logsContainer.appendChild(logLine);
                });
                
                // Scroll to the bottom
                logsContainer.scrollTop = logsContainer.scrollHeight;
            }
        })
        .catch(error => console.error('Error fetching logs:', error));
}

/**
 * Execute a command on the server
 */
function executeCommand(serverName, command) {
    fetch(`/api/server/command/${serverName}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ command: command }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Command executed', 'success');
            
            // Clear the command input if it exists
            const commandInput = document.getElementById('command-input');
            if (commandInput) {
                commandInput.value = '';
            }
            
            // Refresh logs if we're on the logs page
            if (document.getElementById('server-logs')) {
                setTimeout(() => refreshLogs(serverName), 1000);
            }
            
            // If on player management page, refresh after a delay
            if (document.querySelector('.player-management')) {
                setTimeout(() => window.location.reload(), 2000);
            }
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error executing command:', error);
        showToast('Failed to execute command', 'danger');
    });
}

/**
 * Create a backup of the server
 */
function createBackup(serverName) {
    showToast('Creating backup...', 'info');
    
    fetch(`/api/server/backup/create/${serverName}`, {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Refresh the page to show the new backup
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error creating backup:', error);
        showToast('Failed to create backup', 'danger');
    });
}

/**
 * Restore from a backup
 */
function restoreBackup(serverName, backupName) {
    showToast('Restoring from backup...', 'info');
    
    fetch(`/api/server/backup/restore/${serverName}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ backup_name: backupName }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Refresh the page after a delay
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error restoring backup:', error);
        showToast('Failed to restore backup', 'danger');
    });
}

/**
 * Show toast message
 */
function showToast(message, type) {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create new toast
    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Minecraft Control Panel</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-${type} text-white">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.innerHTML += toastHTML;
    
    // Initialize and show the toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    
    // Remove toast after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}
