import mysql.connector
from mysql.connector import Error
from dataclasses import dataclass

def list_tables_in_database(host, user, password, database, port=3306):
    connection = None
    try:
        # Establish the connection
        connection = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database,
            port=port  # Use the port provided by Aiven
        )

        if connection.is_connected():
            print(f"Successfully connected to the database: {database}")
            
            # Create a cursor object
            cursor = connection.cursor()
            
            # Execute the query to list tables
            cursor.execute("SHOW TABLES")
            
            # Fetch all the table names
            tables = cursor.fetchall()
            
            # Print the table names
            print("Tables in the database:")
            for table in tables:
                print(table[0])
    
    except Error as e:
        print(f"Error while connecting to MySQL: {e}")
    
    finally:
        if connection is not None:
            if connection.is_connected():
                cursor.close()
                connection.close()
                print("MySQL connection is closed")

def list_all_capacity(host, user, password, database, port=3306):
    connection = None
    try:
        # Establish the connection
        connection = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database,
            port=port  # Use the port provided by Aiven
        )

        if connection.is_connected():
            print(f"Successfully connected to the database: {database}")
            
            # Create a cursor object
            cursor = connection.cursor()
            
            # Execute the query to list tables
            cursor.execute("SELECT SUM(car_capacity) FROM parkinglot TABLES")
            
            # Fetch all the table names
            tables = cursor.fetchall()
            
            # Print the table names
            print("Tables in the database:")
            for table in tables:
                print(table[0])
    
    except Error as e:
        print(f"Error while connecting to MySQL: {e}")
    
    finally:
        if connection is not None:
            if connection.is_connected():
                cursor.close()
                connection.close()
                print("MySQL connection is closed")

@dataclass
class ParkingLot:
    id:int
    geopos:str
    name:str
    description:str
    car_capacity:int


@dataclass
class DataSource:
    id: int
    parkinglot_id: int
    type: int
    source: str
    

@dataclass
class PLHistory:
    id:int
    parkinglot_id:int
    vacancy:int
    current_timestamp:str

@dataclass
class PLPrediction:
    id:int
    parkinglot_id:int
    vacancy:int
    day:int
    day_timestamp:int

@dataclass
class OccupancyCommunity:
    id:int
    parkinglot_id:int
    occupancy:int
    current_timestamp:str


