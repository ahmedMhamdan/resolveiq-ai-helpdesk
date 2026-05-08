# ResolveIQ — AI-Powered Helpdesk Ticket System

ResolveIQ is a modern Laravel helpdesk ticket system built as a full-stack portfolio project.  
It helps users submit support tickets, agents manage replies and priorities, and admins monitor support activity through a clean dashboard interface.

The project is currently in its early development phase, with the core database structure, model relationships, seed data, dashboard page, tickets list, and ticket details page already implemented.

---

## Features Implemented

- Laravel project setup
- Clean database design for a helpdesk system
- Roles structure: Admin, Agent, User
- Departments
- Tickets with status and priority
- Ticket replies
- Ticket attachments database structure
- Ticket activity logs
- Dashboard statistics
- Tickets list page
- Ticket details / conversation page
- Search and filtering for tickets
- Seed data for testing
- Responsive dashboard layout
- Light modern SaaS UI foundation

---

## Planned Features

- Authentication using Laravel Breeze
- Role-based access control
- Create / edit / delete tickets
- Ticket reply form
- Internal notes for agents
- File upload for ticket attachments
- Admin management for departments and users
- Soft delete and archive system
- Dark mode dashboard
- AI ticket conversation summary
- AI suggested replies
- Realtime ticket updates
- Deployment

---

## Tech Stack

- Laravel
- PHP
- MySQL
- Blade
- HTML
- CSS
- Git / GitHub

---

## Database Structure

The main database tables are:

- `roles`
- `users`
- `departments`
- `tickets`
- `ticket_replies`
- `ticket_attachments`
- `ticket_activity_logs`

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

---

## Installation

Clone the repository:

```bash
git clone https://github.com/ahmedMhamdan/resolveiq-ai-helpdesk
