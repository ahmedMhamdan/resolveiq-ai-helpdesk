@extends('layouts.app')

@section('title', 'Edit Ticket')

@section('content')
<div class="page-head">
    <div>
        <h1>Edit Ticket</h1>
        <p>Update ticket details, status, priority, and assignment.</p>
    </div>

    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
        Back to Ticket
    </a>
</div>

<div class="table-card">
    <div class="card-head">
        <div>
            <h2>{{ $ticket->ticket_number }}</h2>
            <p>Edit the ticket information below.</p>
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
                <label for="title">Ticket Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $ticket->title) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="department_id">Department</label>
                <select id="department_id" name="department_id" required>
                    <option value="">Select department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id', $ticket->department_id) == $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="agent_id">Agent</label>
                <select id="agent_id" name="agent_id">
                    <option value="">Unassigned</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->id }}" @selected(old('agent_id', $ticket->agent_id) == $agent->id)>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="open" @selected(old('status', $ticket->status) === 'open')>Open</option>
                    <option value="pending" @selected(old('status', $ticket->status) === 'pending')>Pending</option>
                    <option value="solved" @selected(old('status', $ticket->status) === 'solved')>Solved</option>
                    <option value="closed" @selected(old('status', $ticket->status) === 'closed')>Closed</option>
                </select>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" required>
                    <option value="low" @selected(old('priority', $ticket->priority) === 'low')>Low</option>
                    <option value="medium" @selected(old('priority', $ticket->priority) === 'medium')>Medium</option>
                    <option value="high" @selected(old('priority', $ticket->priority) === 'high')>High</option>
                    <option value="urgent" @selected(old('priority', $ticket->priority) === 'urgent')>Urgent</option>
                </select>
            </div>

            <div class="form-group full">
                <label for="due_at">Due Date</label>

                <div class="date-picker-box" id="duePickerBox">
                    <input
                        type="date"
                        id="due_at"
                        name="due_at"
                        value="{{ old('due_at', optional($ticket->due_at)->format('Y-m-d')) }}"
                    >

                    <span class="date-display" id="dueDateDisplay">Select due date</span>

                    <span class="date-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="3"></rect>
                            <path d="M16 2v4M8 2v4M3 10h18"></path>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="form-group full">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="7"
                    required
                >{{ old('description', $ticket->description) }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-danger-soft">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Update Ticket
            </button>
        </div>
    </form>
</div>

<script>
    const dueInput = document.getElementById('due_at');
    const dueBox = document.getElementById('duePickerBox');
    const dueDisplay = document.getElementById('dueDateDisplay');

    function formatDate(value) {
        if (!value) {
            return 'Select due date';
        }

        const [year, month, day] = value.split('-');
        return `${day}/${month}/${year}`;
    }

    function updateDateDisplay() {
        dueDisplay.textContent = formatDate(dueInput.value);
        dueBox.classList.toggle('has-value', dueInput.value !== '');
    }

    dueBox?.addEventListener('click', () => {
        dueInput.showPicker?.();
    });

    dueInput?.addEventListener('change', updateDateDisplay);

    updateDateDisplay();
</script>
@endsection
