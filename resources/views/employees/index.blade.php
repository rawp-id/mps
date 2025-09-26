@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3">Employees</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('employees.create') }}" class="btn btn-primary">Add New Employee</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Position</th>
                        <th>Shift</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td>{{ $employee->id }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->phone }}</td>
                            <td>{{ $employee->position }}</td>
                            <td>
                                @foreach ($employee->employeeShifts as $shift)
                                <ul>
                                    <li>{{ $shift->shift->name }} ({{ $shift->shift->start_time }} - {{ $shift->shift->end_time }})</li>
                                </ul>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('employees.show', $employee) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this employee?')" type="submit"
                                        class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No Employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
