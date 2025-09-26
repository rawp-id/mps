@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Employee Details</h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Name:</dt>
                <dd class="col-sm-9">{{ $employee->name }}</dd>

                <dt class="col-sm-3">Email:</dt>
                <dd class="col-sm-9">{{ $employee->email ?? '-' }}</dd>

                <dt class="col-sm-3">Phone:</dt>
                <dd class="col-sm-9">{{ $employee->phone ?? '-' }}</dd>

                <dt class="col-sm-3">Position:</dt>
                <dd class="col-sm-9">{{ $employee->position ?? '-' }}</dd>

                <dt class="col-sm-3">Shifts:</dt>
                <dd class="col-sm-9">
                    @if($employee->employeeShifts->isEmpty())
                        <p class="mb-0">No shifts assigned.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($employee->employeeShifts as $shift)
                                <li>
                                    <strong>{{ $shift->shift->name }}</strong>
                                    @if($shift->role)
                                        - <em>{{ $shift->role }}</em>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </dd>
            </dl>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection