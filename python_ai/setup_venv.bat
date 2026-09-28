@echo off
title DermaVision AI - One Click VENV Setup
echo ==========================================
echo   DermaVision AI - Python Environment Setup
echo ==========================================
echo.

REM === MOVE TO SCRIPT DIRECTORY ===
cd /d "%~dp0"

REM === CHECK PYTHON ===
echo Checking Python installation...
python --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Python not found.
    echo Please install Python 3.9 / 3.10 / 3.11 and try again.
    pause
    exit /b
)

REM === REMOVE OLD VENV ===
if exist .venv (
    echo Removing old virtual environment...
    rmdir /s /q .venv
)

REM === CREATE VENV ===
echo Creating virtual environment...
python -m venv .venv
if errorlevel 1 (
    echo ❌ Failed to create virtual environment.
    pause
    exit /b
)

REM === ACTIVATE VENV ===
echo Activating virtual environment...
call .venv\Scripts\activate

REM === UPGRADE PIP ===
echo Upgrading pip...
python -m pip install --upgrade pip

REM === INSTALL REQUIRED PACKAGES ===
echo Installing required AI libraries...
pip install numpy pillow tensorflow==2.13.0

REM === VERIFY INSTALLATION ===
echo Verifying installation...
python - <<EOF
import numpy, PIL, tensorflow
print("===================================")
print("✅ VENV READY & VERIFIED")
print("NumPy:", numpy.__version__)
print("Pillow:", PIL.__version__)
print("TensorFlow:", tensorflow.__version__)
print("===================================")
EOF

echo.
echo 🎉 SETUP COMPLETE! YOU CAN NOW RUN AI 🎉
echo.
pause
