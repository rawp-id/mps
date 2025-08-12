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

    {{-- <div class="mb-3">
        <label for="shipping_date" class="form-label">Shipping Date</label>
        <input type="datetime-local" class="form-control @error('shipping_date') is-invalid @enderror" name="shipping_date" value="{{ old('shipping_date') }}">
        @error('shipping_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div> --}}

    <div class="mb-3">
        <label for="process_details" class="form-label">Process Details</label>
        <input type="text" class="form-control @error('process_details') is-invalid @enderror" name="process_details" value="{{ old('process_details') }}">
        @error('process_details') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Components</label>
        <div id="components-list">
            <div class="row mb-2 component-row">
                <div class="col-md-5">
                    <select name="components[0][component_product_id]" class="form-select @error('components.0.component_product_id') is-invalid @enderror" required>
                        <option value="">Select Component Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('components.0.component_product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('components.0.component_product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" min="0" name="components[0][quantity]" class="form-control @error('components.0.quantity') is-invalid @enderror" placeholder="Quantity" value="{{ old('components.0.quantity', 1) }}" required>
                    @error('components.0.quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <input type="text" name="components[0][unit]" class="form-control @error('components.0.unit') is-invalid @enderror" placeholder="Unit" value="{{ old('components.0.unit', 'pcs') }}" required>
                    @error('components.0.unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-1 d-flex align-items-center">
                    <button type="button" class="btn btn-danger btn-sm remove-component" style="display:none;">&times;</button>
                </div>
            </div>
            @if(old('components'))
                @foreach(old('components') as $i => $component)
                    @if($i > 0)
                    <div class="row mb-2 component-row">
                        <div class="col-md-5">
                            <select name="components[{{ $i }}][component_product_id]" class="form-select @error('components.'.$i.'.component_product_id') is-invalid @enderror" required>
                                <option value="">Select Component Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('components.'.$i.'.component_product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} ({{ $product->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('components.'.$i.'.component_product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" min="0" name="components[{{ $i }}][quantity]" class="form-control @error('components.'.$i.'.quantity') is-invalid @enderror" placeholder="Quantity" value="{{ old('components.'.$i.'.quantity', 1) }}" required>
                            @error('components.'.$i.'.quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="components[{{ $i }}][unit]" class="form-control @error('components.'.$i.'.unit') is-invalid @enderror" placeholder="Unit" value="{{ old('components.'.$i.'.unit', 'pcs') }}" required>
                            @error('components.'.$i.'.unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <button type="button" class="btn btn-danger btn-sm remove-component">&times;</button>
                        </div>
                    </div>
                    @endif
                @endforeach
            @endif
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="add-component">Add Component</button>
    </div>

    @push('scripts')
    <script>
        let componentIndex = {{ old('components') ? count(old('components')) : 1 }};
        document.getElementById('add-component').addEventListener('click', function() {
            let row = document.querySelector('.component-row').cloneNode(true);
            row.querySelectorAll('select, input').forEach(function(input) {
                let name = input.getAttribute('name');
                if(name) {
                    name = name.replace(/\[\d+\]/, '[' + componentIndex + ']');
                    input.setAttribute('name', name);
                    input.value = input.type === 'number' ? 1 : (input.type === 'text' ? 'pcs' : '');
                    if(input.tagName === 'SELECT') input.selectedIndex = 0;
                    input.classList.remove('is-invalid');
                }
            });
            row.querySelector('.remove-component').style.display = '';
            document.getElementById('components-list').appendChild(row);
            componentIndex++;
        });

        document.getElementById('components-list').addEventListener('click', function(e) {
            if(e.target.classList.contains('remove-component')) {
                e.target.closest('.component-row').remove();
            }
        });
    </script>
    @endpush

    <button type="submit" class="btn btn-success">Save</button>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
