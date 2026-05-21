<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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
                'email_verified_at' => now(),
                'avatar_path' => 'images/avatars/admin-ahmed.svg',
            ]
        );

        $agentOne = User::updateOrCreate(
            ['email' => 'agent@resolveiq.test'],
            [
                'name' => 'Support Agent',
                'password' => Hash::make('password'),
                'role_id' => $agentRole->id,
                'email_verified_at' => now(),
                'avatar_path' => 'images/avatars/support-agent.svg',
            ]
        );

        $agentTwo = User::updateOrCreate(
            ['email' => 'agent2@resolveiq.test'],
            [
                'name' => 'Second Agent',
                'password' => Hash::make('password'),
                'role_id' => $agentRole->id,
                'email_verified_at' => now(),
                'avatar_path' => 'images/avatars/second-agent.svg',
            ]
        );

        $customerOne = User::updateOrCreate(
            ['email' => 'user@resolveiq.test'],
            [
                'name' => 'Sarah Johnson',
                'password' => Hash::make('password'),
                'role_id' => $userRole->id,
                'email_verified_at' => now(),
                'avatar_path' => 'images/avatars/user-sarah.svg',
            ]
        );

        $customerTwo = User::updateOrCreate(
            ['email' => 'omar@resolveiq.test'],
            [
                'name' => 'Omar Customer',
                'password' => Hash::make('password'),
                'role_id' => $userRole->id,
                'email_verified_at' => now(),
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
                'is_deleted' => true,
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
                'is_deleted' => true,
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

            $ticket = Ticket::withTrashed()->updateOrCreate(
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

            if (method_exists($ticket, 'restore') && $ticket->trashed()) {
                $ticket->restore();
            }

            $createdTickets[$ticketData['number']] = $ticket;
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

        $knowledgeArticles = [
            [
                'title' => 'How to reset a customer password safely',
                'content' => "Use this article when a customer cannot access their account.\n\n1. Confirm the customer's email address.\n2. Check whether the account is locked or inactive.\n3. Send the password reset link from the support panel.\n4. Ask the customer to check inbox and spam folders.\n5. Never ask the customer to share their password or reset token.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 8,
            ],
            [
                'title' => 'Troubleshooting login errors',
                'content' => "Use this article for login issues, 500 errors, or repeated failed attempts.\n\nStart by checking the user's email, recent login attempts, browser cache, and active sessions. If the problem affects multiple users, escalate it as a possible authentication service issue.",
                'status' => 'published',
                'user' => $agentOne,
                'days_ago' => 7,
            ],
            [
                'title' => 'Handling invoice amount disputes',
                'content' => "When a customer reports an incorrect invoice amount, compare the invoice total with the subscription plan, renewal date, discounts, taxes, and previous payments. Add an internal note before changing billing data.",
                'status' => 'published',
                'user' => $agentTwo,
                'days_ago' => 6,
            ],
            [
                'title' => 'Responding to suspicious login reports',
                'content' => "If a customer reports a suspicious login notification, ask them to confirm whether they recognize the location and device. Recommend changing the password, reviewing active sessions, enabling 2FA, and escalating if the login looks malicious.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 5,
            ],
            [
                'title' => 'What to check when notifications are delayed',
                'content' => "Delayed notifications can be caused by mail queue issues, provider delays, incorrect email settings, or suppressed addresses. Check the notification logs, mail queue, and recent system activity before replying.",
                'status' => 'published',
                'user' => $agentOne,
                'days_ago' => 4,
            ],
            [
                'title' => 'Explaining ticket priority levels',
                'content' => "Low means the issue has limited impact. Medium means the customer is affected but work can continue. High means an important workflow is blocked. Urgent means security, payment, or business-critical access is affected.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 3,
            ],
            [
                'title' => 'Draft reply for attachment upload failures',
                'content' => "Ask the customer to confirm the file type and size, then suggest trying a supported format. If the problem continues, request the error message and browser name before escalating to technical support.",
                'status' => 'draft',
                'user' => $agentTwo,
                'days_ago' => 2,
            ],
            [
                'title' => 'AI Assistant response quality checklist',
                'content' => "Before using an AI-generated reply, make sure it answers the customer's issue, uses a polite tone, avoids unsupported promises, and does not expose internal notes or sensitive account details.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 1,
            ],
        ];

        $knowledgeTable = null;
        foreach (['knowledge_bases', 'knowledge_base_articles'] as $table) {
            if (Schema::hasTable($table)) {
                $knowledgeTable = $table;
                break;
            }
        }

        if ($knowledgeTable) {
            foreach ($knowledgeArticles as $article) {
                $payload = [
                    'content' => $article['content'],
                ];

                if (Schema::hasColumn($knowledgeTable, 'status')) {
                    $payload['status'] = $article['status'];
                }

                if (Schema::hasColumn($knowledgeTable, 'user_id')) {
                    $payload['user_id'] = $article['user']->id;
                }

                if (Schema::hasColumn($knowledgeTable, 'created_at')) {
                    $payload['created_at'] = now()->subDays($article['days_ago']);
                }

                if (Schema::hasColumn($knowledgeTable, 'updated_at')) {
                    $payload['updated_at'] = now()->subDays(max($article['days_ago'] - 1, 0));
                }

                DB::table($knowledgeTable)->updateOrInsert(
                    ['title' => $article['title']],
                    $payload
                );
            }
        }

        foreach ($ticketTemplates as $ticketData) {
            if (empty($ticketData['is_deleted'])) {
                continue;
            }

            $ticket = $createdTickets[$ticketData['number']] ?? null;

            if ($ticket && method_exists($ticket, 'trashed') && ! $ticket->trashed()) {
                $ticket->delete();
            }
        }
    }
}