class DatabaseMapper:
    def __init__(self, host, user, password, database, port):
        self.host=host
        self.user=user
        self.password=password
        self.database=database
        self.port=port

        self.connection = None
        self.cursor = None

    def connect(self):
        self.connection = mysql.connector.connect(
            host=self.host,
            user=self.user,
            password=self.password,
            database=self.database,
            port=self.port  # Use the port provided by Aiven
        )
        if self.connection.is_connected():
            # Create a cursor object
            self.cursor = self.connection.cursor()

    def disconnect(self):
        if self.connection:
            if self.connection.is_connected():
                if self.cursor:
                    self.cursor.close()
                self.connection.close()
                print("MySQL connection is closed")

    def get_all_parking_lots(self)->list[ParkingLot]:
        if self.connection.is_connected():
            
            # Query to retrieve all parking lots
            query = """
                SELECT id, geopos, car_capacity, name, description FROM parkinglot
            """
            self.cursor.execute(query)

            # Fetch all the results
            results = self.cursor.fetchall()

            parking_lots = []
            # Print each parking lot's details
            for row in results:
                parking_lots.append(ParkingLot(id=row[0], geopos=row[1], car_capacity=row[2], name=row[3], description=row[4]))
            return parking_lots
        
    def get_all_parking_lots_with_datasource(self)->list[ParkingLot]:
        if self.connection.is_connected():
            
            # Query to retrieve all parking lots
            query = """
                SELECT DISTINCT p.id, p.geopos, p.car_capacity, p.name, p.description
                FROM parkinglot p
                INNER JOIN data_source d ON p.id = d.parkinglot_id;
            """
            self.cursor.execute(query)

            # Fetch all the results
            results = self.cursor.fetchall()

            parking_lots = []
            # Print each parking lot's details
            for row in results:
                parking_lots.append(ParkingLot(id=row[0], geopos=row[1], car_capacity=row[2], name=row[3], description=row[4]))
            return parking_lots


    def get_data_sources_by_parkinglot(self, parkinglot_id:int) -> list[DataSource]:
        if self.connection.is_connected():
            
            # Query to retrieve all cameras linked to a parking lot
            query = """
                SELECT id, parkinglot_id, type, source
                FROM data_source
                WHERE parkinglot_id = %s
            """
            self.cursor.execute(query, (parkinglot_id,))

            # Fetch all the results
            results = self.cursor.fetchall()


            cameras = []
            # Print each camera's details
            for row in results:
                cameras.append(DataSource(id=row[0], parkinglot_id=row[1], type=row[2], source=row[3]))
            return cameras

    def get_pl_history(self, parkinglot_id:int) -> list[PLHistory]:
        if self.connection.is_connected():
            
            # Query to retrieve all cameras linked to a parking lot
            query = """
                SELECT id, parkinglot_id, vacancy, current_timestamp
                FROM pl_history
                WHERE parkinglot_id = %s
            """
            self.cursor.execute(query, (parkinglot_id,))

            # Fetch all the results
            results = self.cursor.fetchall()


            data = []
            # Print each camera's details
            for row in results:
                data.append(PLHistory(id=row[0], parkinglot_id=row[1], vacancy=row[2], current_timestamp=row[3]))
            return data
        
    def get_pl_prediction(self, parkinglot_id:int) -> list[PLPrediction]:
        if self.connection.is_connected():
            
            # Query to retrieve all cameras linked to a parking lot
            query = """
                SELECT id, parkinglot_id, vacancy, day, day_timestamp
                FROM pl_prediction
                WHERE parkinglot_id = %s
            """
            self.cursor.execute(query, (parkinglot_id,))

            # Fetch all the results
            results = self.cursor.fetchall()


            data = []
            # Print each camera's details
            for row in results:
                data.append(PLPrediction(id=row[0], parkinglot_id=row[1], vacancy=row[2], day=row[3], day_timestamp=row[4]))
            return data
        
    def write_occupancy_community(self, parkinglot_id, occupancy):
        if self.connection.is_connected():
            try:
                query = """
                    INSERT INTO occupancy_community (parkinglot_id, occupancy)
                    VALUES (%s, %s)
                """
                
                # Execute the query with the provided parkinglot_id and address
                self.cursor.execute(query, (parkinglot_id, occupancy))

                # Commit the transaction to ensure the row is inserted
                self.connection.commit()

            except Error as e:
                print(f"Error while inserting the camera: {e}")

    def write_parking_lot(self, geopos:tuple[float, float], car_capacity, name, description) -> int:
        """
        geopos (longitude, latitude)
        """
        if self.connection.is_connected():
            try:
                # Query to insert a new parking lot into the table
                query = """
                    INSERT INTO parkinglot (geopos, car_capacity, name, description)
                    VALUES (ST_GeomFromText(%s), %s, %s, %s)
                """
                
                # Construct the point value as WKT (Well-Known Text) format for the `POINT` type
                point_wkt = f'POINT({geopos[0]} {geopos[1]})'

                # Execute the query with the provided geopos and car_capacity
                self.cursor.execute(query, (point_wkt, car_capacity, name, description))

                # Commit the transaction to ensure the row is inserted
                self.connection.commit()

                # Return the ID of the newly inserted row
                return self.cursor.lastrowid

            except Error as e:
                print(f"Error while inserting the parking lot: {e}")
                return None
            
    def alter_parking_lot(self, parkinglot_id, geopos:tuple[float, float], car_capacity, name, description):
        """
        geopos (longitude, latitude)
        """
        if self.connection.is_connected():
            try:
                # Query to insert a new parking lot into the table
                query = """
                    UPDATE parkinglot
                    SET geopos = ST_GeomFromText(%s), car_capacity = %s, name = %s, description = %s
                    WHERE id = %s
                """
                
                # Construct the point value as WKT (Well-Known Text) format for the `POINT` type
                point_wkt = f'POINT({geopos[0]} {geopos[1]})'

                # Execute the query with the provided geopos and car_capacity
                self.cursor.execute(query, (point_wkt, car_capacity, name, description, parkinglot_id))

                # Commit the transaction to ensure the row is inserted
                self.connection.commit()

            except Error as e:
                print(f"Error while altering the parking lot: {e}")

    def write_data_source(self, parkinglot_id, type, source) -> int:
        if self.connection.is_connected():
            try:
                # Query to insert a new camera into the table
                query = """
                    INSERT INTO data_source (parkinglot_id, type, source)
                    VALUES (%s, %s, %s)
                """
                
                # Execute the query with the provided parkinglot_id and address
                self.cursor.execute(query, (parkinglot_id, type, source))

                # Commit the transaction to ensure the row is inserted
                self.connection.commit()

                return self.cursor.lastrowid

            except Error as e:
                print(f"Error while inserting the datasource: {e}")
                return None

    def alter_data_source(self, data_source_id, parkinglot_id, type, source):
        if self.connection.is_connected():
            try:
                # Query to insert a new camera into the table
                query = """
                    UPDATE data_source
                    SET parkinglot_id = %s, type = %s, source = %s
                    WHERE id = %s
                """
                
                # Execute the query with the provided parkinglot_id and address
                self.cursor.execute(query, (parkinglot_id, type, source, data_source_id))

                # Commit the transaction to ensure the row is inserted
                self.connection.commit()

            except Error as e:
                print(f"Error while inserting the datasource: {e}")

    def write_vacancy(self, parkinglot_id, vacancy):

        if self.connection.is_connected():
            try:
                # Query to insert a new camera into the table
                query = """
                    INSERT INTO pl_history (parkinglot_id, vacancy)
                    VALUES (%s, %s)
                """
                
                # Execute the query with the provided parkinglot_id and address
                self.cursor.execute(query, (parkinglot_id, vacancy))

                # Commit the transaction to ensure the row is inserted
                self.connection.commit()

            except Error as e:
                print(f"Error while inserting the camera: {e}")

    def write_prediction(self, parkinglot_id, vacancy, day, day_timestamp):

        if self.connection.is_connected():
            try:
                # Query to insert a new camera into the table
                query = """
                    INSERT INTO pl_prediction (parkinglot_id, vacancy, day, day_timestamp)
                    VALUES (%s, %s, %s, %s)
                """
                
                # Execute the query with the provided parkinglot_id and address
                self.cursor.execute(query, (parkinglot_id, vacancy, day, day_timestamp))

                # Commit the transaction to ensure the row is inserted
                self.connection.commit()

                return self.cursor.lastrowid

            except Error as e:
                print(f"Error while inserting the camera: {e}")
                return None

    # alter prediction data
    def alter_prediction(self, prediction_id, parkinglot_id, vacancy, day, day_timestamp):
        if self.connection.is_connected():
            try:
                query = """
                    UPDATE pl_prediction
                    SET parkinglot_id = %s, vacancy = %s, day = %s, day_timestamp = %s
                    WHERE id = %s
                """
                self.cursor.execute(query, (parkinglot_id, vacancy, day, day_timestamp, prediction_id))
                self.connection.commit()

            except Error as e:
                print(f"Error while inserting the camera: {e}")

    def get_newest_pl_history(self, parkinglot_id):          
        if self.connection.is_connected():
            try:
                query = """
                    SELECT id, parkinglot_id, vacancy, current_timestamp
                    FROM pl_history
                    WHERE parkinglot_id = %s
                    ORDER BY current_timestamp DESC
                    LIMIT 1
                """
                self.cursor.execute(query, (parkinglot_id,))

                # Fetch the newest row
                result = self.cursor.fetchone()
                
            except Error as e:
                print(f"Error while inserting the camera: {e}")

            return result







