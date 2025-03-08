import requests
import numpy as np
import cv2 as cv
import base64
import sys
from dataclasses import dataclass

@dataclass
class BEZPPrahaDataSource:
    url:str


def base64_to_png(base64_string):
    if base64_string.startswith("data:image"):
        base64_string = base64_string.split(",")[1]

    image_data = base64.b64decode(base64_string)
    return image_data

def bin_to_cv_image(bin_data):
    np_array = np.frombuffer(bin_data, np.uint8)
    return cv.imdecode(np_array, cv.IMREAD_COLOR)


def read_live_image(url):

    data = requests.get(url)

    if data.status_code != 200:
        return None

    data = data.json()
    image = bin_to_cv_image(base64_to_png(data["contentBase64"]))
    return image

if __name__ == "__main__":
    url = f"https://bezpecnost.praha.eu/Intens.CrisisPortalInfrastructureApp/cameras/500005/image?format=json"


    image = read_live_image(url)

        
    cv.imshow("image",image)

    cv.imwrite("bezp_images/19.png", image)


    key= cv.waitKey(0)