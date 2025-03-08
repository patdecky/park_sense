from dataclasses import dataclass
from database_management import DatabaseMapper
import json

@dataclass
class ParkingLotSource:
    longitude:float
    latitude:float
    name:str
    car_capacity:int
    description:str

class ParkingLotSourceLoader:
    def __init__(self, config_path):
        with open(config_path, "r", encoding="utf-8") as f:
            self.data:dict = json.load(f)
    
    def create_parkinglot_sources(self):
        parking_lots = []
        for data in self.data:
            parking_lots.append(ParkingLotSource(**data))
        return parking_lots

    
@dataclass
class DataSourceSource:
    parkinglot_name: str
    type: int
    data: str

class DataSourceSourceLoader:
    def __init__(self, config_path):
        with open(config_path, "r", encoding="utf-8") as f:
            self.data:dict = json.load(f)
    
    def create_datasource_sources(self):
        parking_lots = []
        for data in self.data:
            parking_lots.append(DataSourceSource(**data))
        return parking_lots



class Loader:
    def __init__(self, parking_lots_source_loader:ParkingLotSourceLoader, datasources_source_loader:DataSourceSourceLoader, database_mapper:DatabaseMapper):
        self.parking_lots_source_loader = parking_lots_source_loader
        self.datasources_source_loader = datasources_source_loader
        self.database_mapper = database_mapper
        self.parkinglot_sources:list[ParkingLotSource] = []
        self.datasource_sources:list[DataSourceSource] = []

    def create_sources(self):
        self.parkinglot_sources = self.parking_lots_source_loader.create_parkinglot_sources()
        self.datasource_sources = self.datasources_source_loader.create_datasource_sources()

    
    def insert_parkinglot_into_database(self):
        parkinglot_ids = []
        for parkinglot in self.parkinglot_sources:
            parkinglot_ids.append(self.database_mapper.write_parking_lot(
                (parkinglot.longitude, parkinglot.latitude),
                parkinglot.car_capacity,
                parkinglot.name,
                parkinglot.description
            ))
        return parkinglot_ids

    def alter_parkinglot_into_database(self, existing_parkinglot_ids:list):
        for parkinglot, existing_id in zip(self.parkinglot_sources, existing_parkinglot_ids):
            self.database_mapper.alter_parking_lot(
                existing_id,
                (parkinglot.longitude, parkinglot.latitude),
                parkinglot.car_capacity,
                parkinglot.name,
                parkinglot.description
            )

    def insert_datasource_into_database(self, parkinglot_ids:list):
        datasource_ids = []
        for datasource in self.datasource_sources:
            for parkinglot_id, parkinglot_soruce in zip(parkinglot_ids, self.parkinglot_sources):
                if parkinglot_soruce.name == datasource.parkinglot_name:
                    datasource_ids.append(self.database_mapper.write_data_source(
                        parkinglot_id,
                        datasource.type,
                        datasource.data
                    ))
        return datasource_ids

    def alter_datasource_into_database(self, parkinglot_ids:list, existing_datasource_ids:list):
        for datasource, existing_datasource_id in zip(self.datasource_sources, existing_datasource_ids):
            for parkinglot_id, parkinglot_soruce in zip(parkinglot_ids, self.parkinglot_sources):
                if parkinglot_soruce.name == datasource.parkinglot_name:
                    self.database_mapper.alter_data_source(
                        existing_datasource_id,
                        parkinglot_id,
                        datasource.type,
                        datasource.data
                    )


if __name__ == "__main__":

    import sys
    sys.stdout.reconfigure(encoding='utf-8')

    parking_lot = ParkingLotSourceLoader("datasource/parkinglots_praha_api.json")


    data_loader = DataSourceSourceLoader("datasource/datasource_praha_bezp_1.json")

    from config_loader import ConfigNew

    config = ConfigNew(config_path="config.json")

    database_manager = DatabaseMapper(config.host, config.user,config.password, config.database, config.port)
    database_manager.connect()

    loader = Loader(parking_lot, data_loader, database_manager)

    loader.create_sources()
    lot_ids = loader.insert_parkinglot_into_database()
    print(lot_ids)
    #datasource_ids = loader.insert_datasource_into_database([17, 18, 19])
    #print(datasource_ids)

    #print(loader.alter_parkinglot_into_database([1,2,3,4]))
    #print(loader.alter_datasource_into_database([1,2,3,4], [1,2,3,4]))
    #print(loader.insert_datasource_into_database([4]))

    database_manager.disconnect()
