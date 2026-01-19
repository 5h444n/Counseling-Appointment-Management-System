@echo off
echo Starting Laravel Server...
php artisan serve --port=8000
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Server exited with error code %ERRORLEVEL%
)
pause
