<?php
// Available types
$types = ['VANILLA', 'PAPER', 'FABRIC', 'FORGE', 'NEOFORGE', 'VELOCITY', 'PURPUR', 'PUFFERFISH', 'SPONGE', 'SPIGOT', 'BUNGEECORD', 'FOLIA', 'QUILT', 'CANVAS', 'MOHIST', 'LEAVES', 'ASPAPER', 'LEGACY FABRIC'];

// Get selected type from URL (default: VANILLA)
$type = isset($_GET['type']) && in_array($_GET['type'], $types) ? $_GET['type'] : 'VANILLA';

// Get selected filter from URL (default: ALL)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'ALL';

// Fetch data from API based on the selected type
$api_url = "https://versions.mcjars.app/api/v2/builds/$type?fields=projectVersionId,versionId";
$response = file_get_contents($api_url);
$data = json_decode($response, true);

// Extract versions
$versions = $data['builds'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minecraft Version Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .filter-buttons, .dropdown-container {
            margin-bottom: 15px;
        }
        .filter-buttons button {
            padding: 10px;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .all { background-color: #888; color: white; }
        .release { background-color: #2196F3; color: white; }
        .snapshot { background-color: #FF9800; color: white; }
        select {
            padding: 10px;
            font-size: 16px;
        }
    </style>
    <script>
        function updateType() {
            var type = document.getElementById("typeSelector").value;
            var filter = new URLSearchParams(window.location.search).get("filter") || "ALL";
            window.location.href = "?type=" + type + "&filter=" + filter;
        }

        function updateFilter(filter) {
            var type = document.getElementById("typeSelector").value;
            window.location.href = "?type=" + type + "&filter=" + filter;
        }

        async function getDownloadUrl(version, type) {
            try {
                let response = await fetch(`https://versions.mcjars.app/api/v2/builds/${type}/${version}?fields=id,type,projectVersionId,versionId,name,experimental,created,changes,installation`);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                let data = await response.json();
                
                // Check if the required fields exist
                if (data.builds && data.builds.length > 0 && data.builds[0].installation.length > 0 && data.builds[0].installation[0].length > 0) {
                    let downloadUrl = data.builds[0].installation[0][0].url;
                    window.location.href = downloadUrl; // Redirect to the download URL
                } else {
                    throw new Error('Download URL not found in the API response');
                }
            } catch (error) {
                console.error('Error fetching download URL:', error);
                alert('Failed to fetch download URL. Please try again.');
            }
        }
    </script>
</head>
<body>

<h1>Minecraft Version Viewer</h1>

<!-- Dropdown for selecting type -->
<div class="dropdown-container">
    <label for="typeSelector">Select Type:</label>
    <select id="typeSelector" onchange="updateType()">
        <?php foreach ($types as $option): ?>
            <option value="<?= $option ?>" <?= $option == $type ? 'selected' : '' ?>><?= strtoupper($option) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Filter Buttons -->
<div class="filter-buttons">
    <button class="all" onclick="updateFilter('ALL')">Show All</button>
    <button class="release" onclick="updateFilter('RELEASE')">Show RELEASE</button>
    <button class="snapshot" onclick="updateFilter('SNAPSHOT')">Show SNAPSHOT</button>
</div>

<!-- Version Table -->
<table>
    <tr>
        <?php if ($type === 'VELOCITY'): ?>
            <th>Project Version ID</th>
        <?php endif; ?>
        <?php if ($type !== 'VELOCITY'): ?>
        <th>Version</th>
        <?php endif; ?>

        <th>Type</th>
        <th>Java Version</th>
        <th>Release Date</th>
        <th>Download</th>
    </tr>
    <?php
    foreach ($versions as $version => $info) {
        if ($filter !== 'ALL' && $info['type'] !== $filter) {
            continue; // Skip if it doesn't match the selected filter
        }
        echo "<tr>";
        
        // Show Project Version ID only if type is VELOCITY
        if ($type === 'VELOCITY') {
            echo 
            "<td>{$info['latest']['projectVersionId']}</td>
                  <td>{$info['type']}</td>
                  <td>{$info['java']}</td>
                  <td>{$info['created']}</td>
                  <td><button onclick='getDownloadUrl(\"{$info['latest']['projectVersionId']}\", \"{$type}\")'>Download</button></td>
              </tr>";
        }

        if ($type !== 'VELOCITY') {
            echo 
            "<td>{$info['latest']['versionId']}</td>
                  <td>{$info['type']}</td>
                  <td>{$info['java']}</td>
                  <td>{$info['created']}</td>
                  <td><button onclick='getDownloadUrl(\"{$info['latest']['versionId']}\", \"{$type}\")'>Download</button></td>
              </tr>";
        }
    }
    ?>
</table>

</body>
</html>
