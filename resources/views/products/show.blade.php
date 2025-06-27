@extends('layouts.app')

@section('title', 'View Product')

@section('content')
<h1>Product Details</h1>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ $product->name }}</h5>
        <p class="card-text"><strong>Code:</strong> {{ $product->code }}</p>
        <p class="card-text"><strong>Shipping Date:</strong> {{ $product->shipping_date ?? '-' }}</p>
    </div>
</div>

<a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Back</a>
@endsection
