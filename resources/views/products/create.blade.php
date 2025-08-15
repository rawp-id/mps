@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
    <h1>Create New Product</h1>

    <form action="{{ route('products.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="text" class="form-control @error('code') is-invalid @enderror" name="code"
                value="{{ old('code') }}" required>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- <div class="mb-3">
        <label for="shipping_date" class="form-label">Shipping Date</label>
        <input type="datetime-local" class="form-control @error('shipping_date') is-invalid @enderror" name="shipping_date" value="{{ old('shipping_date') }}">
        @error('shipping_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div> --}}

        <div class="mb-3">
            <label for="process_details" class="form-label">Process Details</label>
            <input type="text" class="form-control @error('process_details') is-invalid @enderror" name="process_details"
                value="{{ old('process_details') }}">
            @error('process_details')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Main Components</label>
            <div id="main-components-list">
                <div class="row mb-2 main-component-row">
                    <div class="col-md-3">
                        <input type="text" class="form-control @error('main_components.0.code') is-invalid @enderror"
                            name="main_components[0][code]" placeholder="Code" value="{{ old('main_components.0.code') }}"
                            required>
                        @error('main_components.0.code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control @error('main_components.0.name') is-invalid @enderror"
                            name="main_components[0][name]" placeholder="Name" value="{{ old('main_components.0.name') }}"
                            required>
                        @error('main_components.0.name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" min="0" name="main_components[0][quantity]"
                            class="form-control @error('main_components.0.quantity') is-invalid @enderror"
                            placeholder="Quantity" value="{{ old('main_components.0.quantity', 1) }}" required>
                        @error('main_components.0.quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <select name="main_components[0][unit]"
                                class="form-select @error('main_components.0.unit') is-invalid @enderror" required>
                                <option value="">Select Unit</option>
                                <option value="pcs"
                                    {{ old('main_components.0.unit', 'pcs') == 'pcs' ? 'selected' : '' }}>pcs</option>
                                <option value="kg" {{ old('main_components.0.unit') == 'kg' ? 'selected' : '' }}>kg
                                </option>
                                <option value="liter" {{ old('main_components.0.unit') == 'liter' ? 'selected' : '' }}>
                                    liter</option>
                                <option value="meter" {{ old('main_components.0.unit') == 'meter' ? 'selected' : '' }}>
                                    meter</option>
                                <option value="other"
                                    {{ !in_array(old('main_components.0.unit'), ['pcs', 'kg', 'liter', 'meter']) && old('main_components.0.unit') ? 'selected' : '' }}>
                                    Other...</option>
                            </select>
                            <input type="text" name="main_components[0][unit_custom]" class="form-control"
                                placeholder="Type unit"
                                value="{{ !in_array(old('main_components.0.unit'), ['pcs', 'kg', 'liter', 'meter']) ? old('main_components.0.unit') : '' }}"
                                style="{{ !in_array(old('main_components.0.unit'), ['pcs', 'kg', 'liter', 'meter']) && old('main_components.0.unit') ? '' : 'display:none;' }}">
                        </div>
                        @error('main_components.0.unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-1 d-flex align-items-center">
                        <button type="button" class="btn btn-danger btn-sm remove-main-component"
                            style="display:none;">&times;</button>
                    </div>
                </div>
                @if (old('main_components'))
                    @foreach (old('main_components') as $i => $main)
                        @if ($i > 0)
                            <div class="row mb-2 main-component-row">
                                <div class="col-md-3">
                                    <input type="text"
                                        class="form-control @error('main_components.' . $i . '.code') is-invalid @enderror"
                                        name="main_components[{{ $i }}][code]" placeholder="Code"
                                        value="{{ old('main_components.' . $i . '.code') }}" required>
                                    @error('main_components.' . $i . '.code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <input type="text"
                                        class="form-control @error('main_components.' . $i . '.name') is-invalid @enderror"
                                        name="main_components[{{ $i }}][name]" placeholder="Name"
                                        value="{{ old('main_components.' . $i . '.name') }}" required>
                                    @error('main_components.' . $i . '.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" min="0"
                                        name="main_components[{{ $i }}][quantity]"
                                        class="form-control @error('main_components.' . $i . '.quantity') is-invalid @enderror"
                                        placeholder="Quantity" value="{{ old('main_components.' . $i . '.quantity', 1) }}"
                                        required>
                                    @error('main_components.' . $i . '.quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <select name="main_components[{{ $i }}][unit]"
                                            class="form-select @error('main_components.' . $i . '.unit') is-invalid @enderror"
                                            required>
                                            <option value="">Select Unit</option>
                                            <option value="pcs"
                                                {{ old('main_components.' . $i . '.unit', 'pcs') == 'pcs' ? 'selected' : '' }}>
                                                pcs</option>
                                            <option value="kg"
                                                {{ old('main_components.' . $i . '.unit') == 'kg' ? 'selected' : '' }}>kg
                                            </option>
                                            <option value="liter"
                                                {{ old('main_components.' . $i . '.unit') == 'liter' ? 'selected' : '' }}>
                                                liter</option>
                                            <option value="meter"
                                                {{ old('main_components.' . $i . '.unit') == 'meter' ? 'selected' : '' }}>
                                                meter</option>
                                            <option value="other"
                                                {{ !in_array(old('main_components.' . $i . '.unit'), ['pcs', 'kg', 'liter', 'meter']) && old('main_components.' . $i . '.unit') ? 'selected' : '' }}>
                                                Other...</option>
                                        </select>
                                        <input type="text" name="main_components[{{ $i }}][unit_custom]"
                                            class="form-control" placeholder="Type unit"
                                            value="{{ !in_array(old('main_components.' . $i . '.unit'), ['pcs', 'kg', 'liter', 'meter']) ? old('main_components.' . $i . '.unit') : '' }}"
                                            style="{{ !in_array(old('main_components.' . $i . '.unit'), ['pcs', 'kg', 'liter', 'meter']) && old('main_components.' . $i . '.unit') ? '' : 'display:none;' }}">
                                    </div>
                                    @error('main_components.' . $i . '.unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-1 d-flex align-items-center">
                                    <button type="button"
                                        class="btn btn-danger btn-sm remove-main-component">&times;</button>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
            <button type="button" class="btn btn-primary btn-sm mt-2" id="add-main-component">Add Main
                Component</button>
        </div>
        <script>
            let mainComponentIndex = {{ old('main_components') ? count(old('main_components')) : 1 }};
            document.getElementById('add-main-component').addEventListener('click', function() {
                let row = document.querySelector('.main-component-row').cloneNode(true);
                row.querySelectorAll('input, select').forEach(function(input) {
                    let name = input.getAttribute('name');
                    if (name) {
                        name = name.replace(/\[\d+\]/, '[' + mainComponentIndex + ']');
                        input.setAttribute('name', name);
                        if (input.type === 'number') input.value = 1;
                        else if (input.type === 'text') input.value = '';
                        if (input.tagName === 'SELECT') input.selectedIndex = 0;
                        input.classList.remove('is-invalid');
                    }
                });
                row.querySelector('.remove-main-component').style.display = '';
                document.getElementById('main-components-list').appendChild(row);
                mainComponentIndex++;
            });

            document.getElementById('main-components-list').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-main-component')) {
                    e.target.closest('.main-component-row').remove();
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('select[name^="main_components"][name$="[unit]"]').forEach(function(select) {
                    select.addEventListener('change', function() {
                        let input = this.parentElement.querySelector(
                            'input[name^="main_components"][name$="[unit_custom]"]');
                        if (this.value === 'other') {
                            input.style.display = '';
                            input.required = true;
                            input.value = '';
                        } else {
                            input.style.display = 'none';
                            input.required = false;
                            input.value = '';
                        }
                    });
                });
            });
        </script>

        {{-- <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <label class="form-label me-2">Component</label>
                <button type="button" class="btn btn-primary btn-sm" id="add-component">Add Component</button>
            </div>
            <div id="components-list">
                <div class="row mb-2 component-row">
                    <div class="col-md-5">
                        <select name="components[0][component_product_id]"
                            class="form-select @error('components.0.component_product_id') is-invalid @enderror" required>
                            <option value="">Select Component Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                    {{ old('components.0.component_product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('components.0.component_product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" min="0" name="components[0][quantity]"
                            class="form-control @error('components.0.quantity') is-invalid @enderror"
                            placeholder="Quantity" value="{{ old('components.0.quantity', 1) }}" required>
                        @error('components.0.quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <select name="components[0][unit]"
                                class="form-select @error('components.0.unit') is-invalid @enderror" required>
                                <option value="">Select Unit</option>
                                <option value="pcs" {{ old('components.0.unit', 'pcs') == 'pcs' ? 'selected' : '' }}>
                                    pcs
                                </option>
                                <option value="kg" {{ old('components.0.unit') == 'kg' ? 'selected' : '' }}>kg
                                </option>
                                <option value="liter" {{ old('components.0.unit') == 'liter' ? 'selected' : '' }}>liter
                                </option>
                                <option value="meter" {{ old('components.0.unit') == 'meter' ? 'selected' : '' }}>meter
                                </option>
                                <option value="other"
                                    {{ !in_array(old('components.0.unit'), ['pcs', 'kg', 'liter', 'meter']) && old('components.0.unit') ? 'selected' : '' }}>
                                    Other...</option>
                            </select>
                            <input type="text" name="components[0][unit_custom]" class="form-control"
                                placeholder="Type unit"
                                value="{{ !in_array(old('components.0.unit'), ['pcs', 'kg', 'liter', 'meter']) ? old('components.0.unit') : '' }}"
                                style="{{ !in_array(old('components.0.unit'), ['pcs', 'kg', 'liter', 'meter']) && old('components.0.unit') ? '' : 'display:none;' }}">
                        </div>
                        @error('components.0.unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            document.querySelectorAll('select[name="components[0][unit]"]').forEach(function(select) {
                                select.addEventListener('change', function() {
                                    let input = this.parentElement.querySelector(
                                        'input[name="components[0][unit_custom]"]');
                                    if (this.value === 'other') {
                                        input.style.display = '';
                                        input.required = true;
                                        input.value = '';
                                    } else {
                                        input.style.display = 'none';
                                        input.required = false;
                                        input.value = '';
                                    }
                                });
                            });
                        });
                    </script>
                    <div class="col-md-1 d-flex align-items-center">
                        <button type="button" class="btn btn-danger btn-sm remove-component"
                            style="display:none;">&times;</button>
                    </div>
                </div>
                @if (old('components'))
                    @foreach (old('components') as $i => $component)
                        @if ($i > 0)
                            <div class="row mb-2 component-row">
                                <div class="col-md-5">
                                    <select name="components[{{ $i }}][component_product_id]"
                                        class="form-select @error('components.' . $i . '.component_product_id') is-invalid @enderror"
                                        required>
                                        <option value="">Select Component Product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ old('components.' . $i . '.component_product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('components.' . $i . '.component_product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" min="0"
                                        name="components[{{ $i }}][quantity]"
                                        class="form-control @error('components.' . $i . '.quantity') is-invalid @enderror"
                                        placeholder="Quantity" value="{{ old('components.' . $i . '.quantity', 1) }}"
                                        required>
                                    @error('components.' . $i . '.quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <select name="components[{{ $i }}][unit]"
                                            class="form-select @error('components.' . $i . '.unit') is-invalid @enderror"
                                            required>
                                            <option value="">Select Unit</option>
                                            <option value="pcs"
                                                {{ old('components.' . $i . '.unit', 'pcs') == 'pcs' ? 'selected' : '' }}>
                                                pcs
                                            </option>
                                            <option value="kg"
                                                {{ old('components.' . $i . '.unit') == 'kg' ? 'selected' : '' }}>kg
                                            </option>
                                            <option value="liter"
                                                {{ old('components.' . $i . '.unit') == 'liter' ? 'selected' : '' }}>liter
                                            </option>
                                            <option value="meter"
                                                {{ old('components.' . $i . '.unit') == 'meter' ? 'selected' : '' }}>meter
                                            </option>
                                            <option value="other"
                                                {{ !in_array(old('components.' . $i . '.unit'), ['pcs', 'kg', 'liter', 'meter']) && old('components.' . $i . '.unit') ? 'selected' : '' }}>
                                                Other...</option>
                                        </select>
                                        <input type="text" name="components[{{ $i }}][unit_custom]"
                                            class="form-control" placeholder="Type unit"
                                            value="{{ !in_array(old('components.' . $i . '.unit'), ['pcs', 'kg', 'liter', 'meter']) ? old('components.' . $i . '.unit') : '' }}"
                                            style="{{ !in_array(old('components.' . $i . '.unit'), ['pcs', 'kg', 'liter', 'meter']) && old('components.' . $i . '.unit') ? '' : 'display:none;' }}">
                                    </div>
                                    @error('components.' . $i . '.unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        document.querySelectorAll('select[name="components[{{ $i }}][unit]"]').forEach(function(
                                            select) {
                                            select.addEventListener('change', function() {
                                                let input = this.parentElement.querySelector(
                                                    'input[name="components[{{ $i }}][unit_custom]"]');
                                                if (this.value === 'other') {
                                                    input.style.display = '';
                                                    input.required = true;
                                                    input.value = '';
                                                } else {
                                                    input.style.display = 'none';
                                                    input.required = false;
                                                    input.value = '';
                                                }
                                            });
                                        });
                                    });
                                </script>
                                <div class="col-md-1 d-flex align-items-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-component">&times;</button>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div> --}}

        <script>
            let componentIndex = {{ old('components') ? count(old('components')) : 1 }};
            document.getElementById('add-component').addEventListener('click', function() {
                let row = document.querySelector('.component-row').cloneNode(true);
                row.querySelectorAll('select, input').forEach(function(input) {
                    let name = input.getAttribute('name');
                    if (name) {
                        name = name.replace(/\[\d+\]/, '[' + componentIndex + ']');
                        input.setAttribute('name', name);
                        input.value = input.type === 'number' ? 1 : (input.type === 'text' ? 'pcs' : '');
                        if (input.tagName === 'SELECT') input.selectedIndex = 0;
                        input.classList.remove('is-invalid');
                    }
                });
                row.querySelector('.remove-component').style.display = '';
                document.getElementById('components-list').appendChild(row);
                componentIndex++;
            });

            document.getElementById('components-list').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-component')) {
                    e.target.closest('.component-row').remove();
                }
            });
        </script>

        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
