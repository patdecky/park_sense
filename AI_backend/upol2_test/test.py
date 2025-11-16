import requests
from datetime import datetime, timezone
import json
from urllib.parse import quote



def get_start_date() -> str:
    now = datetime.now(timezone.utc)
    last_midnight = now.replace(hour=0, minute=0, second=0, microsecond=0)
    formatted = last_midnight.isoformat().replace("+00:00", "Z")
    return quote(formatted)

def get_api_url() -> str:
    start_date = get_start_date()
    url = f"https://iot.citiq.cloud/portal/api/v2/view/olomouc-traffic-gates/?start={start_date}&window=15m"
    return url

def get_data_to_variable():
    response = requests.get(get_api_url(), timeout=30)
    return response.json()

import sys
sys.stdout.reconfigure(encoding='utf-8')

print(get_data_to_variable())