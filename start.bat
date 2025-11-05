@echo off
echo ========================================
echo Personal Finance Tracker - Setup and Start
echo ========================================
echo.

echo [1/3] Installing frontend dependencies...
cd frontend
call npm install
if %errorlevel% neq 0 (
    echo Error: Frontend npm install failed
    pause
    exit /b %errorlevel%
)
echo.

echo [2/3] Installing backend dependencies...
cd ..\backend
call composer install
if %errorlevel% neq 0 (
    echo Error: Backend composer install failed
    pause
    exit /b %errorlevel%
)
echo.

echo [3/3] Starting frontend development server...
cd ..\frontend
echo.
echo ========================================
echo Setup complete! Starting dev server...
echo ========================================
echo.
call npm run dev
