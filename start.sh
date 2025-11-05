#!/bin/bash

echo "========================================"
echo "Personal Finance Tracker - Setup and Start"
echo "========================================"
echo ""

echo "[1/3] Installing frontend dependencies..."
cd frontend
npm install
if [ $? -ne 0 ]; then
    echo "Error: Frontend npm install failed"
    exit 1
fi
echo ""

echo "[2/3] Installing backend dependencies..."
cd ../backend
composer install
if [ $? -ne 0 ]; then
    echo "Error: Backend composer install failed"
    exit 1
fi
echo ""

echo "[3/3] Starting frontend development server..."
cd ../frontend
echo ""
echo "========================================"
echo "Setup complete! Starting dev server..."
echo "========================================"
echo ""
npm run dev
