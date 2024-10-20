
from database_management import DatabaseMapper
from mysql.connector import Error
from config_loader import Config


souradnice_lanskroun = (49.912070, 16.611389)# lanskroun
souradnice_mohelnice = (49.776825, 16.918547)# mohelnice

# Example usage
host = "mysql-1b962c14-pat-f5fd.k.aivencloud.com"
port = 13490  # Use the port provided by Aiven, e.g., 12345
user = "server"
password = "AVNS_fa-0sLZI0OCA5OC4YU9"
database = "park_sense"


lanskroun_mist = 85
mohelnice_mist = 42


url_lanskroun = "https://webcams.windy.com/webcams/public/embed/player/1242468341/day"
url_mohelnice = "https://webcams.windy.com/webcams/public/embed/player/1445793627/day"

config = Config(config_path="config.json")
database_mapper = DatabaseMapper(config.data["host"], config.data["user"], config.data["password"], config.data["database"], config.data["port"])

try:
    database_mapper.connect()

    cameras = database_mapper.get_cameras_by_parkinglot(6)
    print(cameras)
    #database_mapper.write_parking_lot(souradnice_mohelnice, mohelnice_mist, "Náměstí Mohelnice")
    #database_mapper.write_camera(5, url_lanskroun)
    #database_mapper.write_camera(6, url_mohelnice)
    #database_mapper.write_vacancy(1, 20)
    #database_mapper.write_vacancy(1, 40)
    #database_mapper.write_vacancy(1, 40)

    #print(database_mapper.get_newest_pl_history(1))



except Error as e:
    print(f"Error while connecting to MySQL: {e}")

finally:
    database_mapper.disconnect()
