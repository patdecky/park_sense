# Import necessary libraries
from ultralytics import YOLO
import cv2
import matplotlib.pyplot as plt

# Load a pre-trained YOLOv8 model (YOLOv8n is a smaller model, faster to run)
model = YOLO('yolo11l.pt')  # You can also use 'yolov8m.pt' or 'yolov8l.pt' for larger models

# Load an image where you want to detect cars
image_path = 'mohelnice_full.jpg'
img = cv2.imread(image_path)

# Run the YOLOv8 model on the image
results = model(img)

# results is a list, so you need to access individual result objects
for result in results:
    car_detections = [box for box in result.boxes if int(box.cls[0]) == 2]
    
    # Annotate image manually with only car boxes
    annotated_image = img.copy()
    
    for car in car_detections:
        # Extract box coordinates (xyxy format)
        x1, y1, x2, y2 = car.xyxy[0]
        # Draw the bounding box for the car
        cv2.rectangle(annotated_image, (int(x1), int(y1)), (int(x2), int(y2)), (0, 255, 0), 2)
        # Add label and confidence
        label = f"Car {car.conf[0]:.2f}"
        cv2.putText(annotated_image, label, (int(x1), int(y1)-10), cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 255, 0), 2)
    
    # Save and display the image with only car detections
    cv2.imwrite('cars_only_annotated_image.jpg', annotated_image)
    
    plt.imshow(cv2.cvtColor(annotated_image, cv2.COLOR_BGR2RGB))
    plt.axis('off')  # Hide axes
    plt.show()