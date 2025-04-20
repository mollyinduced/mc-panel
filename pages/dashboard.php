<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-5 mb-3">
            <i class="bi bi-hdd-stack text-primary me-2"></i>
            Server Dashboard
        </h1>
        <p class="lead text-muted">Manage your Minecraft servers</p>
    </div>
    <div class="col-md-4 text-end align-self-center">
        <div class="form-check form-switch d-inline-block">
            <input class="form-check-input" type="checkbox" role="switch" id="global_use_api" <?= $useApi ? 'checked' : '' ?>>
            <label class="form-check-label" for="global_use_api">Use API by default</label>
        </div>
        <a href="<?= base_url('?route=add-server') ?>" class="btn btn-primary ms-3">
            <i class="bi bi-plus-circle me-2"></i>Add Server
        </a>
    </div>
</div>

<?php if (empty($servers)): ?>
    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading">No servers configured</h4>
        <p>You don't have any Minecraft servers configured yet. Click the button below to add your first server.</p>
        <hr>
        <p class="mb-0">
            <a href="<?= base_url('?route=add-server') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Server
            </a>
        </p>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($servers as $server): ?>
            <div class="col">
                <div class="card server-card h-100 border-secondary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($server['name']) ?></h5>
                        <span class="badge server-status" data-server="<?= htmlspecialchars($server['name']) ?>">
                            <?php if (isset($statuses[$server['name']]) && $statuses[$server['name']] === 'running'): ?>
                                <span class="bg-success">Online</span>
                            <?php else: ?>
                                <span class="bg-danger">Offline</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><strong>Path:</strong> <?= htmlspecialchars($server['path']) ?></p>
                        <p><strong>JAR:</strong> <?= htmlspecialchars($server['jar_file']) ?></p>
                        <p><strong>Memory:</strong> <?= htmlspecialchars($server['min_memory']) ?> - <?= htmlspecialchars($server['max_memory']) ?></p>
                        <p><strong>Port:</strong> <?= htmlspecialchars($server['port']) ?></p>
                        <?php if (isset($server['server_type']) && isset($server['version'])): ?>
                        <p><strong>Type:</strong> <?= htmlspecialchars($server['server_type']) ?> (<?= htmlspecialchars($server['version']) ?>)</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex flex-wrap server-actions justify-content-between gap-2">
                            <div>
                                <button class="btn btn-success btn-sm btn-start-server" 
                                      data-server="<?= htmlspecialchars($server['name']) ?>"
                                      <?= (isset($statuses[$server['name']]) && $statuses[$server['name']] === 'running') ? 'disabled' : '' ?>>
                                    <i class="bi bi-play-fill"></i> Start
                                </button>
                                <button class="btn btn-danger btn-sm btn-stop-server" 
                                      data-server="<?= htmlspecialchars($server['name']) ?>"
                                      <?= (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running') ? 'disabled' : '' ?>>
                                    <i class="bi bi-stop-fill"></i> Stop
                                </button>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                       type="button" 
                                       data-bs-toggle="dropdown" 
                                       aria-expanded="false">
                                    <i class="bi bi-gear"></i> Manage
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url("?route=server-logs&name={$server['name']}") ?>">
                                            <i class="bi bi-file-text me-2"></i>View Logs
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url("?route=player-management&name={$server['name']}") ?>">
                                            <i class="bi bi-people me-2"></i>Players
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url("?route=server-properties&name={$server['name']}") ?>">
                                            <i class="bi bi-sliders me-2"></i>Settings
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url("?route=backups&name={$server['name']}") ?>">
                                            <i class="bi bi-archive me-2"></i>Backups
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url("?route=edit-server&name={$server['name']}") ?>">
                                            <i class="bi bi-pencil me-2"></i>Edit Server
                                        </a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#deleteModal<?= str_replace([' ', '.'], ['_', '_'], $server['name']) ?>">
                                            <i class="bi bi-trash me-2"></i>Delete Server
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delete confirmation modal -->
            <div class="modal fade" id="deleteModal<?= str_replace([' ', '.'], ['_', '_'], $server['name']) ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the server "<?= htmlspecialchars($server['name']) ?>"?</p>
                            <p class="text-danger">This will only remove the server from the control panel, not delete the actual server files.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <a href="<?= base_url("?route=delete-server&name={$server['name']}") ?>" class="btn btn-danger">Delete Server</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>