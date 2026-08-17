# Ticket Management System

A Laravel and Vue.js based ticket management system with role-based access control, ticket workflow, email notifications, queue processing, and external web service integration.

## Features

- User registration and authentication
- Session-based authentication with Laravel Sanctum
- Vue.js frontend
- Pinia state management
- Vue Router
- PrimeVue
- Tailwind CSS
- Role and permission management using Spatie Laravel Permission
- User ticket creation
- Required ticket attachment
- User ticket listing
- Admin ticket management
- Ticket approval and rejection workflow
- Ticket status history
- Role-based access control
- Email notification when ticket status changes
- Queue-based background processing

## Requirements

Make sure the following software is installed:

- PHP 8.3+
- Composer
- Node.js 22+
- NPM
- MySQL
- Git

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd <project-directory>
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install frontend dependencies

```bash
npm install
```

### 4. Create environment file

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

## Laravel Storage Link

Laravel stores publicly accessible uploaded files inside:

```bash
sudo docker compose exec app php artisan storage:link
```

## Environment Configuration

Configure the database connection in `.env`.

Example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_system
DB_USERNAME=root
DB_PASSWORD=
```

## Authentication

The project uses Laravel Sanctum for authentication.

The frontend communicates with Laravel through the API while authentication is maintained using Laravel's session-based authentication.

Before login or registration, the frontend requests the Sanctum CSRF cookie:

```text
GET /sanctum/csrf-cookie
```

Authentication endpoints:

```text
POST /login
POST /register
POST /logout
```

Current authenticated user:

```text
GET /api/v1/me
```

## Database Setup

Run migrations:

```bash
php artisan migrate --seed
```

The project uses Spatie Laravel Permission for roles and permissions.

Make sure permissions are seeded before assigning roles to users.

## Frontend

The frontend is implemented using Vue.js.

Main technologies:

- Vue.js
- Vue Router
- Pinia
- PrimeVue
- Tailwind CSS

The Vue application is located inside the Laravel project.

Main frontend directories:

```text
resources/
└── js/
    ├── components/
    ├── layouts/
    ├── pages/
    ├── router/
    ├── services/
    ├── stores/
    └── app.js
```

## Running the Application

Start Laravel:

```bash
php artisan serve
```

Start the frontend development server:

```bash
npm run dev
```

The application will normally be available at:

```text
http://127.0.0.1:8000
```

# Ticket Workflow

The ticket workflow is based on ticket status.

A newly created ticket starts with:

```text
pending_review
```

An administrator can approve or reject the ticket.

Example workflow:

```text
pending_review
      │
      ├── approve
      │      ↓
      │  pending_level_two
      │
      └── reject
             ↓
          rejected
```

Every status change is recorded in the ticket status history.

## Ticket Creation

Authenticated users can create tickets.

A ticket requires:

- Title
- Description
- Attachment

The attachment is mandatory.

The ticket is created through:

```text
POST /api/tickets
```

## Ticket Listing

Users can see their own tickets.

Administrators with the appropriate permission can see all tickets.

Example:

```text
GET /api/tickets
```

# Admin Panel

Admin routes are defined separately from the main application routes.

Example:

```text
/admin
/admin/tickets
```

Admin API routes are also separated from user API routes.

Example:

```text
/api/admin/tickets
```

Access to admin routes is controlled using authentication, roles, and permissions.

## Admin Login

The application provides two administrator accounts for testing the admin panel.

### Level One Admin

```text
Email: admin1@example.com
Password: password
Role: admin_level_1
```
### Level Two Admin
```text
Email: admin2@example.com
Password: password
Role: admin_level_2
```

# Roles and Permissions

The application uses:

```text
spatie/laravel-permission
```

Permissions are used to control access to application functionality.

Examples:

```text
ticket.view
ticket.view-all
ticket.create
ticket.approve
ticket.reject
```

Roles can contain multiple permissions.

The frontend also checks roles and permissions before displaying protected navigation items or allowing access to protected routes.

# Vue Router Authorization

Protected routes use route metadata.

Example:

```javascript
{
    path: '/admin',
    component: AppLayout,
    meta: {
        requiresAuth: true
    },
    children: [
        {
            path: '',
            name: 'admin.dashboard',
            component: () => import('@/pages/admin/Dashboard.vue'),
        },
    ],
}
```

The router checks authentication and authorization before navigation.

This prevents users from accessing protected pages directly through the browser.

Backend authorization is still required and is the final security layer.

# Authentication State

Authentication state is managed using Pinia.

The authentication store is responsible for:

- Login
- Registration
- Logout
- Fetching the authenticated user
- Checking authentication state
- Checking roles
- Checking permissions

Example:

```javascript
const authStore = useAuthStore();

if (authStore.isAuthenticated) {
    // User is authenticated
}
```

# Events and Listeners

The application uses Laravel Events and Listeners for side effects.

When a ticket status changes:

```text
TicketStatusChanged
        │
        ├── StoreTicketStatusHistory
        │
        ├── SendTicketStatusNotification
        │
        └── SendTicketToExternalApiListener
```

This keeps the ticket business logic independent from notifications, logging, and external services.

# Email Notifications

When a ticket is approved or rejected, the ticket owner receives an email notification.

The notification uses Laravel's Notification system.

Notification channel:

```text
mail
```

The notification is queued using:

```php
ShouldQueue
```

This means the HTTP request does not have to wait for the email to be delivered.

# Queue

The application uses Laravel Queue for background processing.

For database queues:

```env
QUEUE_CONNECTION=database
```

Start the queue worker:

```bash
php artisan queue:work
```

# Email Configuration

For development, Mailtrap can be used.

Example:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls

MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

After changing environment variables, clear the configuration cache:

```bash
php artisan optimize:clear
```

This prevents the core ticket business logic from being tightly coupled to a specific external API.

# Background Processing

External API communication is handled asynchronously using Laravel Queue.

The listener implements:

```php
ShouldQueue
```

Example flow:

```text
Admin approves ticket
        ↓
Ticket status updated
        ↓
Event dispatched
        ↓
Queue job created
        ↓
Worker processes job
        ↓
External API called
```

This prevents slow external services from blocking the admin request.


# Project Structure

The main application structure is:

```text
app/
├── Enums/
├── Events/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Jobs/
├── Listeners/
├── Models/
├── Notifications/
├── Providers/
└── Policies/

database/
├── factories/
├── migrations/
└── seeders/

resources/
└── js/
    ├── assets/
    ├── components/
    ├── layouts/
    ├── pages/
    ├── router/
    ├── services/
    ├── stores/
    ├── app.js
    └── App.vue

routes/
├── api.php
├── admin.php
├── web.php
└── console.php

```

# API Structure

The API is separated into user and admin areas.

User API:

```text
/api/...
```

Admin API:

```text
/api/admin/...
```

This separation keeps the responsibilities of user and administrator APIs clear.

# Security

The application uses multiple authorization layers.

## Backend

Backend authorization is handled using:

- Laravel authentication
- Laravel authorization
- Spatie roles
- Spatie permissions

## Frontend

The Vue application uses:

- Vue Router navigation guards
- Authentication state
- Role checks
- Permission checks

Frontend authorization is only a user experience layer.

All sensitive operations must still be authorized on the backend.


# Troubleshooting

## Authentication does not work

Make sure Sanctum CSRF initialization is called before login or registration:

```text
GET /sanctum/csrf-cookie
```

Also verify that the frontend and backend session configuration are correct.

## Queue jobs are not processed

Check:

```env
QUEUE_CONNECTION=database
```

Then run:

```bash
php artisan queue:work
```

Check failed jobs:

```bash
php artisan queue:failed
```

## Emails are not received

Verify the Mailtrap configuration in `.env` and run:

```bash
php artisan optimize:clear
```

Then make sure the queue worker is running:

```bash
php artisan queue:work
```
