@extends('layouts.app')

@section('title', __('tickets.title_edit'))

@section('content')
@php
    $currentRole = strtolower($role ?? auth()->user()?->role?->name ?? 'user');
    $isAdmin = $currentRole === 'admin';
@endphp

<div class="page-head">
    <div>
        <h1>{{ __('tickets.title_edit') }}</h1>
        <p>{{ __('tickets.edit_subtitle') }}</p>
    </div>

    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
        {{ __('tickets.back_to_ticket') }}
    </a>
</div>

<div class="table-card">
    <div class="card-head">
        <div>
            <h2>{{ $ticket->ticket_number }}</h2>
            <p>{{ __('tickets.edit_instructions') }}</p>
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

    <form action="{{ route('tickets.update', $ticket) }}" method="POST" class="ticket-form">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full">
                <label for="title">{{ __('tickets.ticket_title_label') }}</label>
                <input
                type="text"
                class="readonly-field"
                value="{{ $ticket->title }}"
                readonly
            >
            </div>

            <div class="form-group">
                <label for="department_id">{{ __('tickets.department_label') }}</label>
                <select id="department_id" name="department_id" required>
                    <option value="">{{ __('tickets.select_department') }}</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id', $ticket->department_id) == $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($isAdmin)
                <div class="form-group">
                    <label for="agent_id">{{ __('tickets.agent_label') }}</label>
                    <select id="agent_id" name="agent_id">
                        <option value="">{{ __('tickets.unassigned') }}</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" @selected(old('agent_id', $ticket->agent_id) == $agent->id)>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="form-group">
                <label for="status">{{ __('tickets.status_label') }}</label>
                <select id="status" name="status" required>
                    <option value="open" @selected(old('status', $ticket->status) === 'open')>{{ __('tickets.open') }}</option>
                    <option value="pending" @selected(old('status', $ticket->status) === 'pending')>{{ __('tickets.pending') }}</option>
                    <option value="solved" @selected(old('status', $ticket->status) === 'solved')>{{ __('tickets.solved') }}</option>
                    <option value="closed" @selected(old('status', $ticket->status) === 'closed')>{{ __('tickets.closed') }}</option>
                </select>
            </div>

            @if ($isAdmin)
                @php
                    $selectedPriority = old('priority', $ticket->priority);
                @endphp

                <div class="form-group">
                    <label for="priority">{{ __('tickets.priority_label') }}</label>
                    <select name="priority" id="priority">
                        <option value="" @selected($selectedPriority === null || $selectedPriority === '')>{{ __('tickets.not_set') }}</option>
                        <option value="low" @selected($selectedPriority === 'low')>{{ __('tickets.low') }}</option>
                        <option value="medium" @selected($selectedPriority === 'medium')>{{ __('tickets.medium') }}</option>
                        <option value="high" @selected($selectedPriority === 'high')>{{ __('tickets.high') }}</option>
                        <option value="urgent" @selected($selectedPriority === 'urgent')>{{ __('tickets.urgent') }}</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label for="due_at">{{ __('tickets.due_date_label') }}</label>

                    <div class="date-picker-box" id="duePickerBox">
                        <input
                            type="date"
                            id="due_at"
                            name="due_at"
                            value="{{ old('due_at', optional($ticket->due_at)->format('Y-m-d')) }}"
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
                <textarea class="readonly-field" readonly>{{ $ticket->description }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-danger-soft">
                {{ __('tickets.cancel') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('tickets.update_ticket') }}
            </button>
        </div>
    </form>
</div>

@if ($isAdmin)
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
            dueInput?.showPicker?.();
        });

        dueInput?.addEventListener('change', updateDateDisplay);

        updateDateDisplay();
    </script>
@endif
@endsection