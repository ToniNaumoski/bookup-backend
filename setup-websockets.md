# Laravel WebSockets Setup Instructions

## Step 1: Install Dependencies
```bash
cd backend/my-api
composer install
```

## Step 2: Add to .env file
Add these lines to your `backend/my-api/.env` file:

```env
# Laravel WebSockets Configuration
BROADCAST_DRIVER=pusher

# Local WebSocket Server Settings
PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_APP_CLUSTER=mt1

# WebSocket Server Host and Port
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http

# Laravel WebSockets Dashboard Port
LARAVEL_WEBSOCKETS_PORT=6001
```

## Step 3: Publish and Run Migrations
```bash
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
php artisan migrate
```

## Step 4: Clear Caches
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

## Step 5: Start the Servers

### Terminal 1 - Laravel API Server
```bash
cd backend/my-api
php artisan serve
```

### Terminal 2 - WebSocket Server
```bash
cd backend/my-api
php artisan websockets:serve
```

### Terminal 3 - Frontend Development Server
```bash
cd frontend
npm run dev
```

## Step 6: Test the Setup

1. **Access WebSocket Dashboard:**
   - Go to: http://127.0.0.1:8000/laravel-websockets
   - You should see the WebSocket dashboard

2. **Test Super Admin Login:**
   - Go to: http://localhost:3000/super-admin-login
   - Login with: admin@bookiraj.com / Admin123!

3. **Test Real-time Notifications:**
   - Open another browser window
   - Register as a business user
   - Create a business
   - Watch the super admin dashboard for notifications

## Troubleshooting

### WebSocket Connection Issues:
- Make sure all three servers are running
- Check if port 6001 is available
- Verify .env configuration

### No Notifications:
- Check browser console for errors
- Verify WebSocket server is running
- Test by creating a new business/reservation

### Dashboard Not Accessible:
- Ensure Laravel server is running on port 8000
- Check if routes are properly registered



