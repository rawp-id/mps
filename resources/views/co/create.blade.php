@extends('layouts.app')

@section('content')
    <h1>Create New CO</h1>

    <form action="{{ route('co.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
        </div>
        <div id="product_fields">
            <!-- Dynamic product and shipment date inputs will be added here -->
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="button" id="addProduct" class="btn btn-secondary">Tambah Product</button>
        </div>

        <button type="submit" class="btn btn-primary">Create CO</button>
    </form>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add product input field dynamically
            $('#addProduct').on('click', function() {
                var productFieldId = 'product_' + new Date().getTime(); // Unique ID based on timestamp
                var productField = `
                    <div class="form-group" id="${productFieldId}">
                        <label for="co_products">CO Products</label>
                        <select name="co_products[]" id="co_products_${productFieldId}" class="form-control" required>
                            <option value="">Select a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <label for="shipment_date_${productFieldId}">Shipment Date</label>
                        <input type="date" name="shipment_dates[]" id="shipment_date_${productFieldId}" class="form-control" required>
                    </div>
                `;
                $('#product_fields').append(productField);
            });
        });
    </script>
@endsection
