<div class="row">
    <div class="col">
        <h1 class="display-5 mb-4">
            <?php if (isset($server)): ?>
                <i class="bi bi-pencil text-primary me-2"></i>
                Edit Server: <?= htmlspecialchars($server['name']) ?>
            <?php else: ?>
                <i class="bi bi-plus-circle text-primary me-2"></i>
                Add New Server
            <?php endif; ?>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body">
                <!-- Server configuration form -->
                <form method="POST" action="<?= $_SERVER['REQUEST_URI'] ?>" id="server-form">
                    <?php if (!isset($server)): ?>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="use_api" name="use_api" <?= isset($useApi) && $useApi ? 'checked' : '' ?>>
                            <label class="form-check-label" for="use_api">Use API to download server files</label>
                        </div>
                        <div class="form-text">
                            When enabled, the server JAR file will be automatically downloaded from the MCJars API
                        </div>
                    </div>
                    
                    <div id="api-section" class="mb-4 <?= !isset($useApi) || !$useApi ? 'd-none' : '' ?>">
                        <div class="card border-info">
                            <div class="card-header bg-info bg-opacity-25">
                                <h5 class="mb-0">API Download Options</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="server_type" class="form-label">Server Type</label>
                                    <select class="form-select" id="server_type" name="server_type">
                                        <option value="">Select a server type...</option>
                                        <?php if (isset($serverTypes) && !empty($serverTypes)): ?>
                                            <?php foreach ($serverTypes as $type): ?>
                                            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Type of Minecraft server to download</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="version" class="form-label">Server Version</label>
                                    <select class="form-select" id="version" name="version" disabled>
                                        <option value="">Select a server type first...</option>
                                    </select>
                                    <div class="form-text">Version of the selected server type</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Server Name</label>
                        <input type="text" class="form-control" id="name" name="name" required
                              value="<?= isset($server) ? htmlspecialchars($server['name']) : '' ?>">
                        <div class="form-text">A unique name for your server</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="path" class="form-label">Server Directory</label>
                        <input type="text" class="form-control" id="path" name="path" required
                              value="<?= isset($server) ? htmlspecialchars($server['path']) : '' ?>">
                        <div class="form-text">Full path to the server directory</div>
                    </div>
                    
                    <div id="local-section" class="mb-3 <?= !isset($server) && isset($useApi) && $useApi ? 'd-none' : '' ?>">
                        <label for="jar_file" class="form-label">Server JAR File</label>
                        <input type="text" class="form-control" id="jar_file" name="jar_file"
                              value="<?= isset($server) ? htmlspecialchars($server['jar_file']) : 'server.jar' ?>">
                        <div class="form-text">Name of the server JAR file (e.g., server.jar)</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_memory" class="form-label">Minimum Memory</label>
                                <input type="text" class="form-control" id="min_memory" name="min_memory"
                                      value="<?= isset($server) ? htmlspecialchars($server['min_memory']) : '1G' ?>">
                                <div class="form-text">Minimum memory allocation (e.g., 1G)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_memory" class="form-label">Maximum Memory</label>
                                <input type="text" class="form-control" id="max_memory" name="max_memory"
                                      value="<?= isset($server) ? htmlspecialchars($server['max_memory']) : '2G' ?>">
                                <div class="form-text">Maximum memory allocation (e.g., 2G)</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="port" class="form-label">Server Port</label>
                        <input type="number" class="form-control" id="port" name="port" min="1" max="65535"
                              value="<?= isset($server) ? htmlspecialchars($server['port']) : '25565' ?>">
                        <div class="form-text">Minecraft server port (default: 25565)</div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url() ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <?php if (isset($server)): ?>
                                <i class="bi bi-save me-2"></i>Save Changes
                            <?php else: ?>
                                <i class="bi bi-plus-circle me-2"></i>Add Server
                            <?php endif; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>