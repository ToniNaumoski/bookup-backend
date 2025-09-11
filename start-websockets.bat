@echo off
echo Starting Laravel WebSocket Server...
echo.
echo Make sure you have:
echo 1. Added the WebSocket configuration to your .env file
echo 2. Run: php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
echo 3. Run: php artisan migrate
echo.
echo Starting WebSocket server on port 6001...
echo Dashboard will be available at: http://127.0.0.1:8000/laravel-websockets
echo.
php artisan websockets:serve
pause


