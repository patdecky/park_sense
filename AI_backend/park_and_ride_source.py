from dataclasses import dataclass
from database_management import DatabaseMapper
import json




@dataclass
class ParkAndRideSource:
    longitude:float
    latitude:float
    name:str
    car_capacity:int
    description:str

@dataclass
class ParkAndRideData:
    day_timestamp: int
    vacancy:int
    weekday:int

class ParkAndRideSourceLoader:
    def __init__(self, config_path):
        self.lines = None
        self.header = None
        with open(config_path, "r", encoding="utf-8") as f:
            self.lines = f.readlines()
            self.header = self.lines[0].split("\t")
            self.lines = [x.split("\t")[1:] for x in self.lines[1:]]

    def create_parkinglot_source(self):
        return ParkAndRideSource(self.header[2], self.header[3], self.header[0], self.header[1], "")

    def read_park_and_ride_data(self):
        data = []
        for i in range(7):
            for time, vacancy in zip (self.lines[i*2], self.lines[i*2+1]):
                data.append(ParkAndRideData(time, vacancy, i))
        return data
    

class Loader:
    def __init__(self, parking_lots_source_loader:ParkAndRideSourceLoader, database_mapper:DatabaseMapper):
        self.parking_lots_source_loader = parking_lots_source_loader
        self.database_mapper = database_mapper
        self.parkinglot_source:ParkAndRideSource
        self.data = []

    def create_sources(self):
        self.parkinglot_source = self.parking_lots_source_loader.create_parkinglot_source()
        self.data = self.parking_lots_source_loader.read_park_and_ride_data()

    
    def insert_parkinglot_into_database(self):
        return self.database_mapper.write_parking_lot(
            (self.parkinglot_source.longitude, self.parkinglot_source.latitude),
            self.parkinglot_source.car_capacity,
            self.parkinglot_source.name,
            self.parkinglot_source.description
        )

    def alter_parkinglot_into_database(self, existing_parkinglot_id:int):
        self.database_mapper.alter_parking_lot(
            existing_parkinglot_id,
            (self.parkinglot_source.longitude, self.parkinglot_source.latitude),
            self.parkinglot_source.car_capacity,
            self.parkinglot_source.name,
            self.parkinglot_source.description
        )

    def insert_data_into_database(self, parkinglot_id:int):
        data_ids = []
        for single_data in self.data:
            data_ids.append(self.database_mapper.write_prediction(
                parkinglot_id,
                single_data.vacancy,
                single_data.weekday,
                single_data.day_timestamp
            ))
        return data_ids

    def alter_data_into_database(self, parkinglot_id:int, existing_datasource_ids:list):
        for single_data, existing_datasource_id in zip(self.data, existing_datasource_ids):
            self.database_mapper.alter_prediction(
                existing_datasource_id,
                parkinglot_id,
                single_data.vacancy,
                single_data.weekday,
                single_data.day_timestamp
            )





if __name__ == "__main__":

    import sys
    sys.stdout.reconfigure(encoding='utf-8')

    loader = ParkAndRideSourceLoader("park_and_ride_data/11.txt")
    #print(loader.create_parkinglot_source())
    #print(loader.read_park_and_ride_data())

    from config_loader import ConfigNew

    config = ConfigNew(config_path="config.json")

    database_manager = DatabaseMapper(config.host, config.user,config.password, config.database, config.port)
    database_manager.connect()

    loader = Loader(loader, database_manager)

    loader.create_sources()
    parkinglot_id = loader.insert_parkinglot_into_database()
    print(parkinglot_id, loader.parkinglot_source.name)
    print(loader.insert_data_into_database(parkinglot_id))
    #print(loader.alter_parkinglot_into_database([1,2,3,4]))
    #print(loader.alter_datasource_into_database([1,2,3,4], [1,2,3,4]))
    #print(loader.insert_datasource_into_database([4]))

    database_manager.disconnect()
