import json
from dataclasses import dataclass

class Config:
    def __init__(self, config_path):
        with open(config_path, "r") as f:
            self.data:dict = json.load(f)

@dataclass
class ConfigNew:
    host: str = ""
    port: int = 13490
    user: str = ""
    password: str = ""
    database: str = ""

    def __init__(self, config_path):
        with open(config_path, "r") as f:
            self.data: dict = json.load(f)
            self.host = self.data.get("host", "")
            self.port = self.data.get("port", 13490)
            self.user = self.data.get("user", "")
            self.password = self.data.get("password", "")
            self.database = self.data.get("database", "")
