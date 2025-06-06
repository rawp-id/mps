@extends('layouts.app')

@section('title', 'Schedule Detail')

@section('content')
<h1>Schedule Detail #{{ $schedule->id }}</h1>

<table class="table table-bordered">
    <tr><th>Product</th><td>{{ $schedule->product->name ?? '-' }}</td></tr>
    <tr><th>Process</th><td>{{ $schedule->process->name ?? '-' }}</td></tr>
    <tr><th>Machine</th><td>{{ $schedule->machine->name ?? '-' }}</td></tr>
    <tr><th>Previous Schedule ID</th><td>{{ $schedule->previous_schedule_id ?? '-' }}</td></tr>
    <tr><th>Quantity</th><td>{{ $schedule->quantity }}</td></tr>
    <tr><th>Plan Speed</th><td>{{ $schedule->plan_speed }}</td></tr>
    <tr><th>Conversion Value</th><td>{{ $schedule->conversion_value }}</td></tr>
    <tr><th>Plan Duration</th><td>{{ $schedule->plan_duration }}</td></tr>
    <tr><th>Start Time</th><td>{{ $schedule->start_time }}</td></tr>
    <tr><th>End Time</th><td>{{ $schedule->end_time }}</td></tr>
</table>

<a href="{{ route('schedules.index') }}" class="btn btn-secondary">Back to List</a>
<a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-warning">Edit</a>
@endsection
