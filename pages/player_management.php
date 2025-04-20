<div class="row">
    <div class="col">
        <h1 class="display-5 mb-4">
            <i class="bi bi-people text-primary me-2"></i>
            Player Management: <?= htmlspecialchars($server['name']) ?>
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
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Online Players</h5>
            </div>
            <div class="card-body">
                <?php if (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running'): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Server is offline
                    </div>
                <?php elseif (empty($onlinePlayers)): ?>
                    <p>No players online</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($onlinePlayers as $player): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($player) ?>
                                <div>
                                    <button class="btn btn-sm btn-outline-warning player-command" 
                                           data-command="kick <?= htmlspecialchars($player) ?>"
                                           data-server="<?= htmlspecialchars($server['name']) ?>">
                                        <i class="bi bi-box-arrow-right"></i> Kick
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <form id="command-form" data-server="<?= htmlspecialchars($server['name']) ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" id="command-input" placeholder="Command..."
                               <?= (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running') ? 'disabled' : '' ?>>
                        <button class="btn btn-primary" type="submit"
                                <?= (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running') ? 'disabled' : '' ?>>
                            <i class="bi bi-send"></i> Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Whitelist</h5>
            </div>
            <div class="card-body">
                <?php if (empty($whitelist)): ?>
                    <p>No players whitelisted</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($whitelist as $player): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= isset($player['name']) ? htmlspecialchars($player['name']) : 'Unknown' ?>
                                <div>
                                    <button class="btn btn-sm btn-outline-danger player-command" 
                                           data-command="whitelist remove <?= isset($player['name']) ? htmlspecialchars($player['name']) : '' ?>"
                                           data-server="<?= htmlspecialchars($server['name']) ?>">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <form id="whitelist-form" data-server="<?= htmlspecialchars($server['name']) ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" id="whitelist-input" placeholder="Player name"
                               <?= (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running') ? 'disabled' : '' ?>>
                        <button class="btn btn-success" type="submit"
                                <?= (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running') ? 'disabled' : '' ?>>
                            <i class="bi bi-plus"></i> Add
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Banned Players</h5>
            </div>
            <div class="card-body">
                <?php if (empty($bannedPlayers)): ?>
                    <p>No players banned</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($bannedPlayers as $player): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= isset($player['name']) ? htmlspecialchars($player['name']) : 'Unknown' ?>
                                <div>
                                    <button class="btn btn-sm btn-outline-success player-command" 
                                           data-command="pardon <?= isset($player['name']) ? htmlspecialchars($player['name']) : '' ?>"
                                           data-server="<?= htmlspecialchars($server['name']) ?>">
                                        <i class="bi bi-check"></i> Pardon
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <form id="ban-form" data-server="<?= htmlspecialchars($server['name']) ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" id="ban-input" placeholder="Player name"
                               <?= (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running') ? 'disabled' : '' ?>>
                        <button class="btn btn-danger" type="submit"
                                <?= (!isset($statuses[$server['name']]) || $statuses[$server['name']] !== 'running') ? 'disabled' : '' ?>>
                            <i class="bi bi-ban"></i> Ban
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Player command buttons
    document.querySelectorAll('.player-command').forEach(button => {
        button.addEventListener('click', function() {
            const command = this.getAttribute('data-command');
            const serverName = this.getAttribute('data-server');
            
            if (confirm(`Are you sure you want to execute: ${command}?`)) {
                executeCommand(serverName, command);
            }
        });
    });
    
    // Whitelist form
    const whitelistForm = document.getElementById('whitelist-form');
    if (whitelistForm) {
        whitelistForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const serverName = this.getAttribute('data-server');
            const playerName = document.getElementById('whitelist-input').value.trim();
            
            if (playerName) {
                executeCommand(serverName, `whitelist add ${playerName}`);
                document.getElementById('whitelist-input').value = '';
                
                // Reload after 2 seconds
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            }
        });
    }
    
    // Ban form
    const banForm = document.getElementById('ban-form');
    if (banForm) {
        banForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const serverName = this.getAttribute('data-server');
            const playerName = document.getElementById('ban-input').value.trim();
            
            if (playerName) {
                executeCommand(serverName, `ban ${playerName}`);
                document.getElementById('ban-input').value = '';
                
                // Reload after 2 seconds
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            }
        });
    }
});
</script>