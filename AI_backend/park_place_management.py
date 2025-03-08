from shapely.geometry import Polygon, box
import cv2
import numpy as np



def list_parking_places(file_path:str) -> list[list[int]]:
    pkm_coordinates = []
    with open(file_path, 'r') as file:
        pkm_lines = file.readlines()
        iou = pkm_lines[0]
        pkm_lines = pkm_lines[1:]
        for line in pkm_lines:
            st_line = line.strip()
            if st_line != "":
                sp_line = list(st_line.split(" "))
                pkm_coordinates.append(sp_line)
    return float(iou), pkm_coordinates





def calculate_iou_polygon(car_bbox, parking_lot_polygon):
    """
    Calculate IoU between a rectangular car bounding box and a polygonal parking lot.

    Args:
        car_bbox (tuple): Bounding box of the car (x1, y1, x2, y2).
        parking_lot_polygon (list): List of 4 points representing the polygon [(x1, y1), (x2, y2), (x3, y3), (x4, y4)].

    Returns:
        float: IoU value (between 0 and 1).
    """
    # Create a rectangle (box) for the car bounding box
    car_rect = box(car_bbox[0], car_bbox[1], car_bbox[2], car_bbox[3])
    
    # Create a polygon for the parking lot
    parking_lot_poly = Polygon(parking_lot_polygon)

    # Calculate the intersection area between the car bounding box and the parking lot polygon
    intersection_area = car_rect.intersection(parking_lot_poly).area

    # Calculate the union area
    union_area = car_rect.area + parking_lot_poly.area - intersection_area

    # Compute the IoU (Intersection over Union)
    iou = intersection_area / union_area if union_area > 0 else 0

    return iou


def cars_in_parking_lots_iou_polygon(car_bboxes, parking_lot_polygons, iou_threshold=0.5):
    """
    For each polygonal parking lot, check if there is a car in it using IoU.

    Args:
        car_bboxes (list): List of car bounding boxes [(x1, y1, x2, y2), ...].
        parking_lot_polygons (list): List of parking lot polygons, each polygon as a list of 4 points [(x1, y1), (x2, y2), (x3, y3), (x4, y4)].
        iou_threshold (float): IoU threshold to decide if a car is in a parking lot (default is 0.5).

    Returns:
        list: List of booleans, where each element corresponds to a parking lot and is True
              if the IoU between a car and the parking lot polygon exceeds the threshold, False otherwise.
    """
    results = []
    for lot_polygon in parking_lot_polygons:
        car_in_lot = False
        for car_bbox in car_bboxes:
            iou = calculate_iou_polygon(car_bbox, lot_polygon)
            if iou >= iou_threshold:
                car_in_lot = True
                break  # No need to check further if a car is found in this lot
        results.append(car_in_lot)
    
    return results




def convert_parking_lot_to_polygon(parking_lot:list[int])->list[list[int]]:
    new_list = []
    for i in range(0, len(parking_lot),2):
        new_list.append([parking_lot[i], parking_lot[i+1]])
    return new_list


def convert_parking_lot_list(parking_lots:list[list[int]]) -> list[list[list[int]]]:
    new_lot_list = []
    for parking_lot in parking_lots:
        new_lot_list.append(convert_parking_lot_to_polygon(parking_lot))
    return new_lot_list


class ParkingLotViewer:
    def __init__(self, model):
        self.model = model

    
    def scan_cars(self, image):
        result = self.model(image)[0]

        car_detections = [box for box in result.boxes if int(box.cls[0]) == 2]
    
        car_boxes = []
        for car in car_detections:
            # Extract box coordinates (xyxy format)
            x1, y1, x2, y2 = car.xyxy[0]
            car_boxes.append([int(x1), int(y1), int(x2), int(y2)])
        return car_boxes
    
    def draw_boxes(self, image, boxes:list[list[int]]):
        for box in boxes:
            cv2.rectangle(image, (box[0], box[1]), (box[2], box[3]), (0, 255, 0), 1)
        return image

    def draw_parking_spots(self, image, polygons:list[list[list[int]]], occupancy:list[bool]):
        for polygon, occupied in zip(polygons, occupancy):
            points = np.array(polygon, np.int32)
            points = points.reshape((-1, 1, 2))
            if occupied:
                cv2.polylines(image, [points], True, (0,0,255), 2)
            else:
                cv2.polylines(image, [points], True, (0,255,255), 2)

def calculate_availability(status:list[bool], total_capacity):
    camera_capacity = len(status)
    camera_occupied = sum(status)
    if camera_capacity == 0:
        return 0
    camera_occupied_percentage = camera_occupied/camera_capacity
    estimated_vacancy = int(round((1-camera_occupied_percentage)*total_capacity))
    return estimated_vacancy

def process_parking_lot(image, parkinglot_id:int, parkinglot_capacity:int, pl_viewer:ParkingLotViewer):
    cars_in_image = pl_viewer.scan_cars(image)
    iou, polygons = list_parking_places(f"annotations/{parkinglot_id}.txt")
    parking_lots_polygons = convert_parking_lot_list(polygons)
    parking_lot_status = cars_in_parking_lots_iou_polygon(cars_in_image, parking_lots_polygons, iou)
    vacancy = calculate_availability(parking_lot_status, parkinglot_capacity)
    #print(len(cars_in_image), len(parking_lots_polygons), vacancy)
    return vacancy

if __name__ == "__main__":

        # Import necessary libraries
    from ultralytics import YOLO
    import cv2
    import matplotlib.pyplot as plt

    # Load a pre-trained YOLOv8 model (YOLOv8n is a smaller model, faster to run)
    model = YOLO('yolo11l.pt')  # You can also use 'yolov8m.pt' or 'yolov8l.pt' for larger models

    parkinglot_id = 18

    # Load an image where you want to detect cars
    image_path = f'bezp_images/{parkinglot_id}.png'
    img = cv2.imread(image_path)


    pl_viewer = ParkingLotViewer(model)
    # Parking lot polygons [(x1, y1), (x2, y2), (x3, y3), (x4, y4)]
    # Car bounding boxes [(x1, y1, x2, y2)]
    car_bboxes = pl_viewer.scan_cars(img)

    pl_viewer.draw_boxes(img, car_bboxes)

    iou, polygons = list_parking_places(f"annotations/{parkinglot_id}.txt")
    
    parking_lots_polygons = convert_parking_lot_list(polygons)



    # Check for each polygonal parking lot if there is a car in it using IoU threshold of 0.5
    parking_lot_status = cars_in_parking_lots_iou_polygon(car_bboxes, parking_lots_polygons, iou)
    
    
    pl_viewer.draw_parking_spots(img, parking_lots_polygons, parking_lot_status)


    cv2.imshow("labeled", img)
    cv2.imwrite(f"labeled/{parkinglot_id}.png", img)
    cv2.waitKey(0)

    print(parking_lot_status)  # Output: [True, True, False]






