
import json
from unittest import case
from park_place_camera_management import ParkingLotViewer, process_camera_parking_lot
from park_place_olomouc_enclod_api_management import process_api_parking_lot
from database_management import DatabaseMapper
import logging
from typing import Any

import datasource_windy
import datasource_bezp_praha
import datasource_enclod_api_olomouc

log = logging.getLogger(__name__)

"""
Datasource types:

1 - Windy camera
2 - BEZP Praha camera
3 - Olomouc API - enclod senzorova sit

"""

def decode_json_or_string(input_data):
    try:
        return json.loads(input_data)
    except json.JSONDecodeError:
        return input_data


def process_all_datasources(database_mapper:DatabaseMapper, pl_viewer:ParkingLotViewer, context:dict[str, Any]):
    parking_lots = database_mapper.get_all_parking_lots_with_datasource()
    updated_count = 0
    for parking_lot in parking_lots:
        log.debug(f"Processing parking lot {parking_lot.id}")
        datasources_for_parking_lot = database_mapper.get_data_sources_by_parkinglot(parking_lot.id)
        datasource = datasources_for_parking_lot[0] # here we should optimize for multiple data sources, for now its
        
        vacancy = None
        datasource_data = decode_json_or_string(datasource.source)
        
        match datasource.type:
            case 1:
                log.debug("Processing Windy datasource")
                if not isinstance(datasource_data, dict):
                    log.error("Datasource is not a json")
                    continue
                datasouce_object = datasource_windy.WindyDataSource(**datasource_data)
                image = datasource_windy.read_live_image(datasouce_object.url)
                vacancy = process_camera_parking_lot(image, parking_lot.id, parking_lot.car_capacity, pl_viewer)
            case 2:
                log.debug("Processing BEZP Praha datasource")
                if not isinstance(datasource_data, dict):
                    log.error("Datasource is not a json")
                    continue
                datasouce_object = datasource_bezp_praha.BEZPPrahaDataSource(**datasource_data)
                image = datasource_bezp_praha.read_live_image(datasouce_object.url)
                vacancy = process_camera_parking_lot(image, parking_lot.id, parking_lot.car_capacity, pl_viewer)
            case 3:
                log.debug("Processing Olomouc API datasource")
                if not isinstance(datasource_data, str):
                    log.error("Datasource is not a str")
                    continue
                api_data = datasource_enclod_api_olomouc.read_live_data(context["enclod_olomouc_context"], datasource_data)
                vacancy = process_api_parking_lot(api_data, parking_lot.id, parking_lot.car_capacity)
            case _:
                log.error("Unknown datasource type")
        if vacancy is not None:
            updated_count += 1
            log.debug(f"Vacancy: {vacancy}")
            database_mapper.write_vacancy(parking_lot.id, vacancy)
    log.info(f"Updated {updated_count} parking lots from datasources.")
        



if __name__ == "__main__":
    from ultralytics import YOLO
    import cv2
    # Load a pre-trained YOLOv8 model (YOLOv8n is a smaller model, faster to run)
    model = YOLO('yolo11l.pt')  # You can also use 'yolov8m.pt' or 'yolov8l.pt' for larger models

    pl_viewer = ParkingLotViewer(model)

    from database_management import list_tables_in_database, DatabaseMapper
    from config_loader import ConfigNew
    import sys
    sys.stdout.reconfigure(encoding='utf-8')

    config = ConfigNew(config_path="config.json")
    #database_mapper = DatabaseMapper(config.data["host"], config.data["user"], config.data["password"], config.data["database"], config.data["port"])


    database_manager = DatabaseMapper(config.host, config.user,config.password, config.database, config.port)


    database_manager.connect()

    print(process_all_datasources(database_manager, pl_viewer))

    database_manager.disconnect()