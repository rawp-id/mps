@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0">Machines</h1>
            <a href="{{ route('machines.create') }}" class="btn btn-primary">Create New Machine</a>
        </div>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Capacity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($machines as $machine)
                        <tr>
                            <td>{{ $machine->id }}</td>
                            <td>{{ $machine->name }}</td>
                            <td>{{ $machine->capacity }}</td>
                            <td>
                                <a href="{{ route('machines.show', $machine) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('machines.edit', $machine) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('machines.destroy', $machine) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this machine?')" type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
