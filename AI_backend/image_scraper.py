import requests
import numpy as np
import cv2 as cv


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

    response = requests.get(img_url)

    if response.status_code == 200:
        image_data = response.content
        image = bin_to_cv_image(image_data)

        return image
            

