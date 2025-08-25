@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Overtime Details</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Machine Name:</dt>
                    <dd class="col-sm-9">{{ $overtime->machine->name ?? '-' }}</dd>

                    <dt class="col-sm-3">Date:</dt>
                    <dd class="col-sm-9">{{ $overtime->date ?? '-' }}</dd>

                    <dt class="col-sm-3">Start Time:</dt>
                    <dd class="col-sm-9">{{ $overtime->start_time ?? '-' }}</dd>

                    <dt class="col-sm-3">End Time:</dt>
                    <dd class="col-sm-9">{{ $overtime->end_time ?? '-' }}</dd>
                    
                    <dt class="col-sm-3">Reason:</dt>
                    <dd class="col-sm-9">{{ $overtime->reason ?? '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('overtimes.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
