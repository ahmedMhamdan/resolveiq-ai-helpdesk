<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ResolveIQ helps support teams organize tickets, assign work, and draft replies in one helpdesk workspace.">
    <title>ResolveIQ | Support ticket workspace</title>
    @include('partials.head-assets')
</head>
<body class="home-body" id="top">
    <div class="home-shell">
        <header class="home-header">
            <x-brand />
            <nav class="home-links" aria-label="Product">
                <a href="#features">Features</a>
                <a href="#workflow">Workflow</a>
                <a href="#roles">Roles</a>
            </nav>
            <div class="home-actions">
                <x-theme-toggle />
                @auth
                    <a class="home-button" href="{{ route('dashboard') }}">Open dashboard <x-icon name="arrow" /></a>
                @else
                    <a class="home-sign-in" href="{{ route('login') }}">Sign in</a>
                @endauth
            </div>
        </header>

        <main>
            <section class="home-hero">
                <div class="home-intro">
                    <h1>Take support<br class="home-heading-break"> from request<br class="home-heading-break"> to <em>resolution.</em></h1>
                    <p>Give every support request a place, an owner, and a clear next step. Track tickets and due dates in one workspace, with AI to help draft the reply.</p>
                    <div class="home-actions">
                        @auth
                            <a class="home-button" href="{{ route('tickets.index') }}">View tickets <x-icon name="arrow" /></a>
                        @else
                            <a class="home-button" href="{{ route('register') }}">Create an account <x-icon name="arrow" /></a>
                            <a class="home-text-link" href="{{ route('login') }}#demo-heading">Try the demo <x-icon name="arrow" /></a>
                        @endauth
                    </div>
                </div>

                <div class="home-preview" aria-label="Example helpdesk workspace">
                    <div class="home-preview-toolbar">
                        <span><x-icon name="inbox" /> Support inbox</span>
                        <small>Example workspace</small>
                    </div>
                    <div class="home-preview-content">
                        <div class="home-preview-queue">
                            <h2>Ticket queue</h2>
                            <div class="home-preview-row">
                                <div><strong>Account access issue</strong><small>Account Support · RIQ-1003</small></div>
                                <span class="home-preview-status is-urgent">Urgent</span>
                            </div>
                            <div class="home-preview-row">
                                <div><strong>Billing discrepancy</strong><small>Billing · RIQ-1005</small></div>
                                <span class="home-preview-status is-pending">Pending</span>
                            </div>
                            <div class="home-preview-row">
                                <div><strong>Notification delivery delay</strong><small>Technical Support · RIQ-1008</small></div>
                                <span class="home-preview-status is-solved">Solved</span>
                            </div>
                        </div>
                        <div class="home-preview-draft">
                            <div class="home-preview-draft-head"><x-icon name="spark" /> Suggested reply</div>
                            <p>Could you share the exact error message you see when signing in? That will help us check what’s blocking access to your account.</p>
                            <small>Draft · For agent review</small>
                        </div>
                    </div>
                </div>
            </section>

            <section class="home-section" id="features" aria-labelledby="features-heading">
                <h2 id="features-heading">Keep support work in view.</h2>
                <div class="home-features">
                    <article>
                        <x-icon name="tickets" />
                        <h3>Ticket management</h3>
                        <p>Follow each request from intake to resolution with replies, internal notes, and an activity history.</p>
                    </article>
                    <article>
                        <x-icon name="clock" />
                        <h3>Due dates and priorities</h3>
                        <p>Assign tickets to agents, set deadlines, and find overdue work in a dedicated queue.</p>
                    </article>
                    <article>
                        <x-icon name="spark" />
                        <h3>AI assistance</h3>
                        <p>Summarize a conversation, draft a reply, or suggest a priority from the ticket’s context.</p>
                    </article>
                </div>
            </section>

            <section class="home-section home-workflow" id="workflow" aria-labelledby="workflow-heading">
                <h2 id="workflow-heading">How a ticket gets resolved</h2>
                <ol>
                    <li><h3>Receive</h3><p>A customer sends a request to the right department.</p></li>
                    <li><h3>Assign</h3><p>An admin assigns an agent and can set a priority and due date.</p></li>
                    <li><h3>Resolve</h3><p>The agent replies, tracks the conversation, and closes the ticket when the work is done.</p></li>
                </ol>
            </section>

            <section class="home-section" id="roles" aria-labelledby="roles-heading">
                <h2 id="roles-heading">A workspace for each role</h2>
                <dl class="home-roles">
                    <div><dt>Admin</dt><dd>Manage users, departments, ticket assignments, and overdue work.</dd></div>
                    <div><dt>Agent</dt><dd>Work on assigned tickets, add internal notes, and use AI assistance.</dd></div>
                    <div><dt>Customer</dt><dd>Submit support requests, track progress, and reply to the team.</dd></div>
                </dl>
            </section>
        </main>

        <footer class="home-footer">
            <x-brand />
            <span>© {{ date('Y') }} ResolveIQ</span>
            <a href="#top">Back to top</a>
        </footer>
    </div>
</body>
</html>
