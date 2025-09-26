@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Edit BOM</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('boms.update', $bom->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <select name="product_id" id="product_name" class="form-select" required>
                                    <option value="">Select a product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ $bom->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="component_name" class="form-label">Component Name</label>
                                <select name="component_id" id="component_name" class="form-select" required>
                                    <option value="">Select a component</option>
                                    @foreach ($components as $component)
                                        <option value="{{ $component->id }}" {{ $bom->component_id == $component->id ? 'selected' : '' }}>
                                            {{ $component->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">BOM Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required step="1" pattern="\d*" value="{{ old('quantity', $bom->quantity) }}">
                            </div>
                            <div class="mb-3">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" required value="{{ old('unit', $bom->unit) }}">
                            </div>
                            <div class="mb-3">
                                <label for="usage_type" class="form-label">Usage Type</label>
                                <select name="usage_type" id="usage_type" class="form-select" required>
                                    <option value="">Select usage type</option>
                                    <option value="consumable" {{ $bom->usage_type == 'consumable' ? 'selected' : '' }}>Consumable</option>
                                    <option value="usage_based" {{ $bom->usage_type == 'usage_based' ? 'selected' : '' }}>Usage Based</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="cutsize_length" class="form-label">Cut Size Length</label>
                                <input type="number" class="form-control" id="cutsize_length" name="cutsize_length" step="1" pattern="\d*" value="{{ old('cutsize_length', $bom->cutsize_length) }}">
                            </div>
                            <div class="mb-3">
                                <label for="thickness" class="form-label">Thickness</label>
                                <input type="number" class="form-control" id="thickness" name="thickness" step="1" pattern="\d*" value="{{ old('thickness', $bom->thickness) }}">
                            </div>
                            <div class="mb-3">
                                <label for="qty_plano">Quantity Plano</label>
                                <input type="number" class="form-control" id="qty_plano" name="qty_plano" step="1" pattern="\d*" value="{{ old('qty_plano', $bom->qty_plano) }}">
                            </div>
                            <div class="mb-3">
                                <label for="qty_image">Quantity Image</label>
                                <input type="number" class="form-control" id="qty_image" name="qty_image" step="1" pattern="\d*" value="{{ old('qty_image', $bom->qty_image) }}">
                            </div>
                            <div class="mb-3">
                                <label for="qty_tolerant">Quantity Tolerant</label>
                                <input type="number" class="form-control" id="qty_tolerant" name="qty_tolerant" step="1" pattern="\d*" value="{{ old('qty_tolerant', $bom->qty_tolerant) }}">
                            </div>
                            <div class="mb-3">
                                <label for="qty_waste">Quantity Waste</label>
                                <input type="number" class="form-control" id="qty_waste" name="qty_waste" step="1" pattern="\d*" value="{{ old('qty_waste', $bom->qty_waste) }}">
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('boms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
