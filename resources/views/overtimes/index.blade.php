@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3">Overtime Records</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('machines.index') }}" class="btn btn-secondary">View Machines</a>
                <a href="{{ route('overtimes.create') }}" class="btn btn-primary">Create Overtime</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Machine Name</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($overtimes as $overtime)
                        <tr>
                            <td>{{ $overtime->id }}</td>
                            <td>{{ $overtime->machine->name }}</td>
                            <td>{{ $overtime->date }}</td>
                            <td>{{ $overtime->start_time }}</td>
                            <td>{{ $overtime->end_time }}</td>
                            <td>{{ $overtime->reason }}</td>
                            <td>
                                <a href="{{ route('overtimes.show', $overtime) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('overtimes.edit', $overtime) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('overtimes.destroy', $overtime) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this Overtime?')" type="submit"
                                        class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No Overtimes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
