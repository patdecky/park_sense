import requests
import numpy as np
import cv2 as cv
from dataclasses import dataclass

@dataclass
class WindyDataSource:
    url:str


def read_live_image(url):

    data = requests.get(url)

    data = str(data.text)

    def bin_to_cv_image(bin_data):
        np_array = np.frombuffer(bin_data, np.uint8)
        return cv.imdecode(np_array, cv.IMREAD_COLOR)

    last = 0
    old_last = -1
    img_url = ""
    while old_last != last:
        old_last = last
        starting = data.find("https://images-webcams.windy.com", last)
        if starting == -1:
            break
        #print(starting)
        ending = data.find("?", starting)
        last = ending+1

        img_url_last = img_url
        img_url= data[starting:ending+263]

        if(img_url.find("full") == -1):
            img_url = img_url_last
            continue

        img_name = f"img_{ending}.jpg"

    def extract_webcam_id(url):
        # Extract the webcam ID from the URL
        parts = url.split('/')
        webcam_id = parts[-2]  # The ID is the second to last part
        return webcam_id

    webcam_id = extract_webcam_id(url)

    img_url = f"https://imgproxy.windy.com/_/full/plain/current/{webcam_id}/original.jpg"

    response = requests.get(img_url)

    if response.status_code == 200:
        image_data = response.content
        image = bin_to_cv_image(image_data)

        return image
            

if __name__ == "__main__":
    url = "https://webcams.windy.com/webcams/public/embed/player/1669637039/day"

    cv.imwrite("windy_images/Praha Jih.png", read_live_image(url))
