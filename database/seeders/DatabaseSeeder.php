<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::updateOrCreate(['name' => 'admin']);
        $agentRole = Role::updateOrCreate(['name' => 'agent']);
        $userRole = Role::updateOrCreate(['name' => 'user']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@resolveiq.test'],
            [
                'name' => 'Ahmed Admin',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
            ]
        );

        $agentOne = User::updateOrCreate(
            ['email' => 'agent@resolveiq.test'],
            [
                'name' => 'Support Agent',
                'password' => Hash::make('password'),
                'role_id' => $agentRole->id,
            ]
        );

        $agentTwo = User::updateOrCreate(
            ['email' => 'agent2@resolveiq.test'],
            [
                'name' => 'Second Agent',
                'password' => Hash::make('password'),
                'role_id' => $agentRole->id,
            ]
        );

        $customerOne = User::updateOrCreate(
            ['email' => 'user@resolveiq.test'],
            [
                'name' => 'Sarah Johnson',
                'password' => Hash::make('password'),
                'role_id' => $userRole->id,
            ]
        );

        $customerTwo = User::updateOrCreate(
            ['email' => 'omar@resolveiq.test'],
            [
                'name' => 'Omar Customer',
                'password' => Hash::make('password'),
                'role_id' => $userRole->id,
            ]
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

        $tickets = [
            [
                'ticket_number' => 'RIQ-1001',
                'user_id' => $customerOne->id,
                'agent_id' => $agentOne->id,
                'department_id' => $support->id,
                'title' => 'Unable to login to account',
                'description' => 'I get a 500 error whenever I try to login.',
                'status' => 'open',
                'priority' => 'urgent',
                'due_at' => now()->addHours(8),
            ],
            [
                'ticket_number' => 'RIQ-1002',
                'user_id' => $customerOne->id,
                'agent_id' => $agentOne->id,
                'department_id' => $billing->id,
                'title' => 'Invoice amount is incorrect',
                'description' => 'The latest invoice shows a wrong amount.',
                'status' => 'pending',
                'priority' => 'medium',
                'due_at' => now()->addDay(),
            ],
            [
                'ticket_number' => 'RIQ-1003',
                'user_id' => $customerOne->id,
                'agent_id' => null,
                'department_id' => $security->id,
                'title' => 'Need help resetting 2FA',
                'description' => 'I lost access to my authenticator app.',
                'status' => 'open',
                'priority' => 'high',
                'due_at' => now()->addHours(12),
            ],
            [
                'ticket_number' => 'RIQ-1004',
                'user_id' => $customerTwo->id,
                'agent_id' => $agentTwo->id,
                'department_id' => $support->id,
                'title' => 'Email notifications are delayed',
                'description' => 'Email notifications are arriving late for my account.',
                'status' => 'open',
                'priority' => 'low',
                'due_at' => now()->addDays(2),
            ],
            [
                'ticket_number' => 'RIQ-1005',
                'user_id' => $customerTwo->id,
                'agent_id' => $agentTwo->id,
                'department_id' => $billing->id,
                'title' => 'Cannot download invoice PDF',
                'description' => 'The invoice PDF download button is not working.',
                'status' => 'pending',
                'priority' => 'medium',
                'due_at' => now()->addDays(3),
            ],
            [
                'ticket_number' => 'RIQ-1006',
                'user_id' => $customerTwo->id,
                'agent_id' => $agentOne->id,
                'department_id' => $security->id,
                'title' => 'Account security review request',
                'description' => 'I want to review recent login activity on my account.',
                'status' => 'solved',
                'priority' => 'high',
                'due_at' => now()->addDays(4),
            ],
        ];

        $createdTickets = [];

        foreach ($tickets as $ticketData) {
            $createdTickets[$ticketData['ticket_number']] = Ticket::updateOrCreate(
                ['ticket_number' => $ticketData['ticket_number']],
                $ticketData
            );
        }

        TicketReply::updateOrCreate(
            [
                'ticket_id' => $createdTickets['RIQ-1001']->id,
                'user_id' => $customerOne->id,
                'message' => 'I tried clearing cache but the issue still happens.',
            ],
            ['is_internal_note' => false]
        );

        TicketReply::updateOrCreate(
            [
                'ticket_id' => $createdTickets['RIQ-1001']->id,
                'user_id' => $agentOne->id,
                'message' => 'Thanks for the details. We are checking the login service now.',
            ],
            ['is_internal_note' => false]
        );

        TicketReply::updateOrCreate(
            [
                'ticket_id' => $createdTickets['RIQ-1002']->id,
                'user_id' => $agentOne->id,
                'message' => 'Internal review started for the billing record.',
            ],
            ['is_internal_note' => true]
        );

        TicketReply::updateOrCreate(
            [
                'ticket_id' => $createdTickets['RIQ-1004']->id,
                'user_id' => $agentTwo->id,
                'message' => 'We are checking the mail queue and notification service.',
            ],
            ['is_internal_note' => false]
        );

        TicketReply::updateOrCreate(
            [
                'ticket_id' => $createdTickets['RIQ-1005']->id,
                'user_id' => $customerTwo->id,
                'message' => 'The issue happens on Chrome and Edge.',
            ],
            ['is_internal_note' => false]
        );

        foreach ($createdTickets as $ticket) {
            TicketActivityLog::updateOrCreate(
                [
                    'ticket_id' => $ticket->id,
                    'action' => 'Ticket created',
                ],
                [
                    'user_id' => $ticket->user_id,
                ]
            );

            if ($ticket->agent_id) {
            $assignedAgentName = User::query()
                ->whereKey($ticket->agent_id)
                ->value('name');

            TicketActivityLog::updateOrCreate(
                [
                    'ticket_id' => $ticket->id,
                    'action' => 'Assigned to agent',
                ],
                [
                    'user_id' => $admin->id,
                    'new_value' => $assignedAgentName ?? 'Unassigned',
                ]
            );
        }
        }
    }
}
