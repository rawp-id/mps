@extends('layouts.app')

@section('title', 'Report Details')

@section('content')
    <div class="container">
        {{-- <h1>Report Details</h1>
        <p>Here you can view the details of the selected report.</p> --}}

        <h2>Product Information - Report Production</h2>
        <p><strong>Name:</strong> {{ $product->name }}</p>
        <p><strong>Category:</strong> {{ $product->category->name ?? '-' }}</p>
        <p><strong>SKU:</strong> {{ $product->sku ?? '-' }}</p>

        <div class="accordion" id="scheduleAccordion">
            @foreach($schedules as $index => $schedule)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $index }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                            {{ $schedule->operation->process->name }} - {{ $schedule->operation->machine->name }}
                        </button>
                    </h2>
                    <div id="collapse{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $index }}" data-bs-parent="#scheduleAccordion">
                        <div class="accordion-body">
                            <div class="mb-3">
                                <label for="process{{ $index }}" class="form-label"><strong>Process:</strong></label>
                                <input type="text" class="form-control" id="process{{ $index }}" value="{{ $schedule->operation->process->name }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="machine{{ $index }}" class="form-label"><strong>Machine:</strong></label>
                                <select class="form-select" id="machine{{ $index }}" name="machine_id">
                                    @foreach($machines as $machine)
                                        <option value="{{ $machine->id }}" {{ $machine->id == $schedule->operation->machine->id ? 'selected' : '' }}>
                                            {{ $machine->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="startTime{{ $index }}" class="form-label"><strong>Start Time:</strong></label>
                                <input type="datetime-local" class="form-control" id="startTime{{ $index }}" value="{{ $schedule->start_time }}">
                            </div>
                            <div class="mb-3">
                                <label for="endTime{{ $index }}" class="form-label"><strong>End Time:</strong></label>
                                <input type="datetime-local" class="form-control" id="endTime{{ $index }}" value="{{ $schedule->end_time }}">
                            </div>
                            <form action="" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="inputDescription{{ $index }}" class="form-label"><strong>Input Description:</strong></label>
                                    <textarea class="form-control" id="inputDescription{{ $index }}" name="input_description" rows="3">{{ $schedule->input_description }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Update Description</button>
                            </form>
                            <p>
                                <strong>Is Completed:</strong> {{ $schedule->is_completed ? 'Yes' : 'No' }}
                                @if(!$schedule->is_completed)
                                    <form action="" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Mark as Completed</button>
                                    </form>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection