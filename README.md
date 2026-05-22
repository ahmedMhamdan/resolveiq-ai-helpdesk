# ResolveIQ — AI-Powered Helpdesk Ticket System

ResolveIQ is a modern Laravel helpdesk ticket system built as a full-stack portfolio project.

It allows users to create support tickets, agents to manage assigned tickets and replies, and admins to manage the full support workflow through a clean role-based dashboard. The project also includes AI-assisted ticket handling, API authentication using Laravel Sanctum, custom authentication pages, email verification, password reset by email, notifications, responsive UI polishing, and profile management.

---

## Project Progress

Current progress: **around 95%**.

The project is now functionally close to deployment. The main completed areas are:

- Core ticket workflow
- Admin, agent, and user role workflows
- Responsive dashboard layout
- AI assistant workflow
- API authentication with Sanctum
- Email verification
- Password reset by email
- Custom ResolveIQ email templates
- Profile and avatar management
- Notifications and activity logs
- Knowledge base module
- Seeded demo data for testing

Remaining / recommended before final deployment:

- Final production `.env` setup
- Production mail provider testing
- OpenRouter production key setup
- Final deployment smoke test
- Optional feature tests
- Final README screenshots, if desired

---

## Features

### Authentication & Accounts

- User registration and login
- Laravel Fortify-based web authentication
- API authentication using Laravel Sanctum tokens
- Email verification for new accounts
- Custom email verification notification
- Password reset by email
- Custom password reset notification
- Password update support
- Profile page and profile edit
- User avatar upload
- Default avatar fallback
- Role-based access control

### Roles

The system supports three main roles:

- Admin
- Agent
- User

### Admin Features

- Manage users and roles
- Promote users to agents
- Downgrade agents back to users
- Manage agents
- Manage departments
- View all tickets
- Assign tickets to agents
- Set ticket priority
- Set due dates
- View overdue tickets
- View unassigned tickets
- Restore and permanently delete soft-deleted tickets
- Manage knowledge base articles
- Access AI assistant tools
- View notifications and activity logs

### Agent Features

- View assigned tickets only
- Reply to assigned tickets
- Add internal notes
- Close and reopen assigned tickets
- Use AI assistant for ticket replies and analysis

### User Features

- Create support tickets
- View own tickets only
- Reply to own tickets
- Update profile information
- Upload profile avatar
- Receive email verification
- Reset password by email

### Ticket System

- Ticket creation
- Ticket details page
- Ticket replies
- Internal notes for agents/admins
- Ticket status workflow
- Ticket priority workflow
- Due date support
- First response tracking
- Activity logs
- Soft delete, restore, and force delete
- Search and filtering
- Overdue tickets page
- Unassigned tickets page
- Deleted tickets page
- Responsive mobile card layout for ticket tables

### AI Features

- AI suggested replies
- AI internal notes
- AI ticket summaries
- AI priority suggestions
- AI due date suggestions
- Custom AI instructions
- Ticket-level AI actions
- OpenRouter support with mock fallback

### API Features

ResolveIQ includes a real API layer using Laravel Sanctum.

Implemented API endpoints include:

- Register
- Login
- Logout
- Current authenticated user
- Profile view/update
- Departments list
- Tickets list
- Ticket creation
- Ticket details
- Ticket replies
- Email verification
- Resend verification email

### UI / UX Features

- Dark and light mode
- Desktop dashboard layout
- Mobile responsive dashboard pages
- Mobile theme toggle
- Responsive landing page
- Custom auth pages
- Password visibility toggle
- Custom landing page footer
- Consistent buttons, cards, badges, and avatar styling
- Search feedback messages across key pages

---

## Tech Stack

- Laravel
- PHP
- MySQL
- Laravel Fortify
- Laravel Sanctum
- Blade
- HTML
- CSS
- JavaScript
- OpenRouter API
- Gmail SMTP / SMTP mail support
- Git / GitHub

---

## Database Structure

Main tables:

- `roles`
- `users`
- `departments`
- `tickets`
- `ticket_replies`
- `ticket_attachments`
- `ticket_activity_logs`
- `knowledge_base_articles`
- `notifications`
- `personal_access_tokens`
- `password_reset_tokens`
- `sessions`

### Main Relationships

- A role has many users
- A user belongs to a role
- A user can create many tickets
- An agent can be assigned to many tickets
- A department has many tickets
- A ticket belongs to a user
- A ticket may belong to an agent
- A ticket belongs to a department
- A ticket has many replies
- A ticket has many attachments
- A ticket has many activity logs
- A user has many API tokens through Sanctum

---

## Ticket Workflow

Tickets support the following statuses:

- Open
- Pending
- Solved
- Closed

Tickets support the following priorities:

- Low
- Medium
- High
- Urgent
- Not set

