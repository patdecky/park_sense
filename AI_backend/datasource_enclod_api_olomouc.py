import requests
import json
from datetime import datetime, timezone
from urllib.parse import quote

from dataclasses import dataclass
import logging
log = logging.getLogger(__name__)


@dataclass
class EnclodAPIOlomoucDataContext:
    """
    Mapping from zone name to zone vacancy.
    """

    context: dict[str, tuple[float, int, int, int]]  # fraction_vacant, capacity, measured_occupied, calibration


zone_data = {
    "parkoviště Poupětova": {"KAPACITA": 133, "KALIBRACE": 56},
    "Kafkova- Aquapark": {"KAPACITA": 989, "KALIBRACE": 43},
    "Nové Sady jih (Voskovcova)": {"KAPACITA": 1181, "KALIBRACE": 1184},
    "Billa": {"KAPACITA": 85, "KALIBRACE": 13},
    "parkoviště Legionářská": {"KAPACITA": 110, "KALIBRACE": 19},
    "UPOL Envelopa": {"KAPACITA": 370, "KALIBRACE": 83},
    "Nové Sady sever (Trnkova)": {"KAPACITA": 1169, "KALIBRACE": 1148},
    "Boleslavova C.2": {"KAPACITA": 247, "KALIBRACE": 263},
    "Tržnice": {"KAPACITA": 105, "KALIBRACE": 21},
    "Lazce D.1": {"KAPACITA": 1218, "KALIBRACE": 1249},
}

profile_to_zone_mapping = {
    "17.listopadu JIH": "UPOL Envelopa",
    "17.listopadu Sever": "UPOL Envelopa",
    "Aksamitova/Vídeňská": "Tržnice",
    "Billa (Hynaisova)": "Billa",
    "Kafkova - levá": "Kafkova- Aquapark",
    "Kafkova - pravá": "Kafkova- Aquapark",
    "Parkoviště Flora": "parkoviště Poupětova",
    "Šmeralova IN": "UPOL Envelopa",
    "Šmeralova OUT/17.listopadu": "UPOL Envelopa",
    "Šmeralova OUT/Blahoslavova": "UPOL Envelopa",
    "Trnkova/Rooseveltova": "Nové Sady sever (Trnkova)",
    "Trnkova/Zítkova": "Nové Sady sever (Trnkova)",
    "Tržnice/Polská": "Tržnice",
    "U Letadla": "parkoviště Legionářská",
    "Zikova": "Nové Sady sever (Trnkova)",
}

# tyto nejsou v profilech
# "Fugnerova",
# "Peškova": "Nové Sady jih (Voskovcova)",
# "Voskovcova": "Nové Sady jih (Voskovcova)",
# "Fisherova": "Nové Sady jih (Voskovcova)",

def get_start_date() -> str:
    now = datetime.now(timezone.utc)
    last_midnight = now.replace(hour=0, minute=0, second=0, microsecond=0)
    formatted = last_midnight.isoformat().replace("+00:00", "Z")
    return quote(formatted)


def get_api_url() -> str:
    start_date = get_start_date()
    url = f"https://iot.citiq.cloud/portal/api/v2/view/olomouc-traffic-gates/?start={start_date}&window=15m"
    return url


def get_data_to_file(file_path: str, api_key: str):
    with open(file_path, "w", encoding="utf-8") as f:
        response = requests.get(get_api_url(), timeout=30, headers={"Authorization": f"JWT {api_key}"})
        f.write(response.text)


def get_data_to_variable(api_key: str):
    response = requests.get(get_api_url(), timeout=30, headers={"Authorization": f"JWT {api_key}"})
    return response.json()


def load_data(file_path: str):
    with open(file_path, "r", encoding="utf-8") as f:
        data = json.load(f)
        return data


def get_context(api_key: str) -> EnclodAPIOlomoucDataContext:
    data = get_data_to_variable(api_key)
    
    profiles = {}

    for row in data["results"]:
        profiles[row["object_name"]] = None
        # print(len(row["measurements"]))
        state = 0
        for measurement in row["measurements"]:
            if measurement["key"] == "gate_increment_value_sum":
                for value in measurement["values"]:
                    state += value["point"]["value"]

        profiles[row["object_name"]] = state

    zone_entries = {}
    for zone in zone_data.keys():
        zone_entries[zone] = []
        for profile, profile_vacancy in profiles.items():
            if profile in profile_to_zone_mapping:
                zone_name = profile_to_zone_mapping[profile]
                if zone_name == zone:
                    zone_entries[zone].append(profile_vacancy)

    zone_states = {}
    for (zone, entries), zone_info in zip(zone_entries.items(), zone_data.values()):
        if len(entries) == 0:
            zone_states[zone] = None
        else:
            calibrated_sum = sum(entries) + zone_info["KALIBRACE"]
            fraction_zone_vacant = 1 - calibrated_sum / zone_info["KAPACITA"]
            fraction_zone_vacant = max(0.04, min(0.99, fraction_zone_vacant))
            zone_states[zone] = (fraction_zone_vacant, zone_info["KAPACITA"], sum(entries), zone_info["KALIBRACE"])



    return EnclodAPIOlomoucDataContext(context=zone_states)


def read_live_data(
    data_context: EnclodAPIOlomoucDataContext, city_zone: str
):
    """
    Returns fraction of occupied parking spots in the given city zone.
    """
    if city_zone in data_context.context:
        if isinstance(data_context.context[city_zone], tuple):
            fraction_zone_vacant, _, _, _ = data_context.context[city_zone]
            return fraction_zone_vacant
    return None


if __name__ == "__main__":

    import sys
    sys.stdout.reconfigure(encoding='utf-8')

    context = get_context()
    print(context)