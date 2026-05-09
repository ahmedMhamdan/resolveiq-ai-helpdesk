@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Departments</h1>
        <p class="page-subtitle">Manage support departments used for ticket routing.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('departments.create') }}" class="btn btn-primary">
            + New Department
        </a>
    </div>
</div>

@if (session('error'))
    <div class="flash-message flash-error">
        {{ session('error') }}
    </div>
@endif

<div class="table-card">
    <div class="table-head">
        <div>
            <h2>Departments</h2>
            <p class="page-subtitle">All available support departments.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Description</th>
                    <th class="tickets-col">TICKETS</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($departments as $department)
                    <tr>
                        <td>
                            <strong>{{ $department->name }}</strong>
                        </td>

                        <td>
                            {{ $department->description ?? 'No description' }}
                        </td>

                        <td class="tickets-col">
                        <span class="badge open ticket-count-badge">
                            {{ $department->tickets_count }}
                        </span>
                        </td>

                        <td>
                            {{ $department->created_at->format('M d, Y') }}
                        </td>

                        <td>
                            <div class="row-actions">
                                <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-edit-soft">
                                    Edit
                                </a>

                                <form action="{{ route('departments.destroy', $department) }}" method="POST" onsubmit="return confirm('Delete this department?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger-soft">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No departments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $departments->links() }}
    </div>
</div>
@endsection
