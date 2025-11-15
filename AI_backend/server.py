from database_management import DatabaseMapper
from config_loader import ConfigNew
from mysql.connector import Error
import datasource_windy
from ultralytics import YOLO
import cv2
import matplotlib.pyplot as plt
from datasource_processor import process_all_datasources
from park_place_camera_management import ParkingLotViewer, convert_parking_lot_list, list_parking_places, calculate_availability, cars_in_parking_lots_iou_polygon
from datasource_enclod_api_olomouc import get_context as get_enclod_olomouc_context
import logging
import logging.handlers
import argparse
import os


import time

parser = argparse.ArgumentParser(description="Park Sense - AI Backend")
parser.add_argument('-debug', action='store_true', help="Enable debug logging")
args, unknown = parser.parse_known_args()


APP_NAME = "park_sense_ai_backend"
LOG_DIR = "log"
LOG_FILE = os.path.join(LOG_DIR, f"{APP_NAME}.log")

os.makedirs(LOG_DIR, exist_ok=True)


# Configure file handler
file_handler = logging.handlers.TimedRotatingFileHandler(LOG_FILE, when="midnight", interval=1, backupCount=30, encoding="utf-8")
file_formatter = logging.Formatter(
    fmt="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S"
)
file_handler.setFormatter(file_formatter)

# Configure console handler
console_handler = logging.StreamHandler()
console_formatter = logging.Formatter(
    fmt="%(asctime)s - %(name)s - %(levelname)s - %(message)s"
)
console_handler.setFormatter(console_formatter)

# Configure logging
log_level = logging.DEBUG if args.debug else logging.INFO


# Set up the root logger
root = logging.getLogger()
root.setLevel(log_level)
root.addHandler(file_handler)
root.addHandler(console_handler)


def process_all_data(database_mapper:DatabaseMapper, pl_viewer:ParkingLotViewer):
    enclod_olomouc_context = get_enclod_olomouc_context()

    datasource_context = {
        "enclod_olomouc_context": enclod_olomouc_context
    }

    process_all_datasources(database_mapper, pl_viewer, datasource_context)                


if __name__ == "__main__":

    logging.info(f"Starting {APP_NAME}")
    
    config = ConfigNew(config_path="config.json")

    database_mapper = DatabaseMapper(config.host, config.user,config.password, config.database, config.port)

    model = YOLO('yolo11l.pt')  # You can also use 'yolov8m.pt' or 'yolov8l.pt' for larger models
    pl_viewer = ParkingLotViewer(model)

    
    
    while True:
        try:
            logging.info(f"Process start")
            logging.debug("Connecting to database")
            database_mapper.connect()
            logging.debug("Processing all data")
            process_all_data(database_mapper, pl_viewer)
        except Exception:
            logging.exception(f"Server exception.", stack_info=True)
        except KeyboardInterrupt:
            break
        finally:
            database_mapper.disconnect()
            logging.info(f"Process end")
        time.sleep(60)



