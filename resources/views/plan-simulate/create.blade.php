@extends('layouts.app')

@section('title', 'Create Plan Simulation')

@section('content')
<h1>Create Plan Simulation</h1>

<form action="{{ route('plan-simulate.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">Plan Name</label>
        <input type="text" name="name" id="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="product_id" class="form-label">Product</label>
        <select name="product_id" id="product_id"
            class="form-select @error('product_id') is-invalid @enderror" required>
            <option value="">-- Select Product --</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}"
                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
        @error('product_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description (Optional)</label>
        <textarea name="description" id="description"
            class="form-control @error('description') is-invalid @enderror"
            rows="3">{{ old('description') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <p class="text-muted">* Jadwal akan disimulasikan otomatis saat plan dibuat berdasarkan konfigurasi default.</p>

    <button type="submit" class="btn btn-success">Create Plan & Simulate</button>
    <a href="{{ route('plan-simulate.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
