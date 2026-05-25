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
use Illuminate\Support\Str;

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
            ['RIQ-1001', $customerOne, 'I tried clearing the browser cache and using another device, but the login still returns a 500 error after I submit the form.', false],
            ['RIQ-1001', $agentOne, 'Thanks for the details. We are checking the authentication service logs, recent failed login attempts, and whether the account session table has any errors.', false],
            ['RIQ-1001', $agentOne, 'Internal note: possible login service issue. Check Laravel logs, session driver, database sessions table, and recent deployment changes before asking the customer to retry again.', true],

            ['RIQ-1002', $customerTwo, 'The invoice should be for the basic monthly plan, but the total looks higher than expected.', false],
            ['RIQ-1002', $agentTwo, 'Internal review started for the billing record. Compare plan price, renewal date, tax, discounts, and previous unpaid balance.', true],

            ['RIQ-1003', $customerOne, 'I changed my phone and lost the authenticator app, so I cannot pass the 2FA challenge.', false],
            ['RIQ-1003', $admin, 'Internal note: verify account ownership before resetting two-factor authentication. Do not ask for passwords or recovery codes in chat.', true],

            ['RIQ-1004', $customerTwo, 'The notification eventually arrives, but it is delayed by around 20 minutes.', false],
            ['RIQ-1004', $agentOne, 'We are checking the mail queue, notification logs, and whether the address was delayed by the mail provider.', false],

            ['RIQ-1005', $customerOne, 'The download button shows loading for a few seconds and then nothing happens.', false],
            ['RIQ-1005', $agentTwo, 'Internal note: check invoice PDF generation, file permissions, storage link, and browser console errors.', true],

            ['RIQ-1006', $customerTwo, 'I noticed a login from a location I do not recognize.', false],
            ['RIQ-1006', $agentTwo, 'Please change your password, review active sessions, and confirm whether you recognize the device shown in the security notification.', false],

            ['RIQ-1008', $customerTwo, 'I checked spam and promotions folders, but I still did not receive the reset email.', false],
            ['RIQ-1008', $agentOne, 'Internal note: verify mail provider settings, reset token creation, and whether the email is suppressed or blocked.', true],

            ['RIQ-1010', $agentOne, 'Please confirm if you recognize the login location. If you do not, we recommend changing your password and enabling 2FA immediately.', false],
            ['RIQ-1010', $admin, 'Internal note: treat as urgent security alert if the customer does not recognize the location. Review login activity and active sessions.', true],

            ['RIQ-1014', $agentOne, 'Billing team is reviewing the renewal record and checking whether the subscription webhook completed successfully.', false],
            ['RIQ-1019', $agentTwo, 'We escalated this payment issue for urgent review because checkout is blocking the customer from completing payment.', true],

            ['RIQ-1021', $customerOne, 'The previous answer did not explain what I should do next.', false],
            ['RIQ-1021', $agentOne, 'Internal note: next reply should be clearer, avoid vague wording, and include exactly one practical next step.', true],

            ['RIQ-1023', $customerOne, 'I tried logging in several times and now the account says it is locked.', false],
            ['RIQ-1023', $admin, 'Internal note: check login rate limiting, lockout timestamp, and whether account verification is complete before unlocking.', true],
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
                'title' => 'Password reset email troubleshooting',
                'content' => "Use this article when a customer says the password reset email was not received.\n\nRecommended steps:\n1. Confirm the customer is using the correct account email.\n2. Ask them to check inbox, spam, promotions, and blocked sender settings.\n3. Check whether a reset token was created.\n4. Check the mail queue and SMTP provider logs.\n5. Resend the reset link only after confirming the email address.\n6. Never ask the customer to share their password or reset token.\n\nCustomer reply guidance: apologize briefly, give clear checks, and explain that support can resend the link after verification.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 14,
            ],
            [
                'title' => 'Login issue and 500 error checklist',
                'content' => "Use this article for login issue, login error, 500 error, session failure, or account access problems.\n\nAgent checklist:\n1. Confirm the exact error and when it appears.\n2. Ask whether the customer tried another browser or private window.\n3. Check application logs for authentication exceptions.\n4. Check session storage, database sessions table, and recent deployments.\n5. If multiple users are affected, escalate as an authentication service incident.\n\nCustomer reply guidance: avoid blaming the customer, explain that logs are being reviewed, and provide one safe next step.",
                'status' => 'published',
                'user' => $agentOne,
                'days_ago' => 13,
            ],
            [
                'title' => 'Two factor authentication 2FA reset policy',
                'content' => "Use this article when a customer lost access to an authenticator app or asks to reset two factor authentication / 2FA.\n\nSecurity rules:\n1. Verify account ownership before resetting 2FA.\n2. Do not ask for passwords, recovery codes, or screenshots containing secrets.\n3. Review recent login activity before disabling 2FA.\n4. Add an internal note explaining why the reset was approved.\n5. Ask the customer to re-enable 2FA after regaining access.\n\nSuggested priority: high if the customer is locked out, urgent if there is suspicious activity.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 12,
            ],
            [
                'title' => 'Suspicious login and security alert response',
                'content' => "Use this article for suspicious login notification, security alert, unknown location, or account compromise reports.\n\nRecommended steps:\n1. Ask whether the customer recognizes the device and location.\n2. Recommend changing the password immediately if the login is unknown.\n3. Recommend enabling 2FA.\n4. Review active sessions and revoke unknown sessions.\n5. Escalate as urgent if unauthorized access is likely.\n\nCustomer reply guidance: be calm, direct, and security-focused. Do not expose internal risk scores or investigation details.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 11,
            ],
            [
                'title' => 'Invoice amount dispute workflow',
                'content' => "Use this article when a customer says an invoice amount is incorrect.\n\nBilling checklist:\n1. Compare the invoice total with the active subscription plan.\n2. Check renewal date, taxes, discounts, credits, and previous unpaid balance.\n3. Confirm whether a plan upgrade or add-on was applied.\n4. Add an internal note before changing billing data.\n5. If the customer may have been overcharged, escalate to billing review.\n\nCustomer reply guidance: acknowledge the concern, explain what will be checked, and avoid promising a refund until billing confirms it.",
                'status' => 'published',
                'user' => $agentTwo,
                'days_ago' => 10,
            ],
            [
                'title' => 'Refund request follow-up workflow',
                'content' => "Use this article for refund request, payment dispute, or billing follow-up tickets.\n\nAgent checklist:\n1. Confirm the invoice number or payment date.\n2. Check refund eligibility based on policy.\n3. Review whether the request was already escalated.\n4. Add an internal note with the refund status.\n5. Give the customer a clear next step and realistic review timeframe.\n\nSuggested priority: medium for normal follow-up, high if payment access is blocked, urgent only for duplicate or business-critical charges.",
                'status' => 'published',
                'user' => $agentTwo,
                'days_ago' => 9,
            ],
            [
                'title' => 'Payment method rejected at checkout',
                'content' => "Use this article when a payment method is rejected or checkout fails.\n\nRecommended checks:\n1. Ask the customer to confirm card details were entered correctly without collecting card numbers.\n2. Check payment gateway logs for decline reason.\n3. Ask the customer to try another supported payment method.\n4. Escalate if many customers are affected or the billing page is blank.\n5. Do not ask for full card details in the ticket.\n\nCustomer reply guidance: keep it practical, protect payment data, and provide safe next steps.",
                'status' => 'published',
                'user' => $agentTwo,
                'days_ago' => 8,
            ],
            [
                'title' => 'Mail queue and delayed notification checks',
                'content' => "Use this article for delayed notifications, reset email delay, ticket reply notification missing, or mail queue issues.\n\nAgent checklist:\n1. Check notification logs and queue worker status.\n2. Check SMTP provider delivery status.\n3. Confirm the user's email address is verified.\n4. Check whether the address is suppressed, bounced, or blocked.\n5. If queue jobs are failing, escalate to engineering.\n\nCustomer reply guidance: explain that delivery is being checked and ask them to monitor inbox and spam while support reviews logs.",
                'status' => 'published',
                'user' => $agentOne,
                'days_ago' => 7,
            ],
            [
                'title' => 'Attachment upload failure troubleshooting',
                'content' => "Use this article when a customer cannot upload an attachment.\n\nRecommended checks:\n1. Ask for file type and approximate file size.\n2. Confirm the file type is supported.\n3. Ask whether the upload fails on another browser.\n4. Check storage permissions and upload validation errors.\n5. If uploads fail for multiple users, escalate as a storage or server configuration issue.\n\nCustomer reply guidance: request only the needed details and avoid asking for sensitive files unless required.",
                'status' => 'published',
                'user' => $agentOne,
                'days_ago' => 6,
            ],
            [
                'title' => 'Profile update form not saving',
                'content' => "Use this article when profile details, avatar upload, or account settings do not save.\n\nChecklist:\n1. Confirm which field fails to save.\n2. Check validation errors and browser console messages.\n3. Check whether the user is authenticated and email verified.\n4. Check file upload size/type if the issue is avatar related.\n5. Review recent profile controller or route changes.\n\nCustomer reply guidance: ask for the failed field and reassure the user that existing data is not lost.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 5,
            ],
            [
                'title' => 'Dashboard performance and slow loading',
                'content' => "Use this article for dashboard loads slowly, slow page, or long first request reports.\n\nAgent checklist:\n1. Ask whether the delay happens only on first visit or every request.\n2. For free hosting, explain that the service may wake up after inactivity.\n3. Check database query count for dashboard statistics.\n4. Check external API calls and notification queries.\n5. If the delay happens after login only, check session and dashboard role queries.\n\nCustomer reply guidance: be transparent and separate temporary hosting delay from real application bugs.",
                'status' => 'published',
                'user' => $agentOne,
                'days_ago' => 4,
            ],
            [
                'title' => 'Ticket priority decision guide',
                'content' => "Use this article when suggesting ticket priority.\n\nPriority rules:\nLow: minor inconvenience, no important workflow blocked.\nMedium: customer is affected but has a workaround.\nHigh: important workflow is blocked, repeated failure, or billing/account access problem.\nUrgent: security risk, suspicious login, payment blocked, account locked, or business-critical access unavailable.\n\nAI output should include an exact line like: Priority: high\nThen add a short reason.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 3,
            ],
            [
                'title' => 'Due date SLA suggestion guide',
                'content' => "Use this article when suggesting a due date.\n\nSLA guide:\nUrgent: same day or next business day.\nHigh: within 2 business days.\nMedium: within 3 to 5 business days.\nLow: within 5 to 7 business days.\nUnassigned tickets should be reviewed before setting a final due date.\n\nAI output should include an exact line like: Due Date: 2026-05-22\nThen add a short reason.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 2,
            ],
            [
                'title' => 'Writing clear customer replies',
                'content' => "Use this article to improve AI replies and agent responses.\n\nA strong customer reply should:\n1. Greet the customer by name when available.\n2. Acknowledge the issue directly.\n3. Explain what support is checking without exposing internal notes.\n4. Give one clear next step.\n5. Avoid unsupported promises.\n6. Stay under 140 words unless more detail is requested.\n\nFor unclear previous responses, rewrite the reply using simple language and practical next steps.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 1,
            ],
            [
                'title' => 'AI Assistant response quality checklist',
                'content' => "Before using an AI-generated reply, make sure it answers the customer's issue, uses a polite tone, avoids unsupported promises, and does not expose internal notes or sensitive account details.\n\nThe AI should use Knowledge Base context when relevant and should refuse unrelated requests that are not connected to the ticket or helpdesk workflow.",
                'status' => 'published',
                'user' => $admin,
                'days_ago' => 0,
            ],
        ];

        $knowledgeTable = null;
        foreach (['knowledge_articles', 'knowledge_base_articles', 'knowledge_bases'] as $table) {
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

                if (Schema::hasColumn($knowledgeTable, 'slug')) {
                    $payload['slug'] = Str::slug($article['title']);
                }

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
