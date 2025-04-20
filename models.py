import os
import json
import logging
import requests
from pathlib import Path

logger = logging.getLogger(__name__)

class MinecraftServer:
    """
    Class to represent a Minecraft server configuration
    """
    def __init__(self, name, path, jar_file, min_memory="1G", max_memory="2G", port=25565, server_type=None, version=None):
        self.name = name
        self.path = Path(path)
        self.jar_file = jar_file
        self.min_memory = min_memory
        self.max_memory = max_memory
        self.port = port
        self.properties_file = self.path / "server.properties"
        self.server_type = server_type  # For API-created servers: VANILLA, PAPER, etc.
        self.version = version  # For API-created servers: version string

    def to_dict(self):
        return {
            "name": self.name,
            "path": str(self.path),
            "jar_file": self.jar_file,
            "min_memory": self.min_memory,
            "max_memory": self.max_memory,
            "port": self.port,
            "server_type": self.server_type,
            "version": self.version
        }
    
    @classmethod
    def from_dict(cls, data):
        return cls(
            name=data["name"],
            path=data["path"],
            jar_file=data["jar_file"],
            min_memory=data.get("min_memory", "1G"),
            max_memory=data.get("max_memory", "2G"),
            port=data.get("port", 25565),
            server_type=data.get("server_type"),
            version=data.get("version")
        )


class ServerManager:
    """
    Class to manage multiple Minecraft servers
    """
    CONFIG_FILE = "minecraft_servers.json"
    
    def __init__(self):
        self.servers = {}
        self.use_api = False  # Flag to determine if we use API or local files
        self.load_servers()
    
    def load_servers(self):
        """Load server configurations from JSON file"""
        if os.path.exists(self.CONFIG_FILE):
            try:
                with open(self.CONFIG_FILE, 'r') as f:
                    data = json.load(f)
                    if isinstance(data, dict) and "use_api" in data:
                        self.use_api = data["use_api"]
                        server_list = data.get("servers", [])
                    else:
                        # Handle legacy format
                        server_list = data
                    
                    for server_data in server_list:
                        server = MinecraftServer.from_dict(server_data)
                        self.servers[server.name] = server
            except Exception as e:
                logger.error(f"Error loading server configurations: {e}")
        
    def save_servers(self):
        """Save server configurations to JSON file"""
        try:
            server_list = [server.to_dict() for server in self.servers.values()]
            data = {
                "use_api": self.use_api,
                "servers": server_list
            }
            with open(self.CONFIG_FILE, 'w') as f:
                json.dump(data, f, indent=2)
        except Exception as e:
            logger.error(f"Error saving server configurations: {e}")
    
    def add_server(self, server):
        """Add a new Minecraft server"""
        self.servers[server.name] = server
        self.save_servers()
        
    def create_server_from_api(self, name, path, server_type, version, min_memory="1G", max_memory="2G", port=25565):
        """Create a new server by downloading the JAR file from the API"""
        try:
            # Create the server directory if it doesn't exist
            os.makedirs(path, exist_ok=True)
            
            # Get the download URL for the server JAR
            api_url = f"https://versions.mcjars.app/api/v2/builds/{server_type}/{version}?fields=installation"
            response = requests.get(api_url)
            response.raise_for_status()
            
            data = response.json()
            download_url = data['builds'][0]['installation'][0][0]['url']
            
            # Determine the JAR file name
            jar_file = f"server.jar"
            jar_path = os.path.join(path, jar_file)
            
            # Download the JAR file
            jar_content = requests.get(download_url)
            jar_content.raise_for_status()
            
            with open(jar_path, 'wb') as f:
                f.write(jar_content.content)
            
            # Create server object
            server = MinecraftServer(
                name=name,
                path=path,
                jar_file=jar_file,
                min_memory=min_memory,
                max_memory=max_memory,
                port=port,
                server_type=server_type,
                version=version
            )
            
            # Add to collection and save
            self.add_server(server)
            return True, "Server created successfully"
        except requests.RequestException as e:
            logger.error(f"API error creating server: {e}")
            return False, f"API error: {str(e)}"
        except Exception as e:
            logger.error(f"Error creating server: {e}")
            return False, f"Error: {str(e)}"
    
    def set_use_api(self, use_api):
        """Set whether to use API for server creation"""
        self.use_api = bool(use_api)
        self.save_servers()
    
    def update_server(self, name, **kwargs):
        """Update a server's configuration"""
        if name in self.servers:
            server = self.servers[name]
            for key, value in kwargs.items():
                if hasattr(server, key):
                    setattr(server, key, value)
            self.save_servers()
            return True
        return False
    
    def remove_server(self, name):
        """Remove a server"""
        if name in self.servers:
            del self.servers[name]
            self.save_servers()
            return True
        return False
    
    def get_server(self, name):
        """Get a server by name"""
        return self.servers.get(name)
    
    def get_all_servers(self):
        """Get all server configurations"""
        return list(self.servers.values())
        
    def get_server_types(self):
        """Get available server types from API"""
        try:
            response = requests.get("https://versions.mcjars.app/api/v2/builds")
            response.raise_for_status()
            return response.json()["builds"]
        except Exception as e:
            logger.error(f"Error fetching server types: {e}")
            # Default types if API fails
            return ['VANILLA', 'PAPER', 'FABRIC', 'FORGE', 'SPIGOT', 'PURPUR']
            
    def get_versions_for_type(self, server_type):
        """Get available versions for a server type"""
        try:
            response = requests.get(f"https://versions.mcjars.app/api/v2/builds/{server_type}?fields=projectVersionId,versionId")
            response.raise_for_status()
            data = response.json()
            versions = []
            for build in data.get('builds', {}).values():
                if 'latest' in build:
                    version_id = build['latest'].get('versionId') or build['latest'].get('projectVersionId')
                    if version_id:
                        versions.append({
                            'id': version_id,
                            'type': build.get('type', 'UNKNOWN'),
                            'java': build.get('java', 'Unknown')
                        })
            return versions
        except Exception as e:
            logger.error(f"Error fetching versions for {server_type}: {e}")
            return []
