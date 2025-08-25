@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3">Downtime Records</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('machines.index') }}" class="btn btn-secondary">View Machines</a>
                <a href="{{ route('downtimes.create') }}" class="btn btn-primary">Create Downtime</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Machine Name</th>
                        <th>Start Date Time</th>
                        <th>End Date Time</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($downtimes as $downtime)
                        <tr>
                            <td>{{ $downtime->id }}</td>
                            <td>{{ $downtime->machine->name }}</td>
                            <td>{{ $downtime->start_datetime }}</td>
                            <td>{{ $downtime->end_datetime }}</td>
                            <td>{{ $downtime->reason }}</td>
                            <td>
                                <a href="{{ route('downtimes.show', $downtime) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('downtimes.edit', $downtime) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('downtimes.destroy', $downtime) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this Downtime?')" type="submit"
                                        class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No Downtimes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
