
from database_management import DatabaseMapper
from mysql.connector import Error
from config_loader import Config




def list_statistics_data(file_path:str) -> list[list[int]]:
    pkm_coordinates = []
    with open(file_path, 'r') as file:
        pkm_lines = file.readlines()    
        for line in pkm_lines[1:]:
            st_line = line.strip()
            sp_line = list(st_line.split(";"))
            pkm_coordinates.append(sp_line)
    return pkm_coordinates


url = "https://webcams.windy.com/webcams/public/embed/player/1242468341/day"






config = Config(config_path="config.json")
database_mapper = DatabaseMapper(config.data["host"], config.data["user"], config.data["password"], config.data["database"], config.data["port"])

try:
    
    database_mapper.connect()

    cameras = database_mapper.get_cameras_by_parkinglot(1)
    print(cameras)
    #database_mapper.write_parking_lot((49.912070, 16.611389), 150)
    #database_mapper.write_camera(1, url)
    for row in list_statistics_data("plotly_data.csv"):
    
        database_mapper.write_statistics_row(row[0], row[1], row[2], row[3])


except Error as e:
    print(f"Error while connecting to MySQL: {e}")

finally:
    database_mapper.disconnect()
   
