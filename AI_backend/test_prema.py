from database_management import list_tables_in_database, DatabaseMapper
from config_loader import ConfigNew

config = ConfigNew(config_path="config.json")
#database_mapper = DatabaseMapper(config.data["host"], config.data["user"], config.data["password"], config.data["database"], config.data["port"])

list_tables_in_database(config.host, config.user,config.password, config.database, config.port)



database_manager = DatabaseMapper(config.host, config.user,config.password, config.database, config.port)


database_manager.connect()
print(database_manager.get_all_parking_lots())
database_manager.disconnect()