from database_management import DatabaseMapper
from config_loader import ConfigNew
from mysql.connector import Error
import datasource_windy
from ultralytics import YOLO
import cv2
import matplotlib.pyplot as plt
from camera_processor import process_all_cameras
from park_place_management import ParkingLotViewer, convert_parking_lot_list, list_parking_places, calculate_availability, cars_in_parking_lots_iou_polygon

import time


def process_all_data(database_mapper:DatabaseMapper, pl_viewer:ParkingLotViewer):
    return process_all_cameras(database_mapper, pl_viewer)                



if __name__ == "__main__":
    
    config = ConfigNew(config_path="config.json")

    database_mapper = DatabaseMapper(config.host, config.user,config.password, config.database, config.port)

    model = YOLO('yolo11l.pt')  # You can also use 'yolov8m.pt' or 'yolov8l.pt' for larger models
    pl_viewer = ParkingLotViewer(model)

    
    
    while True:
        try:
            database_mapper.connect()
            print(process_all_data(database_mapper, pl_viewer))
        except KeyboardInterrupt:
            break
        finally:
            database_mapper.disconnect()
        time.sleep(60)



