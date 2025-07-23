@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Create Operation</h1>
    <form action="{{ route('operations.store') }}" method="POST" class="p-4 shadow-sm">
        @csrf

        <div class="mb-3">
            <label for="process_id" class="form-label">Process</label>
            <select name="process_id" id="process_id" class="form-select" required>
                @foreach($processes as $process)
                    <option value="{{ $process->id }}">{{ $process->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="machine_id" class="form-label">Machine</label>
            <select name="machine_id" id="machine_id" class="form-select" required>
                @foreach($machines as $machine)
                    <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="text" name="code" id="code" class="form-control">
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control">
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration</label>
            <input type="number" name="duration" id="duration" class="form-control" required min="0">
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('operations.index') }}" class="btn btn-secondary ms-2">Back</a>
    </form>
</div>
@endsection
