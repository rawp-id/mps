@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">BOM Details</h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Product:</dt>
                <dd class="col-sm-9">{{ $bom->product->name ?? '-' }}</dd>

                <dt class="col-sm-3">Component:</dt>
                <dd class="col-sm-9">{{ $bom->component->name ?? '-' }}</dd>

                <dt class="col-sm-3">Quantity:</dt>
                <dd class="col-sm-9">{{ $bom->quantity }}</dd>

                <dt class="col-sm-3">Unit:</dt>
                <dd class="col-sm-9">{{ $bom->unit }}</dd>

                <dt class="col-sm-3">Usage Type:</dt>
                <dd class="col-sm-9">{{ ucfirst($bom->usage_type) }}</dd>

                <dt class="col-sm-3">Cut Size Length:</dt>
                <dd class="col-sm-9">{{ $bom->cutsize_length ?? '-' }}</dd>

                <dt class="col-sm-3">Thickness:</dt>
                <dd class="col-sm-9">{{ $bom->thickness ?? '-' }}</dd>

                <dt class="col-sm-3">Quantity Plano:</dt>
                <dd class="col-sm-9">{{ $bom->qty_plano ?? '-' }}</dd>

                <dt class="col-sm-3">Quantity Image:</dt>
                <dd class="col-sm-9">{{ $bom->qty_image ?? '-' }}</dd>

                <dt class="col-sm-3">Quantity Tolerant:</dt>
                <dd class="col-sm-9">{{ $bom->qty_tolerant ?? '-' }}</dd>

                <dt class="col-sm-3">Quantity Waste:</dt>
                <dd class="col-sm-9">{{ $bom->qty_waste ?? '-' }}</dd>
            </dl>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('boms.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection