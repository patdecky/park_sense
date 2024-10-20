import mysql.connector
from mysql.connector import Error
from dataclasses import dataclass

def list_tables_in_database(host, user, password, database, port=3306):
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
        if connection.is_connected():
            cursor.close()
            connection.close()
            print("MySQL connection is closed")

@dataclass
class ParkingLot:
    id:int
    car_capacity:int

@dataclass
class Camera:
    id:int
    parkinglot_id:int
    address:str
    


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
                SELECT id, car_capacity FROM parkinglot
            """
            self.cursor.execute(query)

            # Fetch all the results
            results = self.cursor.fetchall()

            parking_lots = []
            # Print each parking lot's details
            for row in results:
                parking_lots.append(ParkingLot(id=row[0], car_capacity=row[1]))
            return parking_lots

    def get_cameras_by_parkinglot(self, parkinglot_id:int) -> list[Camera]:
        if self.connection.is_connected():
            
            # Query to retrieve all cameras linked to a parking lot
            query = """
                SELECT id, parkinglot_id, address
                FROM camera
                WHERE parkinglot_id = %s
            """
            self.cursor.execute(query, (parkinglot_id,))

            # Fetch all the results
            results = self.cursor.fetchall()


            cameras = []
            # Print each camera's details
            for row in results:
                cameras.append(Camera(id=row[0], parkinglot_id=row[1], address=row[2]))
            return cameras


    def write_parking_lot(self, geopos:tuple[float, float], car_capacity, name):
        """
        Inserts a new parking lot into the `parkinglot` table.

        Args:
            geopos (tuple): A tuple of latitude and longitude for the parking lot.
            car_capacity (int): The capacity of the parking lot.

        `id` bigint UNSIGNED NOT NULL,
        `geopos` point NOT NULL,
        `car_capacity` int UNSIGNED NOT NULL
        """
        if self.connection.is_connected():
            try:
                # Query to insert a new parking lot into the table
                query = """
                    INSERT INTO parkinglot (geopos, car_capacity, name)
                    VALUES (ST_GeomFromText(%s), %s, %s)
                """
                
                # Construct the point value as WKT (Well-Known Text) format for the `POINT` type
                point_wkt = f'POINT({geopos[0]} {geopos[1]})'

                # Execute the query with the provided geopos and car_capacity
                self.cursor.execute(query, (point_wkt, car_capacity, name))

                # Commit the transaction to ensure the row is inserted
                self.connection.commit()

            except Error as e:
                print(f"Error while inserting the parking lot: {e}")


    def write_camera(self, parkinglot_id, address):
        """
        Inserts a new camera into the `camera` table.

        Args:
            parkinglot_id (int): The ID of the parking lot to which this camera belongs.
            address (str): The address/location of the camera.
        """
        if self.connection.is_connected():
            try:
                # Query to insert a new camera into the table
                query = """
                    INSERT INTO camera (parkinglot_id, address)
                    VALUES (%s, %s)
                """
                
                # Execute the query with the provided parkinglot_id and address
                self.cursor.execute(query, (parkinglot_id, address))

                # Commit the transaction to ensure the row is inserted
                self.connection.commit()

            except Error as e:
                print(f"Error while inserting the camera: {e}")

    def write_statistics_row(self, day_w, hours, minutes, total_arrival_count):
            """
            Args:
                CREATE TABLE `statistics` (
                `id` bigint UNSIGNED NOT NULL,
                `day_w` tinyint UNSIGNED NOT NULL,
                `hours` tinyint UNSIGNED NOT NULL,
                `minutes` tinyint UNSIGNED NOT NULL,
                `total_arrival_count` int UNSIGNED NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
            """
            if self.connection.is_connected():
                try:
                    # Query to insert a new camera into the table
                    query = """
                        INSERT INTO statistics (day_w, hours, minutes, total_arrival_count)
                        VALUES (%s, %s, %s, %s)
                    """
                    
                    # Execute the query with the provided parkinglot_id and address
                    self.cursor.execute(query, (day_w, hours, minutes, total_arrival_count))

                    # Commit the transaction to ensure the row is inserted
                    self.connection.commit()

                except Error as e:
                    print(f"Error while inserting the camera: {e}")

    def write_vacancy(self, parkinglot_id, vacancy):
        """
        Args:
            CREATE TABLE `pl_history` (
            `id` bigint UNSIGNED NOT NULL,
            `parkinglot_id` bigint UNSIGNED NOT NULL,
            `vacancy` int UNSIGNED NOT NULL,
            `current_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        """
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


    def get_newest_pl_history(self, parkinglot_id):
        """
        Retrieves the newest row from the `pl_history` table.
        
        Args:
            parkinglot_id (int, optional): If provided, filters the results by this parking lot ID.
        
        Returns:
            tuple: The newest record from `pl_history` table or None if no records exist.
        """

            
        if self.connection.is_connected():
            print("Connected to MySQL database")
            
            # Create a cursor object


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

            if result:
                print(f"Newest PL History: ID: {result[0]}, Parking Lot ID: {result[1]}, Vacancy: {result[2]}, Timestamp: {result[3]}")
            else:
                print("No data found in the pl_history table.")

            return result
        
    def get_pl_history(self, parkinglot_id):
        """
        Retrieves the newest row from the `pl_history` table.
        
        Args:
            parkinglot_id (int, optional): If provided, filters the results by this parking lot ID.
        
        Returns:
            tuple: The newest record from `pl_history` table or None if no records exist.
        """

            
        if self.connection.is_connected():
            print("Connected to MySQL database")
            
            # Create a cursor object


            query = """
                SELECT id, parkinglot_id, vacancy, `current_timestamp`
                FROM pl_history
                WHERE parkinglot_id = %s
                ORDER BY `current_timestamp` DESC
            """
            self.cursor.execute(query, (parkinglot_id,))

            # Fetch the newest row
            results = self.cursor.fetchall()


            data = []
            # Print each camera's details
            for result in results:
                data.append(f"Newest PL History: ID: {result[0]}, Parking Lot ID: {result[1]}, Vacancy: {result[2]}, Timestamp: {result[3]}")
            return data







