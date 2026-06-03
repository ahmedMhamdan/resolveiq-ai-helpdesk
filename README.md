# ResolveIQ — AI Helpdesk Ticket System

ResolveIQ is a Laravel-based helpdesk ticket system built as a portfolio project. It simulates a real support workflow with users, agents, admins, tickets, notifications, email verification, and AI-assisted ticket handling.

## Project Status

ResolveIQ is feature-complete as a Laravel + cybersecurity portfolio project. The current version focuses on a practical helpdesk workflow, AI-assisted ticket operations, bilingual UI support, responsive dashboard polish, and deployment readiness.

## Live Demo

https://resolveiq-ai-helpdesk.onrender.com

> The project is hosted on a free Render instance, so the first request may take a few seconds after inactivity.

## Main Features

- User registration, login, logout, remember-me, and password reset
- Email verification workflow
- Role-based access control: Admin, Agent, User
- Admin dashboard for managing tickets, users, agents, departments, and knowledge base
- Agent dashboard for managing assigned tickets
- User dashboard for creating and tracking personal tickets
- Ticket replies and internal notes
- Ticket priorities, due dates, status updates, close/reopen workflow
- Unassigned tickets page
- Overdue tickets page
- Deleted tickets with restore and force delete
- Notifications and activity logs
- AI assistant for suggested replies, summaries, internal notes, priority suggestions, and due date suggestions
- Arabic/English UI support with RTL layout handling
- Responsive UI with dark/light mode, mobile-friendly action bars, and touch-sized controls
- Accessibility improvements for keyboard navigation, focus states, and icon-only action labels
- API authentication using Laravel Sanctum
- Docker deployment on Render
- PostgreSQL database on Neon

## Demo Accounts

```txt
Admin
Email: admin@resolveiq.test
Password: password

Agent
Email: agent@resolveiq.test
Password: password

Second Agent
Email: agent2@resolveiq.test
Password: password

User
Email: user@resolveiq.test
Password: password

Customer
Email: omar@resolveiq.test
Password: password
```

## Email Verification Note

ResolveIQ supports email verification and password reset.

For the deployed demo, emails are tested through a sandbox mail service controlled by the project owner. If you register a new account and cannot access ticket pages, the account may need to be verified by the owner.

For quick testing, use one of the demo accounts above.

## Tech Stack

- Laravel
- PHP 8.3
- Laravel Fortify
- Laravel Sanctum
- Blade
- HTML
- CSS
- JavaScript
- MySQL for local development
- PostgreSQL / Neon for deployment
- Render
- Docker
- Mailtrap Sandbox
- OpenRouter / mock AI fallback

## Local Installation

```bash
git clone https://github.com/ahmedMhamdan/resolveiq-ai-helpdesk.git
cd resolveiq-ai-helpdesk

composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then run:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
npm run dev
```

## Important Environment Variables

Example local configuration:

```env
APP_NAME=ResolveIQ
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=resolveiq
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@resolveiq.test
MAIL_FROM_NAME=ResolveIQ

AI_PROVIDER=mock
OPENROUTER_API_KEY=
OPENROUTER_MODEL=openrouter/free
```

Never commit real `.env` files, database credentials, mail passwords, or API keys.

## Deployment Notes

The project is Docker-ready and deployed on Render.

For production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://resolveiq-ai-helpdesk.onrender.com
APP_FRESH_MIGRATE=false
APP_SEED_DATABASE=false
```

`APP_FRESH_MIGRATE=true` and `APP_SEED_DATABASE=true` should only be used during the first clean deployment setup, then turned off.

## API

ResolveIQ includes API authentication with Laravel Sanctum.

Base deployed API URL:

```txt
https://resolveiq-ai-helpdesk.onrender.com/api/v1
```

Use:

```txt
Authorization: Bearer YOUR_TOKEN_HERE
```

Main API areas:

- Register
- Login
- Logout
- Current user
- Profile
- Departments
- Tickets
- Ticket replies
- Email verification

## Screenshots

Screenshots will be added later.

## Portfolio Notes

ResolveIQ was built to demonstrate practical Laravel backend development with real product concerns:

- Authentication, authorization, verified accounts, and role-specific dashboards
- Ticket lifecycle management with replies, notes, attachments, activity history, and notifications
- Admin workflows for users, agents, departments, deleted tickets, overdue tickets, and unassigned work
- AI assistance layered into the support workflow without replacing the agent's final decision
- Production deployment using Docker, Render, and Neon PostgreSQL
- Bilingual interface polish for English and Arabic users

## Author

Developed by **Ahmed Mhamdan** as a Laravel + Cybersecurity portfolio project.
