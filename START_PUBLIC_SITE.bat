@echo off
echo ========================================
echo   mywallet - Public Access Setup
echo ========================================
echo.
echo This will start your site and make it publicly accessible.
echo.
echo Step 1: Starting PHP Server...
start "PHP Server" cmd /k "cd /d "%~dp0" && php -S localhost:8000"
timeout /t 3 >nul
echo Step 2: Starting Public Tunnel...
echo.
echo IMPORTANT: If ngrok asks you to sign up:
echo 1. Go to https://dashboard.ngrok.com/signup
echo 2. Sign up for FREE account
echo 3. Get your authtoken from https://dashboard.ngrok.com/get-started/your-authtoken
echo 4. Run: ngrok config add-authtoken YOUR_TOKEN_HERE
echo 5. Run this script again
echo.
cd "%USERPROFILE%\Desktop\ngrok"
ngrok.exe http 8000
