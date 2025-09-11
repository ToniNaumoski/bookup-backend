# Pusher WebSocket Setup Guide

## Quick Setup (5 minutes)

### Step 1: Create Free Pusher Account
1. Go to [pusher.com](https://pusher.com)
2. Click "Get Started Free"
3. Sign up with your email
4. Create a new app

### Step 2: Get Your Credentials
From your Pusher dashboard, copy:
- **App ID**
- **Key** 
- **Secret**
- **Cluster** (usually `mt1` or `us2`)

### Step 3: Update Backend .env
Add these to your `backend/my-api/.env` file:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id-here
PUSHER_APP_KEY=your-app-key-here
PUSHER_APP_SECRET=your-app-secret-here
PUSHER_APP_CLUSTER=mt1
```

### Step 4: Update Frontend .env
Create/update `frontend/.env`:

```env
VITE_PUSHER_APP_KEY=your-app-key-here
VITE_PUSHER_APP_CLUSTER=mt1
```

### Step 5: Install Dependencies
```bash
# Backend
cd backend/my-api
composer install

# Frontend
cd frontend
npm install
```

### Step 6: Start Servers
```bash
# Terminal 1 - Backend
cd backend/my-api
php artisan serve

# Terminal 2 - Frontend
cd frontend
npm run dev
```

### Step 7: Test WebSockets
1. Go to `/super-admin-login`
2. Login with: `admin@bookiraj.com` / `Admin123!`
3. Open another browser window
4. Register as a business user
5. Create a business
6. Watch the super admin dashboard for real-time notifications!

## Pusher Free Tier Limits
- 200,000 messages/day
- 100 concurrent connections
- Perfect for development and small projects

## Troubleshooting

### Connection Issues:
- Check if Pusher credentials are correct
- Verify both servers are running
- Check browser console for errors

### No Notifications:
- Ensure super admin is logged in
- Check if WebSocket service is initialized
- Test by creating new business/reservation

## Alternative: Test Without WebSockets
If you want to test the system without WebSockets first:
1. Comment out the WebSocket initialization in SuperAdminDashboard.vue
2. The approval system will still work
3. You just won't get real-time notifications


