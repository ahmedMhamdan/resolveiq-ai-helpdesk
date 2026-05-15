<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>ResolveIQ AI Helpdesk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">

    <style>
        .landing-page {
            min-height: 100vh;
            overflow: hidden;
            color: #f8fafc;
            background:
                radial-gradient(circle at 12% 18%, rgba(14, 165, 233, .22), transparent 28%),
                radial-gradient(circle at 82% 12%, rgba(124, 58, 237, .24), transparent 30%),
                radial-gradient(circle at 75% 86%, rgba(20, 184, 166, .13), transparent 26%),
                linear-gradient(135deg, #020617 0%, #061224 48%, #140f2f 100%);
        }

        .landing-shell {
            width: min(1320px, calc(100% - 48px));
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .landing-glow,
        .landing-glow-secondary {
            position: fixed;
            z-index: 1;
            pointer-events: none;
            filter: blur(18px);
            opacity: .8;
            border-radius: 999px;
            animation: landingFloat 12s ease-in-out infinite;
        }

        .landing-glow {
            width: 320px;
            height: 320px;
            left: -120px;
            top: 170px;
            background: rgba(6, 182, 212, .18);
        }

        .landing-glow-secondary {
            width: 360px;
            height: 360px;
            right: -150px;
            bottom: 60px;
            background: rgba(124, 58, 237, .2);
            animation-delay: -4s;
        }

        .landing-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 28px 0;
        }

        .landing-nav .brand {
            margin: 0;
        }

        .landing-nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .landing-hero {
            min-height: calc(100vh - 100px);
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(420px, .85fr);
            align-items: center;
            gap: 58px;
            padding: 54px 0 90px;
        }

        .landing-kicker {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border: 1px solid rgba(34, 211, 238, .24);
            border-radius: 999px;
            color: #22d3ee;
            background: rgba(14, 165, 233, .1);
            font-weight: 800;
            letter-spacing: .01em;
            box-shadow: 0 16px 50px rgba(6, 182, 212, .13);
        }

        .landing-kicker::before {
            content: "";
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #22d3ee;
            box-shadow: 0 0 0 8px rgba(34, 211, 238, .12);
            animation: landingPulse 1.8s ease-in-out infinite;
        }

        .landing-title {
            margin: 28px 0 22px;
            max-width: 840px;
            overflow: visible;
            font-size: clamp(48px, 6vw, 86px);
            line-height: 1.02;
            letter-spacing: -.055em;
            font-weight: 900;
        }

        .landing-title span {
            display: inline-block;
            overflow: visible;
            padding-right: .12em;
            margin-right: .02em;
            color: transparent;
            background: linear-gradient(135deg, #22d3ee 0%, #60a5fa 48%, #a78bfa 100%);
            background-clip: text;
            -webkit-background-clip: text;
            filter: drop-shadow(0 12px 30px rgba(56, 189, 248, .22));
        }

        .landing-page .brand-text span {
            color: #22d3ee;
            text-shadow: 0 0 18px rgba(34, 211, 238, .24);
        }


        .landing-description {
            max-width: 720px;
            margin: 0;
            color: #bfd0e8;
            font-size: 1.08rem;
            line-height: 1.9;
        }

        .landing-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            margin-top: 34px;
        }

        .landing-ghost-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #cbd5e1;
            font-weight: 750;
            padding: 11px 4px;
        }

        .landing-ghost-link span {
            transition: transform .2s ease;
        }

        .landing-ghost-link:hover span {
            transform: translateX(4px);
        }

        .landing-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 44px;
            max-width: 760px;
        }

        .landing-stat {
            padding: 18px;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 22px;
            background: rgba(15, 23, 42, .58);
            backdrop-filter: blur(16px);
            box-shadow: 0 18px 60px rgba(0, 0, 0, .18);
            transition: transform .22s ease, border-color .22s ease;
        }

        .landing-stat:hover {
            transform: translateY(-5px);
            border-color: rgba(34, 211, 238, .42);
        }

        .landing-stat strong {
            display: block;
            font-size: 1.75rem;
            font-weight: 900;
            letter-spacing: -.04em;
        }

        .landing-stat span {
            display: block;
            margin-top: 4px;
            color: #94a3b8;
            line-height: 1.5;
            font-size: .9rem;
        }

        .landing-preview-wrap {
            position: relative;
            min-height: 620px;
        }

        .landing-orbit-card,
        .landing-preview-card,
        .landing-ai-card {
            border: 1px solid rgba(148, 163, 184, .18);
            background: rgba(15, 23, 42, .74);
            backdrop-filter: blur(20px);
            box-shadow: 0 28px 80px rgba(0, 0, 0, .32);
        }

        .landing-preview-card {
            position: absolute;
            inset: 52px 0 auto auto;
            width: min(100%, 560px);
            padding: 26px;
            border-radius: 30px;
            animation: landingFloat 9s ease-in-out infinite;
        }

        .landing-preview-card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            pointer-events: none;
            background: linear-gradient(135deg, rgba(34, 211, 238, .5), transparent, rgba(139, 92, 246, .55));
            mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            mask-composite: exclude;
            -webkit-mask-composite: xor;
        }

        .landing-preview-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding-bottom: 22px;
            border-bottom: 1px solid rgba(148, 163, 184, .16);
        }

        .landing-preview-head strong,
        .landing-section-title strong {
            display: block;
            color: #f8fafc;
            font-size: 1.25rem;
            font-weight: 900;
        }

        .landing-preview-head span:not(.badge),
        .landing-section-title span {
            display: block;
            margin-top: 6px;
            color: #94a3b8;
            line-height: 1.55;
        }

        .landing-ticket-row {
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            margin-top: 14px;
            padding: 20px;
            border: 1px solid rgba(148, 163, 184, .15);
            border-radius: 22px;
            background: rgba(30, 41, 59, .5);
        }

        .landing-ticket-row::after {
            content: "";
            position: absolute;
            top: -80%;
            left: -35%;
            width: 30%;
            height: 260%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, .12), transparent);
            transform: rotate(18deg);
            animation: landingScan 4s ease-in-out infinite;
        }

        .landing-ticket-row:nth-child(3)::after { animation-delay: .9s; }
        .landing-ticket-row:nth-child(4)::after { animation-delay: 1.7s; }

        .landing-ticket-row strong {
            display: block;
            margin-bottom: 6px;
            color: #f8fafc;
        }

        .landing-ticket-row span:not(.priority):not(.badge) {
            color: #a7b7d1;
            line-height: 1.5;
        }

        .landing-ai-card {
            position: absolute;
            left: 4px;
            bottom: 26px;
            width: 360px;
            padding: 22px;
            border-radius: 26px;
            animation: landingFloat 8s ease-in-out infinite reverse;
        }

        .landing-ai-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            margin-bottom: 16px;
            border-radius: 17px;
            color: #fff;
            font-weight: 900;
            background: linear-gradient(135deg, #22d3ee, #8b5cf6);
            box-shadow: 0 18px 50px rgba(99, 102, 241, .28);
        }

        .landing-ai-card h3 {
            margin: 0 0 10px;
            font-size: 1.25rem;
            letter-spacing: -.03em;
        }

        .landing-ai-card p {
            margin: 0;
            color: #a7b7d1;
            line-height: 1.7;
        }

        .landing-mini-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }

        .landing-mini-actions span {
            padding: 7px 10px;
            border-radius: 999px;
            color: #c4b5fd;
            background: rgba(124, 58, 237, .16);
            font-size: .78rem;
            font-weight: 800;
        }

        .landing-orbit-card {
            position: absolute;
            right: 20px;
            bottom: 0;
            width: 220px;
            padding: 18px;
            border-radius: 24px;
            animation: landingFloat 7s ease-in-out infinite;
            animation-delay: -2.5s;
        }

        .landing-orbit-card strong {
            display: block;
            margin-bottom: 8px;
            font-size: 1.6rem;
            color: #22d3ee;
        }

        .landing-orbit-card span {
            color: #a7b7d1;
            line-height: 1.55;
        }

        .landing-section {
            padding: 90px 0;
        }

        .landing-section-title {
            max-width: 760px;
            margin-bottom: 32px;
        }

        .landing-section-title h2 {
            margin: 14px 0 0;
            color: #f8fafc;
            font-size: clamp(34px, 4vw, 54px);
            line-height: 1.05;
            letter-spacing: -.055em;
            font-weight: 900;
        }

        .landing-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .landing-feature-card,
        .landing-step-card,
        .landing-role-card {
            position: relative;
            overflow: hidden;
            min-height: 240px;
            padding: 24px;
            border: 1px solid rgba(148, 163, 184, .16);
            border-radius: 28px;
            background: rgba(15, 23, 42, .62);
            backdrop-filter: blur(18px);
            transition: transform .22s ease, border-color .22s ease, background-color .22s ease;
        }

        .landing-feature-card:hover,
        .landing-step-card:hover,
        .landing-role-card:hover {
            transform: translateY(-7px);
            border-color: rgba(34, 211, 238, .36);
            background: rgba(15, 23, 42, .82);
        }

        .landing-feature-icon,
        .landing-step-number {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            margin-bottom: 22px;
            border-radius: 17px;
            color: #fff;
            font-weight: 900;
            background: linear-gradient(135deg, #22d3ee, #8b5cf6);
            box-shadow: 0 18px 50px rgba(99, 102, 241, .2);
        }

        .landing-feature-card h3,
        .landing-step-card h3,
        .landing-role-card h3 {
            margin: 0 0 12px;
            color: #f8fafc;
            font-size: 1.2rem;
            letter-spacing: -.03em;
        }

        .landing-feature-card p,
        .landing-step-card p,
        .landing-role-card p {
            margin: 0;
            color: #a7b7d1;
            line-height: 1.75;
        }

        .landing-workflow {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .landing-roles {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .landing-role-card {
            min-height: 260px;
        }

        .landing-role-card ul {
            display: grid;
            gap: 11px;
            margin: 18px 0 0;
            padding: 0;
            list-style: none;
            color: #cbd5e1;
        }

        .landing-role-card li {
            display: flex;
            gap: 9px;
            line-height: 1.55;
        }

        .landing-role-card li::before {
            content: "✓";
            color: #22d3ee;
            font-weight: 900;
        }

        .landing-showcase {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 22px;
            align-items: stretch;
        }

        .landing-panel {
            min-height: 430px;
            padding: 26px;
            border: 1px solid rgba(148, 163, 184, .16);
            border-radius: 32px;
            background: rgba(15, 23, 42, .68);
            box-shadow: 0 28px 80px rgba(0, 0, 0, .28);
            backdrop-filter: blur(18px);
        }

        .landing-panel h3 {
            margin: 0 0 10px;
            color: #f8fafc;
            font-size: 1.35rem;
        }

        .landing-panel p {
            margin: 0 0 22px;
            color: #a7b7d1;
            line-height: 1.7;
        }

        .landing-metric-list {
            display: grid;
            gap: 14px;
        }

        .landing-metric {
            padding: 18px;
            border: 1px solid rgba(148, 163, 184, .14);
            border-radius: 20px;
            background: rgba(30, 41, 59, .48);
        }

        .landing-metric div {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 11px;
            gap: 16px;
        }

        .landing-bar {
            height: 9px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(148, 163, 184, .16);
        }

        .landing-bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #22d3ee, #8b5cf6);
            animation: landingGrow 1.2s ease both;
        }

        .landing-ai-workspace {
            display: grid;
            gap: 14px;
        }

        .landing-ai-output {
            padding: 18px;
            border: 1px dashed rgba(139, 92, 246, .35);
            border-radius: 22px;
            background: rgba(30, 41, 59, .45);
        }

        .landing-ai-output strong {
            display: block;
            color: #f8fafc;
            margin-bottom: 8px;
        }

        .landing-ai-output p {
            margin: 0;
            color: #cbd5e1;
        }

        .landing-footer-cta {
            margin: 88px 0 56px;
            padding: 44px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 24px;
            border: 1px solid rgba(34, 211, 238, .2);
            border-radius: 34px;
            background:
                radial-gradient(circle at top left, rgba(34, 211, 238, .16), transparent 28%),
                rgba(15, 23, 42, .72);
            backdrop-filter: blur(18px);
            box-shadow: 0 28px 90px rgba(0, 0, 0, .28);
        }

        .landing-footer-cta h2 {
            margin: 0 0 12px;
            font-size: clamp(30px, 4vw, 48px);
            line-height: 1.05;
            letter-spacing: -.05em;
        }

        .landing-footer-cta p {
            margin: 0;
            max-width: 720px;
            color: #a7b7d1;
            line-height: 1.7;
        }

        .reveal-up {
            opacity: 0;
            transform: translateY(24px);
            animation: revealUp .8s ease forwards;
        }

        .delay-1 { animation-delay: .12s; }
        .delay-2 { animation-delay: .24s; }
        .delay-3 { animation-delay: .36s; }
        .delay-4 { animation-delay: .48s; }

        @keyframes revealUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes landingFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-16px); }
        }

        @keyframes landingPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(.78); opacity: .55; }
        }

        @keyframes landingScan {
            0% { left: -45%; }
            45%, 100% { left: 130%; }
        }

        @keyframes landingGrow {
            from { width: 0; }
        }

        @media (max-width: 1100px) {
            .landing-hero,
            .landing-showcase,
            .landing-footer-cta {
                grid-template-columns: 1fr;
            }

            .landing-preview-wrap {
                min-height: 560px;
            }

            .landing-preview-card {
                right: auto;
                left: 0;
            }

            .landing-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .landing-shell {
                width: min(100% - 28px, 1320px);
            }

            .landing-nav {
                align-items: flex-start;
                flex-direction: column;
            }

            .landing-hero {
                padding-top: 28px;
            }

            .landing-title {
                font-size: clamp(42px, 13vw, 66px);
            }

            .landing-stats,
            .landing-grid,
            .landing-workflow,
            .landing-roles {
                grid-template-columns: 1fr;
            }

            .landing-preview-wrap {
                min-height: auto;
            }

            .landing-preview-card,
            .landing-ai-card,
            .landing-orbit-card {
                position: relative;
                inset: auto;
                width: 100%;
                margin-top: 18px;
            }

            .landing-footer-cta {
                padding: 28px;
            }
        }
    </style>
