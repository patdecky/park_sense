#requests api
import requests

def get_data_to_file(file_path:str, api_url:str):
    with open (file_path, "w", encoding="utf-8") as f:
        response = requests.get(api_url)
        f.write(response.text)

url = "https://iot.citiq.cloud/portal/api/v2/view/olomouc-traffic-gates/?start=-1d&window=15m&agg_func=sum"

url = "https://iot.citiq.cloud/portal/api/v2/view/olomouc-traffic-gates/?start=2025-11-15T06%3A00%3A00.000Z&window=15m"

get_data_to_file("profiles_history_from_seven.json", url)

url = "https://iot.citiq.cloud/portal/api/v2/view/olomouc-traffic-gates/?start=2025-11-15T06%3A00%3A00.000Z&window=15m&agg_func=sum"

get_data_to_file("profiles_history_from_seven_agg.json", url)