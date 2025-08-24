@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Create New BOM</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('boms.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <select name="product_id" id="product_name" class="form-select" required>
                                    <option value="">Select a product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="component_name" class="form-label">Component Name</label>
                                <select name="component_id" id="component_name" class="form-select" required>
                                    <option value="">Select a component</option>
                                    @foreach ($components as $component)
                                        <option value="{{ $component->id }}">{{ $component->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">BOM Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required step="1" pattern="\d*">
                            </div>
                            <div class="mb-3">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" required>
                            </div>
                            <div class="mb-3">
                                <label for="usage_type" class="form-label">Usage Type</label>
                                <select name="usage_type" id="usage_type" class="form-select" required>
                                    <option value="">Select usage type</option>
                                    <option value="consumable">Consumable</option>
                                    <option value="usage_based">Usage Based</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('boms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
