@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Create New Downtime</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('downtimes.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="machine_id" class="form-label">Machine</label>
                                <select name="machine_id" id="machine_id" class="form-select" required>
                                    <option value="">Select a machine</option>
                                    @foreach ($machines as $machine)
                                        <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('machine_id'))
                                    <div class="text-danger">{{ $errors->first('machine_id') }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="start_datetime" class="form-label">Start Date & Time</label>
                                <input type="datetime-local" name="start_datetime" id="start_datetime" class="form-control" required>
                                @if ($errors->has('start_datetime'))
                                    <div class="text-danger">{{ $errors->first('start_datetime') }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="end_datetime" class="form-label">End Date & Time</label>
                                <input type="datetime-local" name="end_datetime" id="end_datetime" class="form-control" required>
                                @if ($errors->has('end_datetime'))
                                    <div class="text-danger">{{ $errors->first('end_datetime') }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                                @if ($errors->has('reason'))
                                    <div class="text-danger">{{ $errors->first('reason') }}</div>
                                @endif
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('downtimes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
