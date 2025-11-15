import requests
import json
from pathlib import Path
import re

# URL that returns the parking data as JSON
URL = "https://chytra.olomouc.eu/parking?do=data"
HEADERS = {
    "X-Requested-With": "XMLHttpRequest"
}

def chytra_olomouc_eu_parking() -> None:
    """
    Fetches the JSON data from the parking endpoint and saves it to a file.
    """
    try:
        response = requests.get(URL, headers=HEADERS, timeout=10)
        response.raise_for_status()          # Raise an error for bad HTTP status codes+
        # print(response.text[:100])
        data = response.json()               # Parse the response as JSON

        # Write pretty‑printed JSON to disk
        
        trimmed = re.sub(r'^.*?=\s*', '', data["snippets"]["snippet--data"], flags=re.DOTALL)
        trimmed = re.search(r'^(.*?\];)', trimmed, flags=re.DOTALL).group(1)
        trimmed = trimmed [:-1]
        # trimmed += "]"

        # print(trimmed[:30])
        # print(trimmed[-30:])
        # print(trimmed)
        data = json.loads(trimmed)

    except requests.RequestException as e:
        print(f"❌ Network error: {e}")
    except json.JSONDecodeError:
        print("❌ Response was not valid JSON")
    except OSError as e:
        print(f"❌ File write error: {e}")

if __name__ == "__main__":
    # Example: save to a file named `parking.json` in the current directory
    chytra_olomouc_eu_parking()
