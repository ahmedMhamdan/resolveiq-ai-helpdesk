<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\KnowledgeArticle;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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


        $knowledgeArticles = [
            ['Password reset email not received', 'If a user does not receive the password reset email, ask them to check spam, confirm the registered email address, and wait a few minutes. If the issue continues, check the mail queue and resend the reset link.', 'published', $support, $admin],
            ['Login returns 500 server error', 'Ask the user for the exact time of the error, browser type, and screenshot. Check application logs, session configuration, database connectivity, and recent deployments before replying.', 'published', $support, $agentOne],
            ['Dashboard is loading slowly', 'Start by checking browser cache, network speed, and whether the issue happens on all pages or only the dashboard. Then review server response time and database queries.', 'published', $support, $agentOne],
            ['Attachment upload failed', 'Confirm the file type and size. Ask the user to try a smaller file. Check upload validation rules, storage permissions, and server file size limits.', 'published', $support, $agentTwo],
            ['Email notifications are delayed', 'Check whether the notification is delayed for one user or all users. Review queue workers, mail provider status, failed jobs, and notification settings.', 'published', $support, $agentOne],
            ['Profile update does not save', 'Ask the user which field fails to update. Check validation errors, browser console errors, and whether the account has permission to update profile details.', 'published', $support, $agentTwo],
            ['Report export returns empty file', 'Check the selected date range and filters first. Then verify export permissions, query results, and whether the export job completed successfully.', 'published', $support, $admin],
            ['Cannot close a ticket', 'Verify the current ticket status and user role. Agents can close assigned tickets, while admins can close all tickets. Check authorization logic if the button is visible but fails.', 'published', $support, $admin],
            ['Invoice amount is incorrect', 'Compare the invoice amount with the user plan, renewal date, discounts, taxes, and previous payments. If there is a mismatch, escalate to billing with invoice details.', 'published', $billing, $agentTwo],
            ['Cannot download invoice PDF', 'Ask the user to try another browser and check if the invoice exists. Review PDF generation logs, file permissions, and invoice record status.', 'published', $billing, $agentTwo],
            ['Payment method rejected', 'Ask the user to confirm card details, available balance, and bank restrictions. If the issue continues, check payment gateway response and advise trying another method.', 'published', $billing, $admin],
            ['Refund request handling', 'Confirm order number, payment date, refund reason, and policy eligibility. Give the user a clear expected review time and escalate to billing if needed.', 'published', $billing, $agentTwo],
            ['Subscription renewal did not apply', 'Check payment success, subscription status, renewal timestamp, and account plan. If payment succeeded but access was not updated, escalate as high priority.', 'published', $billing, $agentOne],
            ['Account locked after failed logins', 'Verify the user identity before unlocking the account. Explain the lockout reason, reset failed attempts if appropriate, and recommend password reset.', 'published', $security, $agentOne],
            ['Two-factor authentication reset', 'Verify identity carefully before disabling 2FA. Ask for account email and recent account activity. After verification, disable old 2FA and guide the user to configure it again.', 'published', $security, $agentOne],
            ['Suspicious login notification', 'Ask the user if they recognize the location/device. If not, recommend password reset, revoke active sessions, enable 2FA, and review recent account activity.', 'published', $security, $agentTwo],
            ['Security settings are not saving', 'Ask which setting is failing and whether an error appears. Check validation, authorization, browser console errors, and server logs.', 'published', $security, $admin],
            ['Account access review request', 'Review recent logins, active sessions, and permission changes. Share only safe security information and recommend changing the password if suspicious activity is found.', 'published', $security, $agentTwo],
            ['Internal escalation guide', 'Use internal notes when the message should not be visible to the customer. Include ticket number, issue summary, attempted steps, urgency, and expected next action.', 'draft', $support, $admin],
            ['AI reply writing guidelines', 'AI replies should be polite, clear, and based on ticket details. Do not invent facts. If Knowledge Base content is relevant, use it as supporting context.', 'published', $support, $admin],
        ];

        foreach ($knowledgeArticles as [$title, $content, $status, $department, $author]) {
            KnowledgeArticle::updateOrCreate(
                ['title' => $title],
                [
                    'slug' => Str::slug($title),
                    'content' => $content,
                    'status' => $status,
                    'user_id' => $author->id,
                ]
            );
        }

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

        $extraIssues = [
            ['Cannot verify email address', 'The verification link says it is expired.', $support],
            ['Live chat widget is not loading', 'The support chat widget stays blank on my browser.', $support],
            ['Search results are not accurate', 'The search page does not show the ticket I need.', $support],
            ['Unable to change password', 'The change password form keeps returning an error.', $security],
            ['Need to revoke all sessions', 'Please sign out all devices connected to my account.', $security],
            ['Unknown device in account activity', 'There is a device listed that I do not recognize.', $security],
            ['Failed card payment', 'My card payment failed but the bank says it was approved.', $billing],
            ['Duplicate invoice generated', 'I can see two invoices for the same subscription period.', $billing],
            ['Plan upgrade not reflected', 'I upgraded my plan but the dashboard still shows the old plan.', $billing],
            ['Cannot open ticket details', 'The ticket details page keeps loading without opening.', $support],
            ['Reply box not submitting', 'I wrote a reply but it does not appear after submitting.', $support],
            ['File preview is broken', 'Uploaded attachments do not show a preview.', $support],
            ['Need billing statement', 'I need a full billing statement for this month.', $billing],
            ['Refund status is unclear', 'I do not know if my refund was approved or rejected.', $billing],
            ['Account recovery request', 'I lost access to my email and need account recovery help.', $security],
            ['2FA code is always invalid', 'The authenticator code fails every time I enter it.', $security],
            ['Notification preferences not saving', 'My notification settings return to old values.', $support],
            ['API token reset request', 'I need to reset the API token connected to my account.', $security],
            ['Billing address update failed', 'I cannot save the new billing address.', $billing],
            ['System timeout while saving', 'The system times out when I try to save changes.', $support],
            ['Customer portal blank after login', 'After login the portal page is completely blank.', $support],
            ['Tax value seems wrong', 'The invoice tax calculation seems incorrect.', $billing],
            ['Suspicious password reset email', 'I received a password reset email that I did not request.', $security],
            ['Need account data export', 'I want to export my account data for review.', $security],
            ['Ticket reopened by mistake', 'A solved ticket became open again without my action.', $support],
            ['Wrong priority on urgent issue', 'My urgent security issue was marked as low priority.', $security],
            ['Payment receipt missing', 'I paid but cannot find the payment receipt.', $billing],
            ['Cannot delete old attachment', 'I want to remove an old attachment but the delete action fails.', $support],
            ['Agent reply did not answer issue', 'The reply does not solve the actual problem I reported.', $support],
            ['Need manual invoice review', 'Please manually review this invoice because the amount looks duplicated.', $billing],
            ['Login alert from another country', 'I received a login alert from a country I never visited.', $security],
            ['Security question reset request', 'I need help resetting my security questions.', $security],
        ];

        $statuses = ['open', 'pending', 'solved', 'closed'];
        $priorities = [null, 'low', 'medium', 'high', 'urgent'];
        $agents = [null, $agentOne, $agentTwo];

        foreach ($extraIssues as $index => [$title, $description, $department]) {
            $ticketTemplates[] = [
                'number' => 'RIQ-' . (1025 + $index),
                'title' => $title,
                'description' => $description,
                'customer' => $customers[$index % count($customers)],
                'agent' => $agents[$index % count($agents)],
                'department' => $department,
                'status' => $statuses[$index % count($statuses)],
                'priority' => $priorities[$index % count($priorities)],
                'due_at' => $index % 5 === 0 ? null : now()->addDays(($index % 14) - 7),
            ];
        }

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
            $assignedUser = $ticket->agent_id ? User::query()->find($ticket->agent_id) : $admin;

            TicketReply::updateOrCreate(
                [
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->user_id,
                    'message' => 'I need an update on this issue when possible.',
                ],
                ['is_internal_note' => false]
            );

            if ($assignedUser) {
                TicketReply::updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'user_id' => $assignedUser->id,
                        'message' => 'We reviewed the ticket details and will follow the correct support steps.',
                    ],
                    ['is_internal_note' => false]
                );

                TicketReply::updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'user_id' => $assignedUser->id,
                        'message' => 'Internal note: check related logs, account status, and matching knowledge base article before the next reply.',
                    ],
                    ['is_internal_note' => true]
                );
            }
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
