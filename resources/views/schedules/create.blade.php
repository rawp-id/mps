@extends('layouts.app')

@section('title', 'Add Schedule')

@section('content')
<h1>Add New Schedule</h1>

<form action="{{ route('calender.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="product_id" class="form-label">Product</label>
        <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
            <option value="">-- Select Product --</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected':'' }}>{{ $product->name }}</option>
            @endforeach
        </select>
        @error('product_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="process_id" class="form-label">Process</label>
        <select name="process_id" id="process_id" class="form-select @error('process_id') is-invalid @enderror" required>
            <option value="">-- Select Process --</option>
            @foreach ($processes as $process)
                <option value="{{ $process->id }}" {{ old('process_id') == $process->id ? 'selected':'' }}>{{ $process->name }}</option>
            @endforeach
        </select>
        @error('process_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="machine_id" class="form-label">Machine</label>
        <select name="machine_id" id="machine_id" class="form-select @error('machine_id') is-invalid @enderror" required>
            <option value="">-- Select Machine --</option>
            @foreach ($machines as $machine)
                <option value="{{ $machine->id }}" {{ old('machine_id') == $machine->id ? 'selected':'' }}>{{ $machine->name }}</option>
            @endforeach
        </select>
        @error('machine_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="previous_schedule_id" class="form-label">Previous Schedule (optional)</label>
        <input type="number" class="form-control @error('previous_schedule_id') is-invalid @enderror" name="previous_schedule_id" id="previous_schedule_id" value="{{ old('previous_schedule_id') }}">
        @error('previous_schedule_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="quantity" class="form-label">Quantity</label>
        <input type="number" min="1" class="form-control @error('quantity') is-invalid @enderror" name="quantity" id="quantity" value="{{ old('quantity', 8000) }}" required>
        @error('quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="plan_speed" class="form-label">Plan Speed</label>
        <input type="number" min="1" class="form-control @error('plan_speed') is-invalid @enderror" name="plan_speed" id="plan_speed" value="{{ old('plan_speed') }}" required>
        @error('plan_speed')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="conversion_value" class="form-label">Conversion Value</label>
        <input type="text" class="form-control @error('conversion_value') is-invalid @enderror" name="conversion_value" id="conversion_value" value="{{ old('conversion_value') }}" required>
        @error('conversion_value')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="plan_duration" class="form-label">Plan Duration (minutes)</label>
        <input type="number" min="0" class="form-control @error('plan_duration') is-invalid @enderror" name="plan_duration" id="plan_duration" value="{{ old('plan_duration') }}" required>
        @error('plan_duration')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="start_time" class="form-label">Start Time</label>
        <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" name="start_time" id="start_time" value="{{ old('start_time') }}" required>
        @error('start_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="end_time" class="form-label">End Time</label>
        <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" name="end_time" id="end_time" value="{{ old('end_time') }}" required>
        @error('end_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-success">Save</button>
    <a href="{{ route('calender.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
