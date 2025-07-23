@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Operations</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('machines.index') }}" class="btn btn-secondary">View Machines</a>
            <a href="{{ route('processes.index') }}" class="btn btn-secondary">View Processes</a>
            <a href="{{ route('operations.create') }}" class="btn btn-primary">Add New Operation</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    {{-- <th>Code</th> --}}
                    <th>Name</th>
                    <th>Process</th>
                    <th>Machine</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($operations as $operation)
                    <tr>
                        <td>{{ $operation->id }}</td>
                        {{-- <td>{{ $operation->code }}</td> --}}
                        <td>{{ $operation->name }}</td>
                        <td>{{ $operation->process->name }}</td>
                        <td>{{ $operation->machine->name }}</td>
                        <td>{{ $operation->duration }}</td>
                        <td>
                            <a href="{{ route('operations.show', $operation) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('operations.edit', $operation) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('operations.destroy', $operation) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Delete this operation?')" type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
