from database_management import DatabaseMapper
from config_loader import Config
from mysql.connector import Error
import windy_image_scraper
from ultralytics import YOLO
import cv2
import matplotlib.pyplot as plt
from park_place_management import ParkingLotViewer, convert_parking_lot_list, list_parking_places, calculate_availability, cars_in_parking_lots_iou_polygon

import time


def process_all_cameras(database_mapper:DatabaseMapper, pl_viewer:ParkingLotViewer):
    parking_lots = database_mapper.get_all_parking_lots()
    availability = []
    for parking_lot in parking_lots:
        cameras_for_parking_lot = database_mapper.get_cameras_by_parkinglot(parking_lot.id)
        camera = cameras_for_parking_lot[0]
        camera_url = camera.address
        image = windy_image_scraper.read_live_image(camera_url)
        cars_in_image = pl_viewer.scan_cars(image)
        parking_lots_polygons = convert_parking_lot_list(list_parking_places(f"{camera.id}.txt"))
        parking_lot_status = cars_in_parking_lots_iou_polygon(cars_in_image, parking_lots_polygons, 0.15)
        vacancy = calculate_availability(parking_lot_status, parking_lot.car_capacity)
        availability.append(vacancy)
        database_mapper.write_vacancy(parking_lot.id, vacancy)
    return availability
                



if __name__ == "__main__":
    
    config = Config(config_path="config.json")

    database_mapper = DatabaseMapper(config.data["host"], config.data["user"], config.data["password"], config.data["database"], config.data["port"])

    model = YOLO('yolo11l.pt')  # You can also use 'yolov8m.pt' or 'yolov8l.pt' for larger models
    pl_viewer = ParkingLotViewer(model)

    
    
    while True:
        try:
            database_mapper.connect()
            print(process_all_cameras(database_mapper, pl_viewer))
        except KeyboardInterrupt:
            break
        finally:
            database_mapper.disconnect()
        time.sleep(60)