</head>
<body>
    <main class="landing-page">
        <div class="landing-glow"></div>
        <div class="landing-glow-secondary"></div>

        <div class="landing-shell">
            <nav class="landing-nav">
                <a href="{{ route('home') }}" class="brand reveal-up">
                    <span class="brand-mark">R</span>
                    <span class="brand-text">Resolve<span>IQ</span></span>
                </a>

                <div class="landing-nav-actions reveal-up delay-1">
                    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                </div>
            </nav>

            <section class="landing-hero">
                <div class="landing-hero-content">
                    <span class="landing-kicker reveal-up">AI-powered Helpdesk Platform</span>

                    <h1 class="landing-title reveal-up delay-1">
                        Manage support tickets smarter with <span>ResolveIQ</span>
                    </h1>

                    <p class="landing-description reveal-up delay-2">
                        ResolveIQ is a role-based helpdesk system for receiving tickets, assigning agents,
                        tracking activity, setting priorities and due dates, and generating AI-assisted
                        summaries, replies, priority suggestions, and SLA-style due dates.
                    </p>

                    <div class="landing-actions reveal-up delay-3">
                        <a href="{{ route('register') }}" class="btn btn-primary">Create Account</a>
                        <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                        <a href="#features" class="landing-ghost-link">Explore features <span>→</span></a>
                    </div>

                    <div class="landing-stats reveal-up delay-4">
                        <div class="landing-stat">
                            <strong>3 roles</strong>
                            <span>Admin, agent, and customer workflows.</span>
                        </div>

                        <div class="landing-stat">
                            <strong>5 AI actions</strong>
                            <span>Summary, reply, priority, due date, and custom instructions.</span>
                        </div>

                        <div class="landing-stat">
                            <strong>SLA ready</strong>
                            <span>Due dates and overdue tickets stay visible.</span>
                        </div>
                    </div>
                </div>

                <div class="landing-preview-wrap reveal-up delay-2">
                    <div class="landing-preview-card">
                        <div class="landing-preview-head">
                            <div>
                                <strong>Live Ticket Preview</strong>
                                <span>Support workflow overview</span>
                            </div>
                            <span class="badge open">Open</span>
                        </div>

                        <div class="landing-ticket-row">
                            <div>
                                <strong>Unable to login</strong>
                                <span>Waiting for agent response</span>
                            </div>
                            <span class="priority urgent">Urgent</span>
                        </div>

                        <div class="landing-ticket-row">
                            <div>
                                <strong>Invoice amount issue</strong>
                                <span>Needs admin review</span>
                            </div>
                            <span class="priority medium">Medium</span>
                        </div>

                        <div class="landing-ticket-row">
                            <div>
                                <strong>Email notification delay</strong>
                                <span>Resolved successfully</span>
                            </div>
                            <span class="badge solved">Solved</span>
                        </div>
                    </div>

                    <div class="landing-ai-card">
                        <div class="landing-ai-icon">AI</div>
                        <h3>Assistant workspace</h3>
                        <p>
                            Generate clear summaries, customer replies, internal notes, priority suggestions,
                            and due date recommendations from real ticket context.
                        </p>
                        <div class="landing-mini-actions">
                            <span>Summary</span>
                            <span>Reply</span>
                            <span>Due Date</span>
                            <span>Custom</span>
                        </div>
                    </div>

                    <div class="landing-orbit-card">
                        <strong>24/7</strong>
                        <span>Cleaner support flow with activity logs and AI assistance.</span>
                    </div>
                </div>
            </section>

            <section class="landing-section" id="features">
                <div class="landing-section-title reveal-up">
                    <span>Core Features</span>
                    <h2>Everything needed for a realistic support workflow.</h2>
                </div>

                <div class="landing-grid">
                    <div class="landing-feature-card reveal-up delay-1">
                        <div class="landing-feature-icon">T</div>
                        <h3>Ticket Management</h3>
                        <p>Create, assign, update, close, reopen, delete, restore, and track support tickets from one clean interface.</p>
                    </div>

                    <div class="landing-feature-card reveal-up delay-2">
                        <div class="landing-feature-icon">AI</div>
                        <h3>AI Assistant</h3>
                        <p>Generate ticket summaries, suggested replies, priority recommendations, due dates, and custom support guidance.</p>
                    </div>

                    <div class="landing-feature-card reveal-up delay-3">
                        <div class="landing-feature-icon">S</div>
                        <h3>SLA & Due Dates</h3>
                        <p>Set due dates, identify overdue tickets, and make urgent work visible before it is missed.</p>
                    </div>

                    <div class="landing-feature-card reveal-up delay-4">
                        <div class="landing-feature-icon">R</div>
                        <h3>Role-Based Access</h3>
                        <p>Admins manage all tickets, agents work only on assigned tickets, and customers see their own requests only.</p>
                    </div>
                </div>
            </section>

            <section class="landing-section">
                <div class="landing-section-title reveal-up">
                    <span>Workflow</span>
                    <h2>From customer issue to resolved ticket.</h2>
                </div>

                <div class="landing-workflow">
                    <div class="landing-step-card reveal-up delay-1">
                        <div class="landing-step-number">01</div>
                        <h3>Receive the ticket</h3>
                        <p>The customer creates a ticket with title, description, and department while the system keeps priority and assignment controlled.</p>
                    </div>

                    <div class="landing-step-card reveal-up delay-2">
                        <div class="landing-step-number">02</div>
                        <h3>Assign and prioritize</h3>
                        <p>Admins assign agents, set priority, add due dates, monitor unassigned tickets, and review overdue work.</p>
                    </div>

                    <div class="landing-step-card reveal-up delay-3">
                        <div class="landing-step-number">03</div>
                        <h3>Resolve with AI support</h3>
                        <p>Agents use AI suggestions, add replies or internal notes, track activity, and close or reopen tickets when needed.</p>
                    </div>
                </div>
            </section>

            <section class="landing-section">
                <div class="landing-showcase">
                    <div class="landing-panel reveal-up">
                        <h3>Admin dashboard snapshot</h3>
                        <p>Fast overview for tickets, priorities, activity, overdue items, and support performance.</p>

                        <div class="landing-metric-list">
                            <div class="landing-metric">
                                <div>
                                    <strong>Open tickets</strong>
                                    <span>76%</span>
                                </div>
                                <div class="landing-bar"><span style="width: 76%;"></span></div>
                            </div>

                            <div class="landing-metric">
                                <div>
                                    <strong>Assigned work</strong>
                                    <span>68%</span>
                                </div>
                                <div class="landing-bar"><span style="width: 68%;"></span></div>
                            </div>

                            <div class="landing-metric">
                                <div>
                                    <strong>AI assisted replies</strong>
                                    <span>84%</span>
                                </div>
                                <div class="landing-bar"><span style="width: 84%;"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="landing-panel reveal-up delay-1">
                        <h3>AI output preview</h3>
                        <p>AI suggestions are generated from the selected ticket, recent replies, assigned agent, department, status, priority, and due date.</p>

                        <div class="landing-ai-workspace">
                            <div class="landing-ai-output">
                                <strong>1. Main issue</strong>
                                <p>The customer cannot access the account and already tried clearing the browser cache.</p>
                            </div>

                            <div class="landing-ai-output">
                                <strong>2. Recommended next step</strong>
                                <p>Ask the customer to confirm the exact error message, then verify login service status and account lock settings.</p>
                            </div>

                            <div class="landing-ai-output">
                                <strong>3. Suggested due date</strong>
                                <p>Set this ticket to urgent SLA timing because it blocks user access to the account.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="landing-section">
                <div class="landing-section-title reveal-up">
                    <span>Access Model</span>
                    <h2>Clear permissions for each user type.</h2>
                </div>

                <div class="landing-roles">
                    <div class="landing-role-card reveal-up delay-1">
                        <h3>Admin</h3>
                        <p>Full control over the support operation.</p>
                        <ul>
                            <li>Manage all tickets and users</li>
                            <li>Assign agents and set priorities</li>
                            <li>Review overdue and unassigned tickets</li>
                        </ul>
                    </div>

                    <div class="landing-role-card reveal-up delay-2">
                        <h3>Agent</h3>
                        <p>Focused workspace for assigned tickets.</p>
                        <ul>
                            <li>View assigned tickets only</li>
                            <li>Add replies and internal notes</li>
                            <li>Use AI on assigned tickets</li>
                        </ul>
                    </div>

                    <div class="landing-role-card reveal-up delay-3">
                        <h3>Customer</h3>
                        <p>Simple request flow without admin access.</p>
                        <ul>
                            <li>Create support tickets</li>
                            <li>Track own ticket status</li>
                            <li>Reply to support updates</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="landing-footer-cta reveal-up">
                <div>
                    <h2>Ready to try ResolveIQ?</h2>
                    <p>
                        Create an account, open a ticket, and explore how the AI assistant improves the support workflow from the first response to final resolution.
                    </p>
                </div>

                <div class="landing-actions">
                    <a href="{{ route('register') }}" class="btn btn-primary">Create Account</a>
                    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
