@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Component Details</h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Code:</dt>
                <dd class="col-sm-9">{{ $component->code }}</dd>

                <dt class="col-sm-3">Name:</dt>
                <dd class="col-sm-9">{{ $component->name }}</dd>

                <dt class="col-sm-3">Description:</dt>
                <dd class="col-sm-9">{{ $component->description }}</dd>

                <dt class="col-sm-3">Unit:</dt>
                <dd class="col-sm-9">{{ $component->unit }}</dd>

                <dt class="col-sm-3">Stock:</dt>
                <dd class="col-sm-9">{{ $component->stock }}</dd>
            </dl>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('components.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
