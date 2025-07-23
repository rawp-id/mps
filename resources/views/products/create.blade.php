@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
<h1>Create New Product</h1>

<form action="{{ route('products.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="code" class="form-label">Code</label>
        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="shipping_date" class="form-label">Shipping Date</label>
        <input type="datetime-local" class="form-control @error('shipping_date') is-invalid @enderror" name="shipping_date" value="{{ old('shipping_date') }}">
        @error('shipping_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="process_details" class="form-label">Process Details</label>
        <input type="text" class="form-control @error('process_details') is-invalid @enderror" name="process_details" value="{{ old('process_details') }}">
        @error('process_details') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn btn-success">Save</button>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
