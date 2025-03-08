
import json
from park_place_management import ParkingLotViewer, convert_parking_lot_list, list_parking_places, calculate_availability, cars_in_parking_lots_iou_polygon
from database_management import DatabaseMapper
import datasource_windy
import datasource_bezp_praha




def process_all_cameras(database_mapper:DatabaseMapper, pl_viewer:ParkingLotViewer):
    parking_lots = database_mapper.get_all_parking_lots()
    availability = []
    for parking_lot in parking_lots:
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
                print("Unknown datasource type")
        cars_in_image = pl_viewer.scan_cars(image)
        parking_lots_polygons = convert_parking_lot_list(list_parking_places(f"{camera.id}.txt"))
        parking_lot_status = cars_in_parking_lots_iou_polygon(cars_in_image, parking_lots_polygons, 0.15)
        vacancy = calculate_availability(parking_lot_status, parking_lot.car_capacity)
        availability.append(vacancy)
        database_mapper.write_vacancy(parking_lot.id, vacancy)
    return availability
                