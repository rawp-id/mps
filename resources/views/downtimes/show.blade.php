@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Downtime Details</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Machine Name:</dt>
                    <dd class="col-sm-9">{{ $downtime->machine->name ?? '-' }}</dd>

                    <dt class="col-sm-3">Start Date & Time:</dt>
                    <dd class="col-sm-9">{{ $downtime->start_datetime ?? '-' }}</dd>

                    <dt class="col-sm-3">End Date & Time:</dt>
                    <dd class="col-sm-9">{{ $downtime->end_datetime ?? '-' }}</dd>

                    <dt class="col-sm-3">Reason:</dt>
                    <dd class="col-sm-9">{{ $downtime->reason ?? '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('downtimes.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
