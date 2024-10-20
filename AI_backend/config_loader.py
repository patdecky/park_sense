import json

class Config:
    def __init__(self, config_path):
        with open(config_path, "r") as f:
            self.data:dict = json.load(f)

