<div class="row">
    <div class="col">
        <h1 class="display-5 mb-4">
            <i class="bi bi-file-text text-primary me-2"></i>
            Server Logs: <?= htmlspecialchars($server['name']) ?>
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
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Console Output</h5>
            </div>
            <div class="card-body">
                <pre id="server-logs" class="logs" data-server="<?= htmlspecialchars($server['name']) ?>"><?= !empty($logs) ? implode("\n", $logs) : "No logs available" ?></pre>
            </div>
            
            <div class="card-footer">
                <form id="command-form" data-server="<?= htmlspecialchars($server['name']) ?>">
                    <div class="input-group">
                        <span class="input-group-text">&gt;</span>
                        <input type="text" class="form-control" id="command-input" placeholder="Enter command..."
                               <?= (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running') ? 'disabled' : '' ?>>
                        <button class="btn btn-primary" type="submit"
                                <?= (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running') ? 'disabled' : '' ?>>
                            <i class="bi bi-send"></i> Send
                        </button>
                    </div>
                    <div class="form-text">
                        <?php if (isset($statuses[$server['name']]) && $statuses[$server['name']] === 'running'): ?>
                            Type a command and press Enter to send it to the server
                        <?php else: ?>
                            Server must be running to send commands
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>