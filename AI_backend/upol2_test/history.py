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



"""
Vezmu dopravni profily
Vezmu seznam zon
udelam mapu z profilu do zon

stahnu agregovana data pro profily
pro kazdy profil spocitam jeho soucet od pocatecniho casu - might be 7:00 am
pro kazdou zonu udelam soucet z profilu a udelam sumu
"""


if __name__ == "__main__":
    data = load_data("upol2_test\profiles_history_from_seven.json")

    objects = {}

    for row in data["results"]:
        objects[row["object_name"]] = None
        # print(len(row["measurements"]))
        state = 0
        for measurement in row["measurements"]:
            if measurement["key"] == "gate_increment_value_sum":
                for value in measurement["values"]:
                    state += value["point"]["value"]
        objects[row["object_name"]] = state

    print(list(objects.keys()))