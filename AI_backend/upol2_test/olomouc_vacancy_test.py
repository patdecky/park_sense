import requests
import json

from dataclasses import dataclass

import sys
sys.stdout.reconfigure(encoding='utf-8')


def get_data_to_file(file_path:str):
    with open (file_path, "w", encoding="utf-8") as f:
        response = requests.get("https://iot.citiq.cloud/portal/api/v2/view/olomouc-traffic/")
        f.write(response.text)

def get_data_to_variable():
    response = requests.get("https://iot.citiq.cloud/portal/api/v2/view/olomouc-traffic/")
    return response.json()
    
def load_data(file_path:str):
    with open (file_path, "r", encoding="utf-8") as f:
        data = json.load(f)
        return data
    
@dataclass
class Sample:
    key:str
    vacancy:int





if __name__ == "__main__":
    data = load_data("upol2_test/data_vacancy.json")

    names_list = []
    passes_list = []

    for row in data:
        names_list.append(row["object_name"])
        last_values = row["last_value"]
        if last_values is None:
            passes_list.append(None)
        else:
            passes_list.append(row["last_value"]["device_frmpayload_data_len3_cnt32"])

    print(names_list, len(names_list))

    zones = {}

    for name, passes in zip(names_list, passes_list):
        zone_name = ".".join(name.split(".")[:-1])
        if zone_name not in zones:
            zones[zone_name] = []
        zones[zone_name].append((name, passes))

    print(zones)


