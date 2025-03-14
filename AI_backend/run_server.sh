#!/bin/bash

# Navigate to the directory where the server.py is located
cd /home/deckyp/park_sense/AI_backend

# Activate the virtual environment
source /home/deckyp/park_sense/AI_backend/venv/bin/activate

# Get the Process ID (PID) and save it
echo $! > /home/deckyp/park_sense/AI_backend/log/server.pid

echo "Server started with PID $(cat /home/deckyp/park_sense/AI_backend/log/server.pid). Logs: /home/deckyp/park_sense/AI_backend/log/server.log"

# Run the Python server
python3 server.py

