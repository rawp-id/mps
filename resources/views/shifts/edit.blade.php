@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Edit Shift: {{ $shift->name }}</h2>
    <form action="{{ route('shifts.update',$shift) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Machine</label>
            <select name="machine_id" class="form-select" required>
                @foreach($machines as $machine)
                    <option value="{{ $machine->id }}" {{ $shift->machine_id == $machine->id ? 'selected' : '' }}>
                        {{ $machine->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Shift Name</label>
            <input type="text" name="name" class="form-control" value="{{ $shift->name }}" required>
        </div>
        <div class="mb-3">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" value="{{ $shift->start_time }}" required>
        </div>
        <div class="mb-3">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" value="{{ $shift->end_time }}" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" {{ $shift->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection