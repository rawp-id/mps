@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3">Calender Day</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('calender-days.create') }}" class="btn btn-primary">Create Calender Day</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Is Workday</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($calenderDays as $calenderDay)
                        <tr>
                            <td>{{ $calenderDay->id }}</td>
                            <td>{{ $calenderDay->date }}</td>
                            <td>{{ $calenderDay->is_workday ? 'Yes' : 'No' }}</td>
                            <td>{{ $calenderDay->description }}</td>
                            <td>
                                <a href="{{ route('calender-days.show', $calenderDay) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('calender-days.edit', $calenderDay) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('calender-days.destroy', $calenderDay) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this Calender Day?')" type="submit"
                                        class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No Calender Days found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
