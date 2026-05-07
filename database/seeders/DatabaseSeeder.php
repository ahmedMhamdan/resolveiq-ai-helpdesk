<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::updateOrCreate(['name' => 'admin']);
        $agentRole = Role::updateOrCreate(['name' => 'agent']);
        $userRole = Role::updateOrCreate(['name' => 'user']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@resolveiq.test'],
            ['name' => 'Ahmed Admin', 'password' => 'password', 'role_id' => $adminRole->id]
        );

        $agent = User::updateOrCreate(
            ['email' => 'agent@resolveiq.test'],
            ['name' => 'Support Agent', 'password' => 'password', 'role_id' => $agentRole->id]
        );

        $user = User::updateOrCreate(
            ['email' => 'user@resolveiq.test'],
            ['name' => 'Sarah Johnson', 'password' => 'password', 'role_id' => $userRole->id]
        );

        $support = Department::updateOrCreate(
            ['name' => 'Technical Support'],
            ['description' => 'Handles technical issues and system errors.']
        );

        $billing = Department::updateOrCreate(
            ['name' => 'Billing'],
            ['description' => 'Handles payments, invoices, and subscriptions.']
        );

        $security = Department::updateOrCreate(
            ['name' => 'Security'],
            ['description' => 'Handles account security and access issues.']
        );

        $ticketOne = Ticket::updateOrCreate(
            ['ticket_number' => 'RIQ-1001'],
            [
                'user_id' => $user->id,
                'agent_id' => $agent->id,
                'department_id' => $support->id,
                'title' => 'Unable to login to account',
                'description' => 'I get a 500 error whenever I try to login.',
                'status' => 'open',
                'priority' => 'urgent',
                'due_at' => now()->addHours(8),
            ]
        );

        $ticketTwo = Ticket::updateOrCreate(
            ['ticket_number' => 'RIQ-1002'],
            [
                'user_id' => $user->id,
                'agent_id' => $agent->id,
                'department_id' => $billing->id,
                'title' => 'Invoice amount is incorrect',
                'description' => 'The latest invoice shows a wrong amount.',
                'status' => 'pending',
                'priority' => 'medium',
                'due_at' => now()->addDay(),
            ]
        );

        $ticketThree = Ticket::updateOrCreate(
            ['ticket_number' => 'RIQ-1003'],
            [
                'user_id' => $user->id,
                'agent_id' => null,
                'department_id' => $security->id,
                'title' => 'Need help resetting 2FA',
                'description' => 'I lost access to my authenticator app.',
                'status' => 'open',
                'priority' => 'high',
                'due_at' => now()->addHours(12),
            ]
        );

        TicketReply::updateOrCreate(
            ['ticket_id' => $ticketOne->id, 'user_id' => $user->id, 'message' => 'I tried clearing cache but the issue still happens.'],
            ['is_internal_note' => false]
        );

        TicketReply::updateOrCreate(
            ['ticket_id' => $ticketOne->id, 'user_id' => $agent->id, 'message' => 'Thanks for the details. We are checking the login service now.'],
            ['is_internal_note' => false]
        );

        TicketActivityLog::updateOrCreate(
            ['ticket_id' => $ticketOne->id, 'action' => 'Ticket created'],
            ['user_id' => $user->id]
        );

        TicketActivityLog::updateOrCreate(
            ['ticket_id' => $ticketOne->id, 'action' => 'Assigned to agent'],
            ['user_id' => $admin->id, 'new_value' => $agent->name]
        );
    }
}
