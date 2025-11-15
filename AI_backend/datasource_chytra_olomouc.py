import requests
import json
from pathlib import Path
import re
from dataclasses import dataclass
from typing import Any

# URL that returns the parking data as JSON
URL = "https://chytra.olomouc.eu/parking?do=data"
HEADERS = {"X-Requested-With": "XMLHttpRequest"}


@dataclass
class ChytraOlomoucDataContext:
    context: dict[str, int]


def get_context() -> ChytraOlomoucDataContext:
    """
    Fetches the JSON data from the parking endpoint and saves it to a file.
    """
    response = requests.get(URL, headers=HEADERS, timeout=30)
    response.raise_for_status()  # Raise an error for bad HTTP status codes+
    # print(response.text[:100])
    data = response.json()  # Parse the response as JSON

    # Write pretty‑printed JSON to disk

    trimmed = re.sub(
        r"^.*?=\s*", "", data["snippets"]["snippet--data"], flags=re.DOTALL
    )
    trimmed = re.search(r"^(.*?\];)", trimmed, flags=re.DOTALL).group(1)
    trimmed = trimmed[:-1]
    # trimmed += "]"

    data = json.loads(trimmed)

    return ChytraOlomoucDataContext(context={item["id"]: item["occupancy"]["overall"]["free"] for item in data})





if __name__ == "__main__":
    # Example: save to a file named `parking.json` in the current directory
    data = get_context()
    print(data)
