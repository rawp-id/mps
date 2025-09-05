@extends('layouts.app')

@section('content')
    <h1>Edit CO</h1>

    <form action="{{ route('co.update', $co->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- Untuk melakukan update -->

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $co->name) }}" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ old('description', $co->description) }}</textarea>
        </div>

        <div id="product_fields">
            @foreach ($co->coProducts as $key => $coProduct)
                <div class="form-group" id="product_{{ $key }}">
                    <label for="co_products">CO Products</label>
                    <select name="co_products[]" class="form-control" required>
                        <option value="">Select a product</option>
                        @foreach ($products as $availableProduct)
                            <option value="{{ $availableProduct->id }}" @if ($coProduct->product_id == $availableProduct->id) selected @endif>
                                {{ $availableProduct->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="shipment_date_{{ $key }}">Shipment Date</label>
                    <input type="date" name="shipment_dates[]" class="form-control"
                        value="{{ old('shipment_dates.' . $key, $coProduct->shipment_date) }}" required>

                    <!-- Button to remove product -->
                    <button type="button" class="btn btn-danger mt-2 removeProduct" data-id="product_{{ $key }}">Remove</button>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="button" id="addProduct" class="btn btn-secondary">Tambah Product</button>
        </div>

        <button type="submit" class="btn btn-primary">Update CO</button>
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
                        <select name="co_products[]" class="form-control" required>
                            <option value="">Select a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <label for="shipment_date_${productFieldId}">Shipment Date</label>
                        <input type="date" name="shipment_dates[]" class="form-control" required>
                        
                        <!-- Button to remove product -->
                        <button type="button" class="btn btn-danger mt-2 removeProduct" data-id="${productFieldId}">Remove</button>
                    </div>
                `;
                $('#product_fields').append(productField);
            });

            // Use event delegation for dynamically added remove buttons
            $(document).on('click', '.removeProduct', function() {
                var productId = $(this).data('id'); // Get the unique ID of the product field
                $('#' + productId).remove(); // Remove the selected product field
            });
        });
    </script>
@endsection