---

## Installation

Clone the repository:

```bash
git clone https://github.com/ahmedMhamdan/resolveiq-ai-helpdesk.git
cd resolveiq-ai-helpdesk
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Copy the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=resolveiq
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seeders:

```bash
php artisan migrate --seed
```

Create the storage link:

```bash
php artisan storage:link
```

Run the development server:

```bash
php artisan serve
```

Run Vite:

```bash
npm run dev
```

---

## Environment Variables

The project uses these important environment variables:

```env
APP_NAME="ResolveIQ"
APP_URL=http://127.0.0.1:8000

FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="ResolveIQ"

AI_PROVIDER=mock
OPENROUTER_API_KEY=
OPENROUTER_MODEL=openrouter/free

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000
```

Important: never commit your real `.env` file, mail password, API keys, or any secret credentials to GitHub.

---

## Email Verification & Password Reset

ResolveIQ supports:

- Email verification for new accounts
- Password reset links by email
- Custom ResolveIQ notification content
- Custom Laravel mail theme and header

For local testing with Gmail SMTP, use a **Gmail App Password**, not your normal Gmail password.

Example:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_16_character_app_password_without_spaces
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="ResolveIQ"
```

After changing mail settings, clear config cache:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan view:clear
```

For local testing without sending real emails, use:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=support@resolveiq.test
MAIL_FROM_NAME="ResolveIQ"
```

Then check:

```txt
storage/logs/laravel.log
```

---

## API Authentication

ResolveIQ uses Laravel Sanctum for API authentication.

API clients must send the token using:

```txt
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## API Endpoints

Base URL:

```txt
http://127.0.0.1:8000/api/v1
```

### Auth

```txt
POST /register
POST /login
POST /logout
GET  /me
```

### Email Verification

```txt
GET  /email/verify/{id}/{hash}
POST /email/verification-notification
```

### Profile

```txt
GET  /profile
POST /profile
```

### Departments

```txt
GET /departments
```

### Tickets

```txt
GET  /tickets
POST /tickets
GET  /tickets/{ticket}
```

### Ticket Replies

```txt
GET  /tickets/{ticket}/replies
POST /tickets/{ticket}/replies
```

---

## Example API Register Request

```http
POST /api/v1/register
Accept: application/json
Content-Type: application/json
```

```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "device_name": "Postman"
}
```

---

## Example API Login Request

```http
POST /api/v1/login
Accept: application/json
Content-Type: application/json
```

```json
{
  "email": "test@example.com",
  "password": "password123",
  "device_name": "Postman"
}
```

---

## Example Create Ticket Request

```http
POST /api/v1/tickets
Accept: application/json
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN_HERE
```

```json
{
  "title": "Cannot access my dashboard",
  "description": "The dashboard keeps loading after login.",
  "department_id": 1
}
```

---

## Test Accounts

After running seeders, you can use these demo accounts:

```txt
Admin:
admin@resolveiq.test
password

Agent:
agent@resolveiq.test
password

Second Agent:
agent2@resolveiq.test
password

User:
user@resolveiq.test
password

Customer:
omar@resolveiq.test
password
```

---

## Deployment Notes

Before deployment, make sure to:

- Set production environment variables
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set the correct production `APP_URL`
- Configure production database credentials
- Configure SMTP mail settings
- Configure OpenRouter API settings if AI is enabled
- Run migrations on the production database
- Run `php artisan storage:link`
- Cache config, routes, and views
- Never upload `.env` to GitHub

Recommended production commands:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Responsive Design

The dashboard layout supports desktop and smaller screens.

Recently polished responsive areas:

- Tickets table and mobile cards
- Deleted tickets page
- Overdue tickets page
- Unassigned tickets page
- Users management page
- Agents management page
- Departments page
- Knowledge base pages
- Dashboard search sections
- Auth pages
- Landing page
- Profile and verification pages

---

## Current Development Status

Completed:

- Core helpdesk system
- Role-based access control
- Admin, agent, and user workflows
- Ticket CRUD and ticket workflow actions
- Soft delete, restore, and force delete
- Overdue, unassigned, and deleted ticket management
- Knowledge base CRUD
- AI assistant workflow
- API authentication
- API tickets and replies
- API profile update
- Email verification
- Password reset by email
- Custom ResolveIQ email notifications
- Custom mail header and theme
- User and agent avatar handling
- Notifications and activity logs
- Responsive UI polishing across major pages
- Landing page footer and auth page navbar polishing

Next recommended steps:

- Test the full production mail flow after deployment
- Configure production `APP_URL`
- Configure production database
- Configure production AI provider
- Add screenshots to README
- Add automated feature tests
- Final deployment smoke test

---

## Author

Developed by Ahmed Mhamdan as a Laravel + Cybersecurity portfolio project.
