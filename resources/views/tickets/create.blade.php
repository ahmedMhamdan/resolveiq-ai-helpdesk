@extends('layouts.app')

@section('title', 'New Ticket')

@section('content')
<div class="page-head">
    <div>
        <h1>New Ticket</h1>
        <p>Create a new support request and assign it to the right department.</p>
    </div>

    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
        Back to Tickets
    </a>
</div>

<div class="table-card ticket-create-card">
    <div class="card-head">
        <div>
            <h2>Create Ticket</h2>
            <p>Fill in the ticket details below.</p>
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
                <label for="title">Ticket Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    placeholder="Example: Unable to login to account"
                    required
                >
            </div>

            <div class="form-group">
                <label for="department_id">Department</label>
                <select id="department_id" name="department_id" required>
                    <option value="">Select department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
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
                        <option value="{{ $agent->id }}" @selected(old('agent_id') == $agent->id)>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" required>
                    <option value="low" @selected(old('priority') === 'low')>Low</option>
                    <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                    <option value="high" @selected(old('priority') === 'high')>High</option>
                    <option value="urgent" @selected(old('priority') === 'urgent')>Urgent</option>
                </select>
            </div>

          <div class="form-group">
        <label for="due_at">Due Date</label>

        <div class="date-picker-box" id="duePickerBox">
            <input
                type="date"
                id="due_at"
                name="due_at"
                value="{{ old('due_at') }}"
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
            </div>

            <div class="form-group full">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="7"
                    placeholder="Describe the issue clearly..."
                    required
                >{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="form-actions create-actions">
            <a href="{{ route('tickets.index') }}" class="btn btn-danger-soft">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Create Ticket
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
        if (dueInput.showPicker) {
            dueInput.showPicker();
        } else {
            dueInput.focus();
        }
    });

    dueInput?.addEventListener('change', updateDateDisplay);

    updateDateDisplay();
</script>
@endsection
