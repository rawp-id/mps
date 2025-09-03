@extends('layouts.app')

@section('head')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <h2>Edit CO</h2>

    <form action="{{ route('co.update', $co->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $co->name }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" required>{{ $co->description }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label for="co_products">CO Products</label>
            <select name="co_products[]" id="co_products" class="form-control select2" multiple required
                data-placeholder="Select CO Products">
                @php
                    $selectedProducts = old('co_products', $co->coProducts->pluck('product_id')->toArray());
                @endphp

                @foreach ($products as $product)
                    <option value="{{ $product->id }}" {{ in_array($product->id, $selectedProducts) ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('co.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#co_products').select2({
                placeholder: $('#co_products').data('placeholder'),
                allowClear: true
            });
        });
    </script>
@endsection
