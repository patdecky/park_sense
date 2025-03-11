
import json
from park_place_management import ParkingLotViewer, process_parking_lot
from database_management import DatabaseMapper
import datasource_windy
import datasource_bezp_praha
import logging

log = logging.getLogger(__name__)



def process_all_cameras(database_mapper:DatabaseMapper, pl_viewer:ParkingLotViewer):
    parking_lots = database_mapper.get_all_parking_lots_with_datasource()
    for parking_lot in parking_lots:
        log.debug(f"Processing parking lot {parking_lot.id}")
        datasources_for_parking_lot = database_mapper.get_data_sources_by_parkinglot(parking_lot.id)
        datasource = datasources_for_parking_lot[0]
        match datasource.type:
            case 1:
                datasource_data = json.loads(datasource.source)
                datasouce_object = datasource_windy.WindyDataSource(**datasource_data)
                image = datasource_windy.read_live_image(datasouce_object.url)
            case 2:
                datasource_data = json.loads(datasource.source)
                datasouce_object = datasource_bezp_praha.BEZPPrahaDataSource(**datasource_data)
                image = datasource_bezp_praha.read_live_image(datasouce_object.url)
            case _:
                log.error("Unknown datasource type")

        vacancy = process_parking_lot(image, parking_lot.id, parking_lot.car_capacity, pl_viewer)
        log.debug(f"Vacancy: {vacancy}")
        database_mapper.write_vacancy(parking_lot.id, vacancy)



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

    print(process_all_cameras(database_manager, pl_viewer))

    database_manager.disconnect()