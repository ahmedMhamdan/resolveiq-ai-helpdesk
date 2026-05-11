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

        $departments = [$support, $billing, $security];
        $customers = [$customerOne, $customerTwo];
        $agents = [$agentOne, $agentTwo, null];
        $statuses = ['open', 'pending', 'solved', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent', null];

        $ticketTemplates = [
            ['Unable to login to account', 'I get a 500 error whenever I try to login.'],
            ['Invoice amount is incorrect', 'The latest invoice shows a wrong amount.'],
            ['Need help resetting 2FA', 'I lost access to my authenticator app.'],
            ['Email notifications are delayed', 'Email notifications are arriving late for my account.'],
            ['Cannot download invoice PDF', 'The invoice PDF download button is not working.'],
            ['Account security review request', 'I want to review recent login activity on my account.'],
            ['Dashboard loads slowly', 'The dashboard takes too long to load after login.'],
            ['Password reset email not received', 'I requested a password reset but no email arrived.'],
            ['Billing page shows blank screen', 'The billing page opens but does not show invoice details.'],
            ['Suspicious login notification', 'I received a suspicious login notification and need help.'],
            ['Cannot update profile details', 'The profile update form does not save my changes.'],
            ['Attachment upload failed', 'I tried uploading a file but the upload failed.'],
            ['Wrong department selected', 'The ticket was submitted to the wrong department.'],
            ['Subscription renewal issue', 'The subscription renewal did not update my account.'],
            ['Need access review', 'Please review who has access to my account.'],
            ['System error on submit', 'The form shows an error after clicking submit.'],
            ['Missing ticket reply notification', 'I did not receive a notification for the latest reply.'],
            ['Unable to close ticket', 'The ticket status does not update when I try to close it.'],
            ['Payment method not accepted', 'My payment method is rejected on checkout.'],
            ['Security settings not saving', 'Security settings return to the old values after saving.'],
        ];

        $createdTickets = [];

        foreach ($ticketTemplates as $index => [$title, $description]) {
            $number = 1001 + $index;
            $agent = $agents[$index % count($agents)];
            $status = $statuses[$index % count($statuses)];
            $priority = $agent ? $priorities[$index % count($priorities)] : null;

            if ($status === 'closed' && $priority === null) {
                $priority = 'medium';
            }

            $createdTickets['RIQ-' . $number] = Ticket::updateOrCreate(
                ['ticket_number' => 'RIQ-' . $number],
                [
                    'user_id' => $customers[$index % count($customers)]->id,
                    'agent_id' => $agent?->id,
                    'department_id' => $departments[$index % count($departments)]->id,
                    'title' => $title,
                    'description' => $description,
                    'status' => $status,
                    'priority' => $priority,
                    'due_at' => $agent ? now()->addDays(($index % 7) + 1) : null,
                    'resolved_at' => $status === 'solved' ? now()->subDays(1) : null,
                    'closed_at' => $status === 'closed' ? now()->subHours(8) : null,
                ]
            );
        }

        $replyData = [
            ['RIQ-1001', $customerOne, 'I tried clearing cache but the issue still happens.', false],
            ['RIQ-1001', $agentOne, 'Thanks for the details. We are checking the login service now.', false],
            ['RIQ-1002', $agentTwo, 'Internal review started for the billing record.', true],
            ['RIQ-1004', $agentOne, 'We are checking the mail queue and notification service.', false],
            ['RIQ-1005', $customerOne, 'The issue happens on Chrome and Edge.', false],
            ['RIQ-1007', $agentOne, 'I will monitor the dashboard performance metrics.', true],
            ['RIQ-1008', $customerTwo, 'I checked spam and still did not receive anything.', false],
            ['RIQ-1010', $agentOne, 'Please confirm if you recognize the login location.', false],
        ];

        foreach ($replyData as [$ticketNumber, $replyUser, $message, $isInternalNote]) {
            TicketReply::updateOrCreate(
                [
                    'ticket_id' => $createdTickets[$ticketNumber]->id,
                    'user_id' => $replyUser->id,
                    'message' => $message,
                ],
                [
                    'is_internal_note' => $isInternalNote,
                ]
            );
        }

        foreach ($createdTickets as $ticket) {
            TicketActivityLog::updateOrCreate(
                [
                    'ticket_id' => $ticket->id,
                    'action' => 'Ticket created',
                ],
                [
                    'user_id' => $ticket->user_id,
                    'old_value' => null,
                    'new_value' => 'open',
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
                        'old_value' => null,
                        'new_value' => $assignedAgentName ?? 'Unassigned',
                    ]
                );
            }

            if ($ticket->priority) {
                TicketActivityLog::updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'action' => 'Priority set',
                    ],
                    [
                        'user_id' => $admin->id,
                        'old_value' => null,
                        'new_value' => $ticket->priority,
                    ]
                );
            }
        }
    }
}
