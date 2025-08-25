@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Shifts</h2>
    <a href="{{ route('shifts.create') }}" class="btn btn-primary mb-3">Add Shift</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Machine</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $shift)
            <tr>
                <td>{{ $shift->name }}</td>
                <td>{{ $shift->machine->name }}</td>
                <td>{{ $shift->start_time }}</td>
                <td>{{ $shift->end_time }}</td>
                <td>{{ $shift->is_active ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('shifts.edit',$shift) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('shifts.destroy',$shift) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
