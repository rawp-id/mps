@extends('layouts.app')

@section('title', 'Create Plan Simulation')

@section('content')
    <h1>Create Plan Simulation</h1>

    <form action="{{ route('plan-simulate.store') }}" method="POST">
        @csrf

        {{-- <div class="mb-3">
        <label for="name" class="form-label">Plan Name</label>
        <input type="text" name="name" id="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div> --}}
        {{-- <div class="mb-3">
            <label for="plan_id" class="form-label">Select Plan</label>
            <select name="plan_id" id="plan_id" class="form-select @error('plan_id') is-invalid @enderror">
                <option value="">-- Create New Plan --</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }}
                    </option>
                @endforeach
            </select>
            @error('plan_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div> --}}
        {{-- <div class="mb-3" id="new_plan_name_container" style="{{ old('plan_id') ? 'display:none;' : '' }}">
            <label for="new_plan_name" class="form-label">Plan Name</label>
            <input type="text" name="new_plan_name" id="new_plan_name"
                class="form-control @error('new_plan_name') is-invalid @enderror"
                value="{{ old('new_plan_name', 'Plan-' . now()->format('Ymd-His')) }}">
            @error('new_plan_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Isi nama plan jika membuat baru. Kosongkan untuk auto-generate.</small>
        </div> --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const planSelect = document.getElementById('plan_id');
                const newPlanContainer = document.getElementById('new_plan_name_container');
                planSelect.addEventListener('change', function() {
                    if (!planSelect.value) {
                        newPlanContainer.style.display = '';
                    } else {
                        newPlanContainer.style.display = 'none';
                    }
                });
            });
        </script>

        <div class="mb-3">
            <label for="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date"
                class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Products</label>
            <input type="text" id="product_search" class="form-control mb-2" placeholder="Search products...">
            <div class="border rounded p-2" style="max-height: 250px; overflow-y: auto;" id="product_list">
                <div class="mb-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="check_all_btn">Check All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="uncheck_all_btn">Uncheck
                        All</button>
                </div>
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th style="width:40px"></th>
                            {{-- <th>Code</th> --}}
                            <th>CO</th>
                            <th>Product</th>
                            {{-- <th>Description</th> --}}
                            <th>Shipment Date</th>
                            <th>CO User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            @foreach ($product->coProducts as $coProduct)
                                <tr
                                    class="{{ collect(old('co_product_ids'))->contains($product->id) ? 'table-success' : '' }}">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="co_product_ids[]"
                                                value="{{ $coProduct->id }}" id="product_{{ $coProduct->id }}"
                                                {{ collect(old('co_product_ids'))->contains($product->coProducts->first()->id ?? '') ? 'checked' : '' }}
                                                onchange="this.closest('tr').classList.toggle('table-success', this.checked)">
                                            {{-- <label class="form-check-label" for="product_{{ $product->id }}">
                                                ({{ $product->code }})
                                            </label> --}}
                                        </div>
                                    </td>
                                    <td>
                                        {{-- @dd($coProduct->co->code) --}}
                                        <div>{{ $coProduct->co->code }}</div>
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    {{-- <td>{{ $product->description }}</td> --}}
                                    <td>
                                        <div>{{ $coProduct->shipment_date }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $coProduct->co->co_user }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('product_ids')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted">Checklist produk yang ingin disimulasikan.</small>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkAllBtn = document.getElementById('check_all_btn');
                const uncheckAllBtn = document.getElementById('uncheck_all_btn');
                checkAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('#product_list .form-check-input').forEach(function(cb) {
                        cb.checked = true;
                        cb.closest('.form-check').classList.add('bg-success', 'bg-opacity-10');
                    });
                    updateSelectedProducts();
                });
                uncheckAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('#product_list .form-check-input').forEach(function(cb) {
                        cb.checked = false;
                        cb.closest('.form-check').classList.remove('bg-success', 'bg-opacity-10');
                    });
                    updateSelectedProducts();
                });
                document.querySelectorAll('#product_list .form-check-input').forEach(function(cb) {
                    cb.addEventListener('change', function() {
                        if (cb.checked) {
                            cb.closest('.form-check').classList.add('bg-success', 'bg-opacity-10');
                        } else {
                            cb.closest('.form-check').classList.remove('bg-success', 'bg-opacity-10');
                        }
                        updateSelectedProducts();
                    });
                });

                // Initial update on page load
                updateSelectedProducts();

                function updateSelectedProducts() {
                    const selected = [];
                    document.querySelectorAll('#product_list .form-check-input:checked').forEach(function(cb) {
                        const label = cb.closest('.form-check').querySelector('label').textContent.trim();
                        selected.push(label);
                    });
                    // If you want to show selected products, implement here
                }
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchBox = document.getElementById('product_search');
                searchBox.addEventListener('input', function() {
                    const filter = searchBox.value.toLowerCase();
                    document.querySelectorAll('#product_list tbody tr').forEach(function(row) {
                        const productName = row.querySelector('td:nth-child(2)')?.textContent
                            .toLowerCase() || '';
                        row.style.display = productName.includes(filter) ? '' : 'none';
                    });
                });
            });
        </script>

        <div class="mb-3">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
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
