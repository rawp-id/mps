@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Operation</h1>

        <form action="{{ route('operations.update', $operation) }}" method="POST" class="card p-4 shadow-sm">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Process:</label>
                <select name="process_id" class="form-select" required>
                    @foreach($processes as $process)
                        <option value="{{ $process->id }}" {{ $operation->process_id == $process->id ? 'selected' : '' }}>
                            {{ $process->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Machine:</label>
                <select name="machine_id" class="form-select" required>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}" {{ $operation->machine_id == $machine->id ? 'selected' : '' }}>
                            {{ $machine->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Code:</label>
                <input type="text" name="code" class="form-control" value="{{ $operation->code }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Name:</label>
                <input type="text" name="name" class="form-control" value="{{ $operation->name }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Duration:</label>
                <input type="number" name="duration" class="form-control" value="{{ $operation->duration }}" required min="0">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('operations.index') }}" class="btn btn-secondary ms-2">Back</a>
        </form>
    </div>
@endsection
