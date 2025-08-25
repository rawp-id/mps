@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Edit Overtime</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('overtimes.update', $overtime->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="machine_id" class="form-label">Machine</label>
                                <select name="machine_id" id="machine_id" class="form-select" required>
                                    <option value="">Select a machine</option>
                                    @foreach ($machines as $machine)
                                        <option value="{{ $machine->id }}" {{ $overtime->machine_id == $machine->id ? 'selected' : '' }}>
                                            {{ $machine->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('machine_id'))
                                    <div class="text-danger">{{ $errors->first('machine_id') }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $overtime->date) }}" required>
                                @if ($errors->has('date'))
                                    <div class="text-danger">{{ $errors->first('date') }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time', $overtime->start_time) }}" required>
                                @if ($errors->has('start_time'))
                                    <div class="text-danger">{{ $errors->first('start_time') }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" name="end_time" id="end_time" class="form-control" value="{{ old('end_time', $overtime->end_time) }}" required>
                                @if ($errors->has('end_time'))
                                    <div class="text-danger">{{ $errors->first('end_time') }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea name="reason" id="reason" class="form-control" rows="3" required>{{ old('reason', $overtime->reason) }}</textarea>
                                @if ($errors->has('reason'))
                                    <div class="text-danger">{{ $errors->first('reason') }}</div>
                                @endif
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('overtimes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
