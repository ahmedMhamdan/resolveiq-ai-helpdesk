@extends('layouts.app')

@section('title', __('departments.title'))

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ __('departments.title') }}</h1>
        <p class="page-subtitle">{{ __('departments.subtitle') }}</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('departments.create') }}" class="btn btn-primary">
            {{ __('departments.new_department') }}
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
            <h2>{{ __('departments.table_heading') }}</h2>
            <p class="page-subtitle">{{ __('departments.table_subtitle') }}</p>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('departments.department_th') }}</th>
                    <th>{{ __('departments.description_th') }}</th>
                    <th class="tickets-col">{{ __('departments.tickets_th') }}</th>
                    <th>{{ __('departments.created_th') }}</th>
                    <th>{{ __('departments.actions_th') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($departments as $department)
                    <tr>
                        <td>
                            <strong>{{ $department->name }}</strong>
                        </td>

                        <td>
                            {{ $department->description ?? __('departments.no_description') }}
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
                                    {{ __('departments.edit_btn') }}
                                </a>

                                <form action="{{ route('departments.destroy', $department) }}" method="POST" onsubmit="return confirm('{{ __('departments.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger-soft">
                                        {{ __('departments.delete_btn') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">{{ __('departments.no_departments') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $departments->links('vendor.pagination.resolveiq') }}
    </div>
</div>
@endsection