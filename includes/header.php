<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minecraft Server Control Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 40px;
        }
        
        .server-card {
            transition: transform 0.2s;
        }
        
        .server-card:hover {
            transform: translateY(-5px);
        }
        
        .server-status span {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
        }
        
        pre.logs {
            height: 500px;
            overflow-y: auto;
            background-color: #212529;
            color: #fff;
            padding: 1rem;
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 0.85rem;
        }
        
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 rounded">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?= base_url() ?>">
                    <i class="bi bi-hdd-stack me-2"></i>
                    Minecraft Panel
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url() ?>">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('?route=add-server') ?>">
                                <i class="bi bi-plus-circle me-1"></i> Add Server
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <?php
        // Display flash messages
        $flashMessage = getFlashMessage();
        if ($flashMessage) {
            $alertClass = $flashMessage['type'] === 'success' ? 'alert-success' : 'alert-danger';
            $icon = $flashMessage['type'] === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
            <i class="bi <?= $icon ?> me-2"></i>
            <?= htmlspecialchars($flashMessage['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php } ?>