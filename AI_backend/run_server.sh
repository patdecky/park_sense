#!/bin/bash

# Navigate to the directory where the server.py is located
cd /home/deckyp/park_sense/AI_Backend

# Activate the virtual environment
source /home/deckyp/park_sense/venv/bin/activate

# Run the Python server
nohup python3 server.py > /home/deckyp/park_sense/AI_Backend/server.log 2>&1 &

# Get the Process ID (PID) and save it
echo $! > /home/deckyp/park_sense/AI_Backend/server.pid

echo "Server started with PID $(cat /home/deckyp/park_sense/AI_Backend/server.pid). Logs: /home/deckyp/park_sense/AI_Backend/server.log"
