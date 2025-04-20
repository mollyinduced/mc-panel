        <div class="toast-container"></div>
    </div><!-- /.container -->
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Main JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle global API setting toggle
        const globalUseApiCheckbox = document.getElementById('global_use_api');
        if (globalUseApiCheckbox) {
            globalUseApiCheckbox.addEventListener('change', function() {
                const useApi = this.checked;
                
                // Update server manager setting via API
                fetch('<?= base_url("?route=api/settings") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ use_api: useApi }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(`API ${useApi ? 'enabled' : 'disabled'} successfully`, 'success');
                    } else {
                        showToast('Failed to update API setting', 'danger');
                        // Reset checkbox to previous state
                        this.checked = !useApi;
                    }
                })
                .catch(error => {
                    console.error('Error updating API setting:', error);
                    showToast('Failed to update API setting', 'danger');
                    // Reset checkbox to previous state
                    this.checked = !useApi;
                });
            });
        }
        
        // Handle API checkbox toggle in server form
        const useApiCheckbox = document.getElementById('use_api');
        const apiSection = document.getElementById('api-section');
        const localSection = document.getElementById('local-section');
        const serverTypeSelect = document.getElementById('server_type');
        const versionSelect = document.getElementById('version');
        const jarFileInput = document.getElementById('jar_file');

        if (useApiCheckbox) {
            useApiCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    apiSection.classList.remove('d-none');
                    localSection.classList.add('d-none');
                    jarFileInput.removeAttribute('required');
                } else {
                    apiSection.classList.add('d-none');
                    localSection.classList.remove('d-none');
                    jarFileInput.setAttribute('required', 'required');
                }
            });
        }
        
        // Handle server type selection
        if (serverTypeSelect) {
            serverTypeSelect.addEventListener('change', function() {
                const serverType = this.value;
                
                if (serverType) {
                    // Clear versions
                    versionSelect.innerHTML = '<option value="">Loading versions...</option>';
                    versionSelect.disabled = true;
                    
                    // Fetch versions for selected server type
                    fetch(`<?= base_url("?route=api/versions") ?>&type=${serverType}`)
                        .then(response => response.json())
                        .then(data => {
                            versionSelect.innerHTML = '<option value="">Select a version...</option>';
                            
                            // Sort versions by type (RELEASE first, then others)
                            const versions = data.versions.sort((a, b) => {
                                if (a.type === 'RELEASE' && b.type !== 'RELEASE') return -1;
                                if (a.type !== 'RELEASE' && b.type === 'RELEASE') return 1;
                                return 0;
                            });
                            
                            // Add versions to the select
                            versions.forEach(version => {
                                const option = document.createElement('option');
                                option.value = version.id;
                                option.text = `${version.id} (${version.type}, Java ${version.java})`;
                                versionSelect.appendChild(option);
                            });
                            
                            versionSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error fetching versions:', error);
                            versionSelect.innerHTML = '<option value="">Error loading versions</option>';
                            versionSelect.disabled = true;
                        });
                } else {
                    versionSelect.innerHTML = '<option value="">Select a server type first...</option>';
                    versionSelect.disabled = true;
                }
            });
        }
        
        // Handle server start/stop
        document.querySelectorAll('.btn-start-server').forEach(button => {
            button.addEventListener('click', function() {
                const serverName = this.getAttribute('data-server');
                startServer(serverName);
            });
        });
        
        document.querySelectorAll('.btn-stop-server').forEach(button => {
            button.addEventListener('click', function() {
                const serverName = this.getAttribute('data-server');
                stopServer(serverName);
            });
        });
        
        // Auto-refresh server statuses
        if (document.querySelector('.server-status')) {
            refreshServerStatuses();
            setInterval(refreshServerStatuses, 10000); // Every 10 seconds
        }
        
        // Auto-refresh logs
        const logsContainer = document.getElementById('server-logs');
        if (logsContainer) {
            const serverName = logsContainer.getAttribute('data-server');
            refreshLogs(serverName);
            setInterval(() => refreshLogs(serverName), 5000); // Every 5 seconds
        }
        
        // Command input
        const commandForm = document.getElementById('command-form');
        if (commandForm) {
            commandForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const serverName = this.getAttribute('data-server');
                const commandInput = document.getElementById('command-input');
                const command = commandInput.value.trim();
                
                if (command) {
                    executeCommand(serverName, command);
                    commandInput.value = '';
                }
            });
        }
    });
    
    // Refresh server statuses
    function refreshServerStatuses() {
        const serverCards = document.querySelectorAll('.server-status');
        
        serverCards.forEach(statusBadge => {
            const serverName = statusBadge.getAttribute('data-server');
            
            fetch(`<?= base_url("?route=api/server/status") ?>&name=${serverName}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'running') {
                        statusBadge.innerHTML = '<span class="bg-success">Online</span>';
                        // Enable/disable buttons
                        document.querySelectorAll(`.btn-start-server[data-server="${serverName}"]`).forEach(btn => {
                            btn.disabled = true;
                        });
                        document.querySelectorAll(`.btn-stop-server[data-server="${serverName}"]`).forEach(btn => {
                            btn.disabled = false;
                        });
                    } else {
                        statusBadge.innerHTML = '<span class="bg-danger">Offline</span>';
                        // Enable/disable buttons
                        document.querySelectorAll(`.btn-start-server[data-server="${serverName}"]`).forEach(btn => {
                            btn.disabled = false;
                        });
                        document.querySelectorAll(`.btn-stop-server[data-server="${serverName}"]`).forEach(btn => {
                            btn.disabled = true;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching server status:', error);
                });
        });
    }
    
    // Start server
    function startServer(serverName) {
        const startButton = document.querySelector(`.btn-start-server[data-server="${serverName}"]`);
        if (startButton) {
            startButton.disabled = true;
            startButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Starting...';
        }
        
        fetch(`<?= base_url("?route=api/server/start") ?>&name=${serverName}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Server ${serverName} started successfully`, 'success');
                    refreshServerStatuses();
                } else {
                    showToast(`Failed to start server: ${data.message}`, 'danger');
                    if (startButton) {
                        startButton.disabled = false;
                        startButton.innerHTML = '<i class="bi bi-play-fill"></i> Start';
                    }
                }
            })
            .catch(error => {
                console.error('Error starting server:', error);
                showToast('Failed to start server', 'danger');
                if (startButton) {
                    startButton.disabled = false;
                    startButton.innerHTML = '<i class="bi bi-play-fill"></i> Start';
                }
            });
    }
    
    // Stop server
    function stopServer(serverName) {
        const stopButton = document.querySelector(`.btn-stop-server[data-server="${serverName}"]`);
        if (stopButton) {
            stopButton.disabled = true;
            stopButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Stopping...';
        }
        
        fetch(`<?= base_url("?route=api/server/stop") ?>&name=${serverName}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Server ${serverName} stopped successfully`, 'success');
                    refreshServerStatuses();
                } else {
                    showToast(`Failed to stop server: ${data.message}`, 'danger');
                    if (stopButton) {
                        stopButton.disabled = false;
                        stopButton.innerHTML = '<i class="bi bi-stop-fill"></i> Stop';
                    }
                }
            })
            .catch(error => {
                console.error('Error stopping server:', error);
                showToast('Failed to stop server', 'danger');
                if (stopButton) {
                    stopButton.disabled = false;
                    stopButton.innerHTML = '<i class="bi bi-stop-fill"></i> Stop';
                }
            });
    }
    
    // Refresh logs
    function refreshLogs(serverName) {
        const logsContainer = document.getElementById('server-logs');
        if (!logsContainer) return;
        
        fetch(`<?= base_url("?route=api/server/logs") ?>&name=${serverName}`)
            .then(response => response.json())
            .then(data => {
                if (data.logs && data.logs.length > 0) {
                    logsContainer.innerHTML = data.logs.join('\n');
                    // Auto-scroll to bottom
                    logsContainer.scrollTop = logsContainer.scrollHeight;
                }
            })
            .catch(error => {
                console.error('Error refreshing logs:', error);
            });
    }
    
    // Execute command
    function executeCommand(serverName, command) {
        fetch(`<?= base_url("?route=api/server/command") ?>&name=${serverName}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ command: command }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`Command executed: ${command}`, 'success');
                // Refresh logs immediately
                setTimeout(() => refreshLogs(serverName), 500);
            } else {
                showToast(`Failed to execute command: ${data.message}`, 'danger');
            }
        })
        .catch(error => {
            console.error('Error executing command:', error);
            showToast('Failed to execute command', 'danger');
        });
    }
    
    // Show toast message
    function showToast(message, type) {
        const toastContainer = document.querySelector('.toast-container');
        
        const toast = document.createElement('div');
        toast.classList.add('toast', 'show', 'mb-2');
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        const toastClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
        toast.classList.add(toastClass);
        
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">Minecraft Panel</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto-remove toast after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 5000);
    }
    </script>
</body>
</html>