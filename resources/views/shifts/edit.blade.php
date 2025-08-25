@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Edit Shift: {{ $shift->name }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('shifts.update', $shift) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="machine_id" class="form-label">Machine</label>
                                <select name="machine_id" id="machine_id" class="form-select" required>
                                    @foreach ($machines as $machine)
                                        <option value="{{ $machine->id }}" {{ $shift->machine_id == $machine->id ? 'selected' : '' }}>
                                            {{ $machine->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Shift Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $shift->name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time', $shift->start_time) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" name="end_time" id="end_time" class="form-control" value="{{ old('end_time', $shift->end_time) }}" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                                    {{ old('is_active', $shift->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
