import os
from flask import render_template, request, jsonify, redirect, url_for, flash, send_file
from app import app
from server_manager import MinecraftServerManager
from models import MinecraftServer
import logging

# Initialize server manager
server_manager = MinecraftServerManager()

@app.route('/')
def index():
    """Main dashboard view"""
    servers = server_manager.server_manager.get_all_servers()
    statuses = server_manager.get_all_server_statuses()
    return render_template('dashboard.html', servers=servers, statuses=statuses, use_api=server_manager.server_manager.use_api)

@app.route('/api/settings', methods=['POST'])
def update_api_settings():
    """Update API settings"""
    use_api = request.json.get('use_api', False)
    server_manager.server_manager.set_use_api(use_api)
    return jsonify({"success": True, "use_api": use_api})

@app.route('/api/server_types')
def get_server_types():
    """Get available server types for API"""
    server_types = server_manager.server_manager.get_server_types()
    return jsonify({"server_types": server_types})

@app.route('/api/versions/<server_type>')
def get_versions(server_type):
    """Get available versions for a server type"""
    versions = server_manager.server_manager.get_versions_for_type(server_type)
    return jsonify({"versions": versions})

@app.route('/api/server/start/<server_name>', methods=['POST'])
def start_server(server_name):
    """Start a Minecraft server"""
    success, message = server_manager.start_server(server_name)
    return jsonify({"success": success, "message": message})

@app.route('/api/server/stop/<server_name>', methods=['POST'])
def stop_server(server_name):
    """Stop a Minecraft server"""
    success, message = server_manager.stop_server(server_name)
    return jsonify({"success": success, "message": message})

@app.route('/api/server/status/<server_name>')
def server_status(server_name):
    """Get the status of a server"""
    status = server_manager.get_server_status(server_name)
    return jsonify({"status": status})

@app.route('/api/server/status')
def all_server_statuses():
    """Get the status of all servers"""
    statuses = server_manager.get_all_server_statuses()
    return jsonify(statuses)

@app.route('/server/add', methods=['GET', 'POST'])
def add_server():
    """Add a new Minecraft server"""
    if request.method == 'POST':
        name = request.form.get('name')
        path = request.form.get('path')
        min_memory = request.form.get('min_memory')
        max_memory = request.form.get('max_memory')
        port = int(request.form.get('port', 25565))
        
        # Check if using API
        use_api = request.form.get('use_api') == 'on'
        
        # Validate inputs
        if not name or not path:
            flash('Name and path are required', 'danger')
            return redirect(url_for('add_server'))
        
        if use_api:
            # Get API-specific fields
            server_type = request.form.get('server_type')
            version = request.form.get('version')
            
            if not server_type or not version:
                flash('Server type and version are required when using API', 'danger')
                return redirect(url_for('add_server'))
            
            # Create server using API
            success, message = server_manager.server_manager.create_server_from_api(
                name=name,
                path=path,
                server_type=server_type,
                version=version,
                min_memory=min_memory,
                max_memory=max_memory,
                port=port
            )
            
            if success:
                flash(f'Server {name} added successfully using API', 'success')
                return redirect(url_for('index'))
            else:
                flash(f'Failed to create server: {message}', 'danger')
                return redirect(url_for('add_server'))
        else:
            # Using local setup
            jar_file = request.form.get('jar_file')
            
            if not jar_file:
                flash('JAR file name is required', 'danger')
                return redirect(url_for('add_server'))
            
            # Create new server
            server = MinecraftServer(
                name=name,
                path=path,
                jar_file=jar_file,
                min_memory=min_memory,
                max_memory=max_memory,
                port=port
            )
            
            # Add to server manager
            server_manager.server_manager.add_server(server)
            flash(f'Server {name} added successfully', 'success')
            return redirect(url_for('index'))
    
    # Get available server types for API
    server_types = server_manager.server_manager.get_server_types()
    use_api = server_manager.server_manager.use_api
    
    return render_template(
        'server_config.html', 
        server=None, 
        action="add", 
        server_types=server_types,
        use_api=use_api
    )

@app.route('/server/edit/<server_name>', methods=['GET', 'POST'])
def edit_server(server_name):
    """Edit an existing Minecraft server"""
    server = server_manager.server_manager.get_server(server_name)
    
    if not server:
        flash('Server not found', 'danger')
        return redirect(url_for('index'))
    
    if request.method == 'POST':
        name = request.form.get('name')
        path = request.form.get('path')
        jar_file = request.form.get('jar_file')
        min_memory = request.form.get('min_memory')
        max_memory = request.form.get('max_memory')
        port = int(request.form.get('port', 25565))
        
        # Update server
        server_manager.server_manager.update_server(
            server_name,
            name=name,
            path=path,
            jar_file=jar_file,
            min_memory=min_memory,
            max_memory=max_memory,
            port=port
        )
        
        flash(f'Server {name} updated successfully', 'success')
        return redirect(url_for('index'))
    
    return render_template('server_config.html', server=server, action="edit")

