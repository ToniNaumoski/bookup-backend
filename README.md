# 🛡️ BookUp Backend - Laravel 12 API

This is the backend service for the **BookUp** appointment booking system. Built on **Laravel 12**, it operates as a stateless RESTful API providing authentication, role management, business listings, appointment reservation scheduling, and admin review boards, along with WebSocket broadcasting for real-time notifications.

---

## 🛠️ Tech Stack & Requirements

*   **PHP**: `^8.2`
*   **Framework**: Laravel 12
*   **Authentication**: Laravel Sanctum (Token-based authentication)
*   **Database**: SQLite (Default) or MySQL
*   **WebSockets**: Pusher PHP Server / BeyondCode Laravel WebSockets (for local server)
*   **Queueing**: Database driver (for processing background notifications)

---

## 📁 Key Directory Structure

```
Bookup-Backend/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php            # Login, registration, & user profiles
│   │       ├── CreateBusinessController.php   # Business onboarding & detail setup
│   │       ├── PublicBusinessController.php   # Public search, filters, & slot checks
│   │       ├── ReservationController.php      # Booking, confirming, & canceling slots
│   │       └── SuperAdminController.php       # Dashboard stats, business review & user control
│   └── Models/
│       ├── User.php                          # Roles: admin, business, user
│       ├── Business.php                      # Profiles, business hours, coordinates
│       ├── Category.php                      # Business category types
│       ├── Subcategory.php                   # Specialized categories
│       ├── City.php                          # Cities for location-based search
│       └── Reservation.php                   # Bookings with status and timestamps
├── config/                                   # Laravel configuration files
├── database/
│   ├── migrations/                           # DB schemas for listings, bookings, etc.
│   └── seeders/
│       ├── DatabaseSeeder.php                # Master seeder
│       ├── SuperAdminSeeder.php              # Bootstraps super admin credentials
│       ├── CategorySeeder.php                # Populates categories/subcategories
│       └── CitiesSeeder.php                  # Populates cities search index
├── routes/
│   └── api.php                               # RESTful API routes (Sanctum protected)
├── fix-super-admin.php                       # Quick CLI utility to repair/create admin account
├── start-websockets.bat                      # Windows script to spin up websocket servers
└── start-websockets.sh                       # Unix script to spin up websocket servers
```

---

## 🚀 Setup & Installation

Follow these steps to deploy the API locally:

### 1. Install Dependencies
Make sure you have PHP 8.2+ and Composer installed on your system.
```bash
composer install
```

### 2. Configure Environment Variables
Copy the template configuration file:
```bash
cp .env.example .env
```
Generate the application key:
```bash
php artisan key:generate
```

### 3. Database Initialization (SQLite)
By default, the application is configured to use a local **SQLite** database.
1. Create the database file:
   *   **Windows (PowerShell)**: `New-Item -ItemType File -Path database/database.sqlite`
   *   **Bash**: `touch database/database.sqlite`
2. Run database migrations and seed default records (cities, categories, admin user):
   ```bash
   php artisan migrate --seed
   ```

### 4. Admin Credentials
The database seeder creates a default **Super Admin** account:
*   **Email**: `admin@bookiraj.com`
*   **Password**: `Admin123!`

If you ever need to restore or force-update the Super Admin credentials, run the helper script:
```bash
php fix-super-admin.php
```

---

## 📡 WebSockets & Real-Time Setup

The system broadcasts real-time notifications to the Admin Dashboard when new businesses register or reservations are made.

### Option A: Using Pusher (Production/Cloud)
1. Register a free account at [pusher.com](https://pusher.com).
2. Create a channel app and update your backend `.env` variables:
   ```env
   BROADCAST_CONNECTION=pusher
   PUSHER_APP_ID=your-app-id
   PUSHER_APP_KEY=your-app-key
   PUSHER_APP_SECRET=your-app-secret
   PUSHER_APP_CLUSTER=eu
   ```

### Option B: Local WebSocket Server (Development)
If you prefer not to use the cloud service, you can run a local WebSocket server:
1. Update `.env` with local parameters:
   ```env
   BROADCAST_CONNECTION=pusher
   PUSHER_APP_ID=local
   PUSHER_APP_KEY=local
   PUSHER_APP_SECRET=local
   PUSHER_APP_CLUSTER=mt1
   PUSHER_HOST=127.0.0.1
   PUSHER_PORT=6001
   PUSHER_SCHEME=http
   ```
2. Start the WebSocket server using the provided helper script:
   *   **Windows**: Double-click `start-websockets.bat` (or run it in terminal)
   *   **Mac/Linux**: Run `./start-websockets.sh` (or `php artisan websockets:serve`)

---

## 💻 Running the Servers

For complete functionality, you need the API server and the queue worker running:

*   **API Server**: `php artisan serve` (Runs at `http://127.0.0.1:8000`)
*   **Queue Worker**: `php artisan queue:listen` (Required to process notifications/broadcasts)

---

## 🔒 Primary API Endpoints

### 🔑 Authentication
*   `POST /api/login` - Authenticate and retrieve bearer token.
*   `POST /api/register/user` - Register as a client.
*   `POST /api/register/business` - Register as a business owner.
*   `POST /api/logout` - Revoke current session token (Sanctum).

### 🏢 Businesses (Public)
*   `GET /api/businesses/approved` - Paginated, filtered list of approved businesses.
*   `GET /api/businesses/{id}` - Details of a specific business.
*   `GET /api/businesses/{id}/available-slots` - Fetch open time slots for booking.

### 📅 Booking & Reservations
*   `POST /api/reservations` - Create a booking (Authenticated).
*   `GET /api/reservations` - Get history of reservations for current client/owner.
*   `PUT /api/reservations/{id}/cancel` - Cancel a reservation.

### 👑 Super Admin (Protected)
*   `GET /api/admin/stats` - Retrieve dashboard analytics & summary cards.
*   `GET /api/admin/businesses/pending-reviews` - Get queue of pending business listings.
*   `POST /api/admin/businesses/{id}/review` - Approve or reject a business listing.
*   `PUT /api/admin/users/{id}/block` - Block/suspend a user account.
