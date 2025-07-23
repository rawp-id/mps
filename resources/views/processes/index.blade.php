@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Processes</h1>
            <a href="{{ route('processes.create') }}" class="btn btn-primary">+ Add New Process</a>
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
                        <th>Code</th>
                        <th>Name</th>
                        <th>Speed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($processes as $process)
                        <tr>
                            <td>{{ $process->id }}</td>
                            <td>{{ $process->code }}</td>
                            <td>{{ $process->name }}</td>
                            <td>{{ $process->speed }}</td>
                            <td>
                                <a href="{{ route('processes.show', $process) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('processes.edit', $process) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('processes.destroy', $process) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this process?')" type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
