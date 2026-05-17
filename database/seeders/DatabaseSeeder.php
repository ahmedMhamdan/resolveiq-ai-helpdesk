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
                'avatar_path' => 'images/avatars/admin-ahmed.svg',
            ]
        );

        $agentOne = User::updateOrCreate(
            ['email' => 'agent@resolveiq.test'],
            [
                'name' => 'Support Agent',
                'password' => Hash::make('password'),
                'role_id' => $agentRole->id,
                'avatar_path' => 'images/avatars/support-agent.svg',
            ]
        );

        $agentTwo = User::updateOrCreate(
            ['email' => 'agent2@resolveiq.test'],
            [
                'name' => 'Second Agent',
                'password' => Hash::make('password'),
                'role_id' => $agentRole->id,
                'avatar_path' => 'images/avatars/second-agent.svg',
            ]
        );

        $customerOne = User::updateOrCreate(
            ['email' => 'user@resolveiq.test'],
            [
                'name' => 'Sarah Johnson',
                'password' => Hash::make('password'),
                'role_id' => $userRole->id,
                'avatar_path' => 'images/avatars/user-sarah.svg',
            ]
        );

        $customerTwo = User::updateOrCreate(
            ['email' => 'omar@resolveiq.test'],
            [
                'name' => 'Omar Customer',
                'password' => Hash::make('password'),
                'role_id' => $userRole->id,
                'avatar_path' => 'images/avatars/user-omar.svg',
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

        $ticketTemplates = [
            [
                'number' => 'RIQ-1001',
                'title' => 'Unable to login to account',
                'description' => 'I get a 500 error whenever I try to login.',
                'customer' => $customerOne,
                'agent' => $agentOne,
                'department' => $support,
                'status' => 'open',
                'priority' => 'high',
                'due_at' => now()->subDays(3),
            ],
            [
                'number' => 'RIQ-1002',
                'title' => 'Invoice amount is incorrect',
                'description' => 'The latest invoice shows a wrong amount.',
                'customer' => $customerTwo,
                'agent' => $agentTwo,
                'department' => $billing,
                'status' => 'pending',
                'priority' => 'medium',
                'due_at' => now()->subDays(2),
            ],
            [
                'number' => 'RIQ-1003',
                'title' => 'Need help resetting 2FA',
                'description' => 'I lost access to my authenticator app.',
                'customer' => $customerOne,
                'agent' => null,
                'department' => $security,
                'status' => 'open',
                'priority' => null,
                'due_at' => null,
            ],
            [
                'number' => 'RIQ-1004',
                'title' => 'Email notifications are delayed',
                'description' => 'Email notifications are arriving late for my account.',
                'customer' => $customerTwo,
                'agent' => $agentOne,
                'department' => $support,
                'status' => 'pending',
                'priority' => 'low',
                'due_at' => now()->addDays(2),
            ],
            [
                'number' => 'RIQ-1005',
                'title' => 'Cannot download invoice PDF',
                'description' => 'The invoice PDF download button is not working.',
                'customer' => $customerOne,
                'agent' => null,
                'department' => $billing,
                'status' => 'open',
                'priority' => null,
                'due_at' => null,
            ],
            [
                'number' => 'RIQ-1006',
                'title' => 'Account security review request',
                'description' => 'I want to review recent login activity on my account.',
                'customer' => $customerTwo,
                'agent' => $agentTwo,
                'department' => $security,
                'status' => 'open',
                'priority' => 'urgent',
                'due_at' => now()->subDay(),
            ],
            [
                'number' => 'RIQ-1007',
                'title' => 'Dashboard loads slowly',
                'description' => 'The dashboard takes too long to load after login.',
                'customer' => $customerOne,
                'agent' => $agentOne,
                'department' => $support,
                'status' => 'solved',
                'priority' => 'medium',
                'due_at' => now()->subDays(4),
            ],
            [
                'number' => 'RIQ-1008',
                'title' => 'Password reset email not received',
                'description' => 'I requested a password reset but no email arrived.',
                'customer' => $customerTwo,
                'agent' => null,
                'department' => $support,
                'status' => 'open',
                'priority' => null,
                'due_at' => null,
            ],
            [
                'number' => 'RIQ-1009',
                'title' => 'Billing page shows blank screen',
                'description' => 'The billing page opens but does not show invoice details.',
                'customer' => $customerOne,
                'agent' => $agentTwo,
                'department' => $billing,
                'status' => 'open',
                'priority' => 'high',
                'due_at' => now()->addDays(1),
            ],
            [
                'number' => 'RIQ-1010',
                'title' => 'Suspicious login notification',
                'description' => 'I received a suspicious login notification and need help.',
                'customer' => $customerTwo,
                'agent' => $agentOne,
                'department' => $security,
                'status' => 'pending',
                'priority' => 'urgent',
                'due_at' => now()->subDays(5),
            ],
            [
                'number' => 'RIQ-1011',
                'title' => 'Cannot update profile details',
                'description' => 'The profile update form does not save my changes.',
                'customer' => $customerOne,
                'agent' => null,
                'department' => $support,
                'status' => 'open',
                'priority' => null,
                'due_at' => null,
            ],
            [
                'number' => 'RIQ-1012',
                'title' => 'Attachment upload failed',
                'description' => 'I tried uploading a file but the upload failed.',
                'customer' => $customerTwo,
                'agent' => $agentTwo,
                'department' => $support,
                'status' => 'closed',
                'priority' => 'medium',
                'due_at' => now()->subDays(7),
            ],
            [
                'number' => 'RIQ-1013',
                'title' => 'Wrong department selected',
                'description' => 'The ticket was submitted to the wrong department.',
                'customer' => $customerOne,
                'agent' => null,
                'department' => $billing,
                'status' => 'open',
                'priority' => null,
                'due_at' => null,
            ],
            [
                'number' => 'RIQ-1014',
                'title' => 'Subscription renewal issue',
                'description' => 'The subscription renewal did not update my account.',
                'customer' => $customerTwo,
                'agent' => $agentOne,
                'department' => $billing,
                'status' => 'pending',
                'priority' => 'high',
                'due_at' => now()->subDays(6),
            ],
            [
                'number' => 'RIQ-1015',
                'title' => 'Need access review',
                'description' => 'Please review who has access to my account.',
                'customer' => $customerOne,
                'agent' => null,
                'department' => $security,
                'status' => 'open',
                'priority' => null,
                'due_at' => null,
            ],
            [
                'number' => 'RIQ-1016',
                'title' => 'System error on submit',
                'description' => 'The form shows an error after clicking submit.',
                'customer' => $customerTwo,
                'agent' => $agentTwo,
                'department' => $support,
                'status' => 'open',
                'priority' => 'medium',
                'due_at' => now()->addDays(3),
            ],
            [
                'number' => 'RIQ-1017',
                'title' => 'Missing ticket reply notification',
                'description' => 'I did not receive a notification for the latest reply.',
                'customer' => $customerOne,
                'agent' => $agentOne,
                'department' => $support,
                'status' => 'pending',
                'priority' => 'low',
                'due_at' => now()->addDays(4),
            ],
            [
                'number' => 'RIQ-1018',
                'title' => 'Unable to close ticket',
                'description' => 'The ticket status does not update when I try to close it.',
                'customer' => $customerTwo,
                'agent' => null,
                'department' => $support,
                'status' => 'open',
                'priority' => null,
                'due_at' => null,
            ],
            [
                'number' => 'RIQ-1019',
                'title' => 'Payment method not accepted',
                'description' => 'My payment method is rejected on checkout.',
                'customer' => $customerOne,
                'agent' => $agentTwo,
                'department' => $billing,
                'status' => 'open',
                'priority' => 'urgent',
                'due_at' => now()->subDays(8),
            ],
            [
                'number' => 'RIQ-1020',
                'title' => 'Security settings not saving',
                'description' => 'Security settings return to the old values after saving.',
                'customer' => $customerTwo,
                'agent' => null,
                'department' => $security,
                'status' => 'open',
                'priority' => null,
                'due_at' => null,
            ],
            [
                'number' => 'RIQ-1021',
                'title' => 'Agent response is unclear',
                'description' => 'I need a clearer explanation about the previous response.',
                'customer' => $customerOne,
                'agent' => $agentOne,
                'department' => $support,
                'status' => 'pending',
                'priority' => 'medium',
                'due_at' => now()->addDays(5),
            ],
            [
                'number' => 'RIQ-1022',
                'title' => 'Refund request follow up',
                'description' => 'I submitted a refund request and need an update.',
                'customer' => $customerTwo,
                'agent' => $agentTwo,
                'department' => $billing,
                'status' => 'open',
                'priority' => 'high',
                'due_at' => now()->addDays(2),
            ],
            [
                'number' => 'RIQ-1023',
                'title' => 'Account locked after login attempts',
                'description' => 'My account was locked after several login attempts.',
                'customer' => $customerOne,
                'agent' => null,
                'department' => $security,
                'status' => 'open',
                'priority' => null,
                'due_at' => null,
            ],
            [
                'number' => 'RIQ-1024',
                'title' => 'Report export is not working',
                'description' => 'The exported report file is empty.',
                'customer' => $customerTwo,
                'agent' => $agentOne,
                'department' => $support,
                'status' => 'open',
                'priority' => 'low',
                'due_at' => now()->addDays(6),
            ],
        ];

        $createdTickets = [];

        foreach ($ticketTemplates as $ticketData) {
            $status = $ticketData['status'];

            $createdTickets[$ticketData['number']] = Ticket::updateOrCreate(
                ['ticket_number' => $ticketData['number']],
                [
                    'user_id' => $ticketData['customer']->id,
                    'agent_id' => $ticketData['agent']?->id,
                    'department_id' => $ticketData['department']->id,
                    'title' => $ticketData['title'],
                    'description' => $ticketData['description'],
                    'status' => $status,
                    'priority' => $ticketData['priority'],
                    'due_at' => $ticketData['due_at'],
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
            ['RIQ-1008', $customerTwo, 'I checked spam and still did not receive anything.', false],
            ['RIQ-1010', $agentOne, 'Please confirm if you recognize the login location.', false],
            ['RIQ-1014', $agentOne, 'Billing team is reviewing the renewal record.', false],
            ['RIQ-1019', $agentTwo, 'We escalated this payment issue for urgent review.', true],
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
                        'action' => 'Agent assigned',
                    ],
                    [
                        'user_id' => $admin->id,
                        'old_value' => 'Unassigned',
                        'new_value' => $assignedAgentName ?? 'Unassigned',
                    ]
                );
            }

            if ($ticket->priority) {
                TicketActivityLog::updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'action' => 'Priority changed',
                    ],
                    [
                        'user_id' => $admin->id,
                        'old_value' => 'Not set',
                        'new_value' => $ticket->priority,
                    ]
                );
            }
        }
    }
}