@app.route('/server/delete/<server_name>', methods=['POST'])
def delete_server(server_name):
    """Delete a Minecraft server configuration"""
    # Make sure the server is stopped first
    if server_manager.get_server_status(server_name) == "running":
        server_manager.stop_server(server_name)
    
    success = server_manager.server_manager.remove_server(server_name)
    
    if success:
        flash(f'Server {server_name} deleted successfully', 'success')
    else:
        flash(f'Failed to delete server {server_name}', 'danger')
    
    return redirect(url_for('index'))

@app.route('/server/logs/<server_name>')
def server_logs(server_name):
    """View server logs"""
    server = server_manager.server_manager.get_server(server_name)
    
    if not server:
        flash('Server not found', 'danger')
        return redirect(url_for('index'))
    
    logs = server_manager.get_server_logs(server_name)
    return render_template('server_logs.html', server=server, logs=logs)

@app.route('/api/server/logs/<server_name>')
def get_logs(server_name):
    """Get server logs as JSON"""
    logs = server_manager.get_server_logs(server_name)
    return jsonify({"logs": logs})

@app.route('/server/players/<server_name>')
def player_management(server_name):
    """Player management page"""
    server = server_manager.server_manager.get_server(server_name)
    
    if not server:
        flash('Server not found', 'danger')
        return redirect(url_for('index'))
    
    online_players = []
    whitelist = []
    banned_players = []
    
    # Get online players if server is running
    if server_manager.get_server_status(server_name) == "running":
        online_players = server_manager.get_players(server_name)
    
    # Get whitelist and banned players
    whitelist = server_manager.get_whitelist(server_name)
    banned_players = server_manager.get_banned_players(server_name)
    
    return render_template(
        'player_management.html',
        server=server,
        online_players=online_players,
        whitelist=whitelist,
        banned_players=banned_players
    )

@app.route('/api/server/command/<server_name>', methods=['POST'])
def execute_command(server_name):
    """Execute a command on the server"""
    command = request.json.get('command')
    if not command:
        return jsonify({"success": False, "message": "No command provided"})
    
    success, message = server_manager.execute_command(server_name, command)
    return jsonify({"success": success, "message": message})

@app.route('/server/backups/<server_name>')
def backups(server_name):
    """Backup management page"""
    server = server_manager.server_manager.get_server(server_name)
    
    if not server:
        flash('Server not found', 'danger')
        return redirect(url_for('index'))
    
    backups = server_manager.get_backups(server_name)
    return render_template('backups.html', server=server, backups=backups)

@app.route('/api/server/backup/create/<server_name>', methods=['POST'])
def create_backup(server_name):
    """Create a server backup"""
    success, message = server_manager.create_backup(server_name)
    return jsonify({"success": success, "message": message})

@app.route('/api/server/backup/restore/<server_name>', methods=['POST'])
def restore_backup(server_name):
    """Restore a server from backup"""
    backup_name = request.json.get('backup_name')
    if not backup_name:
        return jsonify({"success": False, "message": "No backup specified"})
    
    success, message = server_manager.restore_backup(server_name, backup_name)
    return jsonify({"success": success, "message": message})

@app.route('/server/properties/<server_name>', methods=['GET', 'POST'])
def server_properties(server_name):
    """View and edit server.properties"""
    server = server_manager.server_manager.get_server(server_name)
    
    if not server:
        flash('Server not found', 'danger')
        return redirect(url_for('index'))
    
    if request.method == 'POST':
        # Extract properties from form
        properties = {}
        for key, value in request.form.items():
            if key.startswith('property_'):
                property_name = key[9:]  # Remove 'property_' prefix
                properties[property_name] = value
        
        # Update properties
        success, message = server_manager.update_server_properties(server_name, properties)
        
        if success:
            flash('Server properties updated successfully', 'success')
        else:
            flash(f'Failed to update server properties: {message}', 'danger')
        
        return redirect(url_for('server_properties', server_name=server_name))
    
    properties = server_manager.get_server_properties(server_name)
    return render_template('server_config.html', server=server, properties=properties, action="properties")

@app.errorhandler(404)
def page_not_found(e):
    return render_template('base.html', error="Page not found"), 404

@app.errorhandler(500)
def server_error(e):
    return render_template('base.html', error="Server error occurred"), 500
