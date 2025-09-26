@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Product Status Details</h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Code:</dt>
                <dd class="col-sm-9">{{ $productStatus->code ?? '-' }}</dd>

                <dt class="col-sm-3">Description:</dt>
                <dd class="col-sm-9">{{ $productStatus->description ?? '-' }}</dd>
            </dl>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('product-status.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection