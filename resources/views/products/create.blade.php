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

        <div class="mb-3">
            <label for="process_details" class="form-label">Process Details</label>
            <input type="text" class="form-control @error('process_details') is-invalid @enderror" name="process_details"
                value="{{ old('process_details') }}">
            @error('process_details')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Components</label>
            <div id="main-components-list">
                <div class="row mb-2 main-component-row">
                    <div class="col-md-4">
                        <input type="text" class="form-control @error('main_components.0.name') is-invalid @enderror"
                            name="main_components[0][name]" placeholder="Name" value="{{ old('main_components.0.name') }}"
                            required>
                        @error('main_components.0.name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" min="0" name="main_components[0][quantity]"
                            class="form-control @error('main_components.0.quantity') is-invalid @enderror"
                            placeholder="Quantity" value="{{ old('main_components.0.quantity', 1) }}" required>
                        @error('main_components.0.quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
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

            // Menambahkan event listener untuk tombol 'Add Main Component'
            document.getElementById('add-main-component').addEventListener('click', function() {
                let row = document.querySelector('.main-component-row').cloneNode(true);

                // Update nama dan nilai input
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

                // Tampilkan tombol hapus di baris baru
                row.querySelector('.remove-main-component').style.display = '';
                document.getElementById('main-components-list').appendChild(row);

                // Panggil fungsi untuk memastikan unit_custom disembunyikan atau ditampilkan
                toggleUnitCustomVisibility(row.querySelector('select[name*="unit"]'));

                // Daftarkan event listener untuk unit baru di baris yang baru
                row.querySelector('select[name*="unit"]').addEventListener('change', function() {
                    toggleUnitCustomVisibility(this);
                });

                mainComponentIndex++;
            });

            // Fungsi untuk menghapus komponen yang dipilih
            document.getElementById('main-components-list').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-main-component')) {
                    e.target.closest('.main-component-row').remove();
                }
            });

            // Menangani perubahan unit pada baris pertama dan setelahnya
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('select[name^="main_components"][name$="[unit]"]').forEach(function(select) {
                    select.addEventListener('change', function() {
                        toggleUnitCustomVisibility(this);
                    });
                });
            });

            // Fungsi untuk menyembunyikan atau menampilkan unit_custom berdasarkan pilihan
            function toggleUnitCustomVisibility(selectElement) {
                let inputCustom = selectElement.parentElement.querySelector('input[name$="[unit_custom]"]');
                if (selectElement.value === 'other') {
                    inputCustom.style.display = '';
                    inputCustom.required = true;
                } else {
                    inputCustom.style.display = 'none';
                    inputCustom.required = false;
                }
            }
        </script>
        
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
