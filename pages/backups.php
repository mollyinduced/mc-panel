<div class="row">
    <div class="col">
        <h1 class="display-5 mb-4">
            <i class="bi bi-archive text-primary me-2"></i>
            Backups: <?= htmlspecialchars($server['name']) ?>
        </h1>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <a href="<?= base_url() ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
        <span class="ms-3">
            <span class="badge server-status" data-server="<?= htmlspecialchars($server['name']) ?>">
                <?php if (isset($statuses[$server['name']]) && $statuses[$server['name']] === 'running'): ?>
                    <span class="bg-success">Online</span>
                <?php else: ?>
                    <span class="bg-danger">Offline</span>
                <?php endif; ?>
            </span>
        </span>
        <button id="create-backup-btn" class="btn btn-primary float-end">
            <i class="bi bi-plus-circle me-2"></i>Create Backup
        </button>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Available Backups</h5>
            </div>
            <div class="card-body">
                <?php if (empty($backups)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No backups available for this server
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Backup Name</th>
                                    <th>Size</th>
                                    <th>Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($backup['name']) ?></td>
                                        <td><?= htmlspecialchars($backup['size']) ?></td>
                                        <td><?= htmlspecialchars($backup['time']) ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-primary restore-backup-btn" 
                                                   data-backup="<?= htmlspecialchars($backup['name']) ?>"
                                                   data-server="<?= htmlspecialchars($server['name']) ?>"
                                                   <?= (isset($statuses[$server['name']]) && $statuses[$server['name']] === 'running') ? 'disabled' : '' ?>>
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (isset($statuses[$server['name']]) && $statuses[$server['name']] === 'running'): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Server must be stopped before restoring a backup
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const createBackupBtn = document.getElementById('create-backup-btn');
    if (createBackupBtn) {
        createBackupBtn.addEventListener('click', function() {
            const serverName = "<?= htmlspecialchars($server['name']) ?>";
            
            // Disable button and show loading
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...';
            
            // Create backup via API
            fetch(`<?= base_url("?route=api/server/backup/create") ?>&name=${serverName}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Backup created successfully', 'success');
                        // Reload page after 1 second
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast(`Failed to create backup: ${data.message}`, 'danger');
                        // Re-enable button
                        this.disabled = false;
                        this.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Create Backup';
                    }
                })
                .catch(error => {
                    console.error('Error creating backup:', error);
                    showToast('Failed to create backup', 'danger');
                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Create Backup';
                });
        });
    }
    
    // Restore backup buttons
    document.querySelectorAll('.restore-backup-btn').forEach(button => {
        button.addEventListener('click', function() {
            const serverName = this.getAttribute('data-server');
            const backupName = this.getAttribute('data-backup');
            
            if (confirm(`Are you sure you want to restore the server from backup "${backupName}"? This will replace all current server files.`)) {
                // Disable button and show loading
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Restoring...';
                
                // Restore backup via API
                fetch(`<?= base_url("?route=api/server/backup/restore") ?>&name=${serverName}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ backup_name: backupName }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Backup restored successfully', 'success');
                        // Reload page after 1 second
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast(`Failed to restore backup: ${data.message}`, 'danger');
                        // Re-enable button
                        this.disabled = false;
                        this.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Restore';
                    }
                })
                .catch(error => {
                    console.error('Error restoring backup:', error);
                    showToast('Failed to restore backup', 'danger');
                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Restore';
                });
            }
        });
    });
});
</script>