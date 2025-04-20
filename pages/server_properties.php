<div class="row">
    <div class="col">
        <h1 class="display-5 mb-4">
            <i class="bi bi-sliders text-primary me-2"></i>
            Server Properties: <?= htmlspecialchars($server['name']) ?>
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
            <div class="card-body">
                <form method="POST" action="<?= $_SERVER['REQUEST_URI'] ?>">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Changes to server properties will take effect after restarting the server.
                    </div>
                    
                    <div class="mb-3">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($properties)): ?>
                                        <?php foreach ($properties as $key => $value): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($key) ?></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" 
                                                          name="property_<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center">No properties found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url() ?>" class="btn btn-secondary">Back to Dashboard</a>
                        <button type="submit" class="btn btn-primary"
                               <?= empty($properties) ? 'disabled' : '' ?>>
                            <i class="bi bi-save me-2"></i>Save Properties
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>