@extends('layouts.app')

@section('title', 'Schedules List')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h1>Schedules</h1>
        <a href="{{ route('schedules.create') }}" class="btn btn-primary">+ Schedule</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Process</th>
                <th>Machine</th>
                <th>Quantity</th>
                <th>Plan Speed</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $schedule)
                <tr>
                    <td>{{ $schedule->id }}</td>
                    <td>{{ $schedule->product->name ?? '-' }}</td>
                    <td>{{ $schedule->process->name ?? 'Process ' . $schedule->process_id }}</td>
                    <td>{{ $schedule->machine->name ?? 'Machine ' . $schedule->machine_id }}</td>
                    <td>{{ $schedule->quantity }}</td>
                    <td>{{ $schedule->plan_speed }}</td>
                    <td>{{ $schedule->start_time }}</td>
                    <td>{{ $schedule->end_time }}</td>
                    <td>
                        <div class="d-flex flex-row gap-2 align-items-center">
                            <button class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Delay</button>
                            <a href="{{ route('schedules.show', $schedule) }}" class="btn btn-sm btn-info w-100">View</a>
                            <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-sm btn-warning w-100">Edit</a>
                            <form action="{{ route('schedules.destroy', $schedule) }}" method="POST"
                                style="display:inline-block" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger w-100">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <!-- Modal -->
                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Modal title</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('schedules.delay', $schedule) }}" method="POST">
                                    @csrf
                                    {{-- <input type="hidden" name="schedule_id" value="{{ $schedule->id }}"> --}}
                                    {{-- <div class="mb-3">
                                        <label for="delay_reason" class="form-label">Delay Reason</label>
                                        <input type="text" class="form-control" id="delay_reason" name="delay_reason"
                                            required>
                                    </div> --}}
                                    <div class="mb-3">
                                        <label for="delay_minutes" class="form-label">Delay Duration (minutes)</label>
                                        <input type="number" class="form-control" id="delay_minutes"
                                            name="delay_minutes" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Delay</button>
                                </form>
                            </div>
                            {{-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Understood</button>
                            </div> --}}
                        </div>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No schedules found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $schedules->links() }}
    </div>
@endsection
