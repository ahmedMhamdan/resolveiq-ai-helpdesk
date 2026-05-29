@extends('layouts.app')

@section('title', __('tickets.title_create'))

@section('content')
    @php
        $currentUserRole = strtolower(auth()->user()?->role?->name ?? 'user');
        $canManageTicketDetails = $currentUserRole === 'admin';
    @endphp

    <div class="page-head">
        <div>
            <h1>{{ __('tickets.title_create') }}</h1>
            <p>
                {{ $canManageTicketDetails
                    ? __('tickets.create_subtitle_agent')
                    : __('tickets.create_subtitle_user') }}
            </p>
        </div>

        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
            {{ __('tickets.back_to_tickets') }}
        </a>
    </div>

    <div class="table-card ticket-create-card">
        <div class="card-head">
            <div>
                <h2>{{ __('tickets.title_create') }}</h2>
                <p>{{ __('tickets.create_subtitle') }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('tickets.store') }}" method="POST" class="ticket-form">
            @csrf

            <div class="form-grid">
                <div class="form-group full">
                    <label for="title">{{ __('tickets.ticket_title_label') }}</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        placeholder="{{ __('tickets.title_placeholder') }}"
                        required
                    >
                </div>

                <div class="form-group {{ $canManageTicketDetails ? '' : 'full' }}">
                    <label for="department_id">{{ __('tickets.department_label') }}</label>
                    <select id="department_id" name="department_id" required>
                        <option value="">{{ __('tickets.select_department') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if ($canManageTicketDetails)
                    <div class="form-group">
                        <label for="agent_id">{{ __('tickets.agent_label') }}</label>
                        <select id="agent_id" name="agent_id">
                            <option value="">{{ __('tickets.unassigned') }}</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" @selected(old('agent_id') == $agent->id)>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                    <label for="priority">{{ __('tickets.priority_label') }}</label>
                    <select id="priority" name="priority">
                        <option value="" @selected(old('priority') === null || old('priority') === '')>
                            {{ __('tickets.not_set') }}
                        </option>
                        <option value="low" @selected(old('priority') === 'low')>{{ __('tickets.low') }}</option>
                        <option value="medium" @selected(old('priority') === 'medium')>{{ __('tickets.medium') }}</option>
                        <option value="high" @selected(old('priority') === 'high')>{{ __('tickets.high') }}</option>
                        <option value="urgent" @selected(old('priority') === 'urgent')>{{ __('tickets.urgent') }}</option>
                    </select>
                </div>

                    <div class="form-group">
                        <label for="due_at">{{ __('tickets.due_date_label') }}</label>

                        <div class="date-picker-box" id="duePickerBox">
                            <input
                                type="date"
                                id="due_at"
                                name="due_at"
                                value="{{ old('due_at') }}"
                            >

                            <span class="date-display" id="dueDateDisplay">{{ __('tickets.select_due_date') }}</span>

                            <span class="date-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="3"></rect>
                                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                @endif

                <div class="form-group full">
                    <label for="description">{{ __('tickets.description_label') }}</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="7"
                        placeholder="{{ __('tickets.description_placeholder') }}"
                        required
                    >{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="form-actions create-actions">
                <a href="{{ route('tickets.index') }}" class="btn btn-danger-soft">
                    {{ __('tickets.cancel') }}
                </a>

                <button type="submit" class="btn btn-primary">
                    {{ __('tickets.create_ticket') }}
                </button>
            </div>
        </form>
    </div>

    @if ($canManageTicketDetails)
        <script>
            const dueInput = document.getElementById('due_at');
            const dueBox = document.getElementById('duePickerBox');
            const dueDisplay = document.getElementById('dueDateDisplay');
            const selectDueDate = @json(__('tickets.select_due_date'));

            function formatDate(value) {
                if (!value) {
                    return selectDueDate;
                }

                const [year, month, day] = value.split('-');
                return `${day}/${month}/${year}`;
            }

            function updateDateDisplay() {
                if (!dueInput || !dueBox || !dueDisplay) {
                    return;
                }

                dueDisplay.textContent = formatDate(dueInput.value);
                dueBox.classList.toggle('has-value', dueInput.value !== '');
            }

            dueBox?.addEventListener('click', () => {
                if (dueInput.showPicker) {
                    dueInput.showPicker();
                } else {
                    dueInput.focus();
                }
            });

            dueInput?.addEventListener('change', updateDateDisplay);

            updateDateDisplay();
        </script>
    @endif
@endsection