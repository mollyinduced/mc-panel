import os
import subprocess
import time
import re
import shutil
import datetime
import logging
from pathlib import Path
from models import MinecraftServer, ServerManager

logger = logging.getLogger(__name__)

class MinecraftServerManager:
    """Class to manage Minecraft server operations"""
    def __init__(self):
        self.server_manager = ServerManager()
        self.processes = {}
    
    def start_server(self, server_name):
        """Start a Minecraft server"""
        server = self.server_manager.get_server(server_name)
        if not server:
            return False, "Server not found"
        
        if server_name in self.processes and self.processes[server_name].poll() is None:
            return False, "Server is already running"
        
        try:
            # Make sure we're in the server directory
            os.chdir(server.path)
            
            # Build the Java command
            command = [
                "java",
                f"-Xms{server.min_memory}",
                f"-Xmx{server.max_memory}",
                "-jar",
                server.jar_file,
                "nogui"
            ]
            
            # Start the server process
            process = subprocess.Popen(
                command,
                stdin=subprocess.PIPE,
                stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT,
                text=True,
                cwd=server.path
            )
            
            self.processes[server_name] = process
            logger.info(f"Started Minecraft server: {server_name}")
            return True, "Server started successfully"
            
        except Exception as e:
            logger.error(f"Error starting server {server_name}: {str(e)}")
            return False, f"Error starting server: {str(e)}"
    
    def stop_server(self, server_name):
        """Stop a Minecraft server"""
        if server_name not in self.processes:
            return False, "Server is not running"
        
        process = self.processes[server_name]
        if process.poll() is not None:
            del self.processes[server_name]
            return False, "Server is not running"
        
        try:
            # Send 'stop' command to the server
            process.stdin.write("stop\n")
            process.stdin.flush()
            
            # Wait for the process to terminate (with timeout)
            for _ in range(30):  # 30 second timeout
                if process.poll() is not None:
                    del self.processes[server_name]
                    logger.info(f"Stopped Minecraft server: {server_name}")
                    return True, "Server stopped successfully"
                time.sleep(1)
            
            # Force kill if it didn't stop gracefully
            process.terminate()
            time.sleep(2)
            if process.poll() is None:
                process.kill()
            
            del self.processes[server_name]
            logger.warning(f"Force stopped Minecraft server: {server_name}")
            return True, "Server force stopped"
            
        except Exception as e:
            logger.error(f"Error stopping server {server_name}: {str(e)}")
            return False, f"Error stopping server: {str(e)}"
    
    def get_server_status(self, server_name):
        """Get the status of a server (running or stopped)"""
        if server_name in self.processes and self.processes[server_name].poll() is None:
            return "running"
        return "stopped"
    
    def get_all_server_statuses(self):
        """Get status for all servers"""
        statuses = {}
        for server in self.server_manager.get_all_servers():
            statuses[server.name] = self.get_server_status(server.name)
        return statuses
    
    def get_server_logs(self, server_name, lines=100):
        """Get recent logs from a server"""
        server = self.server_manager.get_server(server_name)
        if not server:
            return []
        
        log_file = server.path / "logs" / "latest.log"
        if not log_file.exists():
            return []
        
        try:
            with open(log_file, 'r', encoding='utf-8') as f:
                logs = f.readlines()
            
            # Return the most recent lines
            return logs[-lines:] if len(logs) > lines else logs
        except Exception as e:
            logger.error(f"Error reading logs for {server_name}: {str(e)}")
            return []
    
    def get_players(self, server_name):
        """Get list of online players"""
        if server_name not in self.processes or self.processes[server_name].poll() is not None:
            return []
        
        # Send the list command to the server
        try:
            process = self.processes[server_name]
            process.stdin.write("list\n")
            process.stdin.flush()
            
            # Wait a moment for the response
            time.sleep(0.5)
            
            # Check the server output for the player list
            if process.stdout:
                output = ""
                while process.stdout.readable():
                    line = process.stdout.readline()
                    if not line:
                        break
                    output += line
                    if "players online" in line:
                        # Extract player names
                        match = re.search(r'players online: (.*)', line)
                        if match and match.group(1):
                            return [player.strip() for player in match.group(1).split(',')]
                        return []
            return []
        except Exception as e:
            logger.error(f"Error getting players for {server_name}: {str(e)}")
            return []
    
    def execute_command(self, server_name, command):
        """Execute a command on the server"""
        if server_name not in self.processes or self.processes[server_name].poll() is not None:
            return False, "Server is not running"
        
        try:
            process = self.processes[server_name]
            process.stdin.write(f"{command}\n")
            process.stdin.flush()
            return True, "Command executed"
        except Exception as e:
            logger.error(f"Error executing command on {server_name}: {str(e)}")
            return False, f"Error executing command: {str(e)}"
    
    def get_whitelist(self, server_name):
        """Get the server whitelist"""
        server = self.server_manager.get_server(server_name)
        if not server:
            return []
        
        whitelist_file = server.path / "whitelist.json"
        if not whitelist_file.exists():
            return []
        
        try:
            with open(whitelist_file, 'r') as f:
                whitelist = json.load(f)
            return whitelist
        except Exception as e:
            logger.error(f"Error reading whitelist for {server_name}: {str(e)}")
            return []
    
    def get_banned_players(self, server_name):
        """Get list of banned players"""
        server = self.server_manager.get_server(server_name)
        if not server:
            return []
        
        banned_players_file = server.path / "banned-players.json"
        if not banned_players_file.exists():
            return []
        
        try:
            with open(banned_players_file, 'r') as f:
                banned_players = json.load(f)
            return banned_players
        except Exception as e:
            logger.error(f"Error reading banned players for {server_name}: {str(e)}")
            return []
    
    def create_backup(self, server_name):
        """Create a backup of the server"""
        server = self.server_manager.get_server(server_name)
        if not server:
            return False, "Server not found"
        
        # Create backup directory if it doesn't exist
        backup_dir = server.path / "backups"
        backup_dir.mkdir(exist_ok=True)
        
        # Create timestamp for backup name
        timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
        backup_name = f"{server_name}_{timestamp}"
        backup_path = backup_dir / backup_name
        
        # Get the current server status
        running = self.get_server_status(server_name) == "running"
        
        # Stop the server if it's running
        if running:
            success, message = self.stop_server(server_name)
            if not success:
                return False, f"Failed to stop server for backup: {message}"
        
        try:
            # Create the backup
            shutil.make_archive(str(backup_path), 'zip', server.path)
            logger.info(f"Created backup of {server_name} at {backup_path}.zip")
            
            # Restart the server if it was running before
            if running:
                self.start_server(server_name)
            
            return True, f"Backup created: {backup_name}.zip"
        except Exception as e:
            logger.error(f"Error creating backup for {server_name}: {str(e)}")
            
            # Try to restart the server if it was running before
            if running:
                self.start_server(server_name)
                
            return False, f"Error creating backup: {str(e)}"
    
    def get_backups(self, server_name):
        """Get list of available backups for a server"""
        server = self.server_manager.get_server(server_name)
        if not server:
            return []
        
        backup_dir = server.path / "backups"
        if not backup_dir.exists():
            return []
        
        backups = []
        for file in backup_dir.glob(f"{server_name}_*.zip"):
            stat = file.stat()
            backups.append({
                "name": file.name,
                "size": stat.st_size,
                "date": datetime.datetime.fromtimestamp(stat.st_mtime).strftime("%Y-%m-%d %H:%M:%S")
            })
        
        # Sort by date (most recent first)
        backups.sort(key=lambda x: x["date"], reverse=True)
        return backups
    
    def restore_backup(self, server_name, backup_name):
        """Restore a server from a backup"""
        server = self.server_manager.get_server(server_name)
        if not server:
            return False, "Server not found"
        
        backup_file = server.path / "backups" / backup_name
        if not backup_file.exists():
            return False, "Backup not found"
        
        # Get the current server status
        running = self.get_server_status(server_name) == "running"
        
        # Stop the server if it's running
        if running:
            success, message = self.stop_server(server_name)
            if not success:
                return False, f"Failed to stop server for restore: {message}"
        
        try:
            # Create temporary restore directory
            restore_temp = server.path / "restore_temp"
            if restore_temp.exists():
                shutil.rmtree(restore_temp)
            restore_temp.mkdir()
            
            # Extract backup to temporary directory
            shutil.unpack_archive(str(backup_file), str(restore_temp), 'zip')
            
            # Remove current server files (keep backups and certain files)
            for item in server.path.iterdir():
                if item.name != "backups" and item.name != "restore_temp":
                    if item.is_dir():
                        shutil.rmtree(item)
                    else:
                        item.unlink()
            
            # Move restored files to server directory
            for item in restore_temp.iterdir():
                shutil.move(str(item), str(server.path))
            
            # Clean up
            shutil.rmtree(restore_temp)
            
            logger.info(f"Restored {server_name} from backup {backup_name}")
            
            # Restart the server if it was running before
            if running:
                self.start_server(server_name)
                
            return True, "Server restored successfully"
        except Exception as e:
            logger.error(f"Error restoring {server_name} from backup: {str(e)}")
            
            # Try to restart the server if it was running before
            if running:
                self.start_server(server_name)
                
            return False, f"Error restoring server: {str(e)}"
    
    def get_server_properties(self, server_name):
        """Get server.properties as a dictionary"""
        server = self.server_manager.get_server(server_name)
        if not server:
            return {}
        
        properties_file = server.path / "server.properties"
        if not properties_file.exists():
            return {}
        
        properties = {}
        try:
            with open(properties_file, 'r') as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith('#'):
                        key, value = line.split('=', 1)
                        properties[key.strip()] = value.strip()
            return properties
        except Exception as e:
            logger.error(f"Error reading server.properties for {server_name}: {str(e)}")
            return {}
    
    def update_server_properties(self, server_name, properties):
        """Update server.properties with new values"""
        server = self.server_manager.get_server(server_name)
        if not server:
            return False, "Server not found"
        
        properties_file = server.path / "server.properties"
        current_properties = self.get_server_properties(server_name)
        
        # Update with new values
        current_properties.update(properties)
        
        try:
            with open(properties_file, 'w') as f:
                for key, value in current_properties.items():
                    f.write(f"{key}={value}\n")
            
            logger.info(f"Updated server.properties for {server_name}")
            return True, "Server properties updated"
        except Exception as e:
            logger.error(f"Error updating server.properties for {server_name}: {str(e)}")
            return False, f"Error updating server properties: {str(e)}"
