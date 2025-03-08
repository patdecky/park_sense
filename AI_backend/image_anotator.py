import cv2

# Global variables to store the points and image
points = []
image = None

def click_event(event, x, y, flags, params):
    global points, image

    # Check for left mouse button click
    if event == cv2.EVENT_LBUTTONDOWN:
        points.append((x, y))
        
        # Draw a small circle at the click point for visualization
        cv2.circle(image, (x, y), 5, (0, 255, 0), -1)
        
        # If 4 points have been selected, draw the polygon
        if len(points) == 4:
            cv2.line(image, points[0], points[1], (255, 0, 0), 2)
            cv2.line(image, points[1], points[2], (255, 0, 0), 2)
            cv2.line(image, points[2], points[3], (255, 0, 0), 2)
            cv2.line(image, points[3], points[0], (255, 0, 0), 2)
            
            # Print the points in the desired format
            print(f"{points[0][0]} {points[0][1]} {points[1][0]} {points[1][1]} "
                  f"{points[2][0]} {points[2][1]} {points[3][0]} {points[3][1]}")
            
            points = []  # Clear points after selection

        cv2.imshow("Image", image)

parkinglot_id = 18

# Load the image
image_path = f'bezp_images/{parkinglot_id}.png'  # Update this to the correct path if needed
image = cv2.imread(image_path)

# Create a window and set the mouse callback function to capture clicks
cv2.imshow("Image", image)
cv2.setMouseCallback("Image", click_event)

# Keep the window open until a key is pressed
cv2.waitKey(0)
cv2.destroyAllWindows()
