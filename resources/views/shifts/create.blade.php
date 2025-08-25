@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Add Shift</h2>
    <form action="{{ route('shifts.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Machine</label>
            <select name="machine_id" class="form-select" required>
                @foreach($machines as $machine)
                    <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Shift Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
