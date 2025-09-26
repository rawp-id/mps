@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Edit Employee</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('employees.update', $employee->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                    value="{{ old('name', $employee->name) }}">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $employee->email) }}">
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="{{ old('phone', $employee->phone) }}">
                            </div>
                            <div class="mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position"
                                    value="{{ old('position', $employee->position) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Shifts</label>
                                <div id="shift-rows">
                                    @foreach ($employee->employeeShifts as $i => $empShift)
                                        <div class="shift-row d-flex gap-2 mb-2">
                                            <select name="shifts[{{ $i }}][shift_id]" class="form-select"
                                                required>
                                                <option value="">-- Pilih Shift --</option>
                                                @foreach ($shifts as $shift)
                                                    <option value="{{ $shift->id }}"
                                                        {{ $empShift->id == $shift->id ? 'selected' : '' }}>
                                                        {{ $shift->name ?? $shift->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="text" class="form-control"
                                                name="shifts[{{ $i }}][role]" placeholder="Role"
                                                value="{{ old("shifts.$i.role", $empShift->pivot->role ?? '') }}">
                                            <button type="button"
                                                class="btn btn-sm btn-danger remove-shift {{ $i == 0 ? 'd-none' : '' }}">
                                                &times;
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" id="add-shift" class="btn btn-outline-primary btn-sm mt-2">
                                    + Tambah Shift
                                </button>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addBtn = document.getElementById('add-shift');
            const container = document.getElementById('shift-rows');

            function reindex() {
                [...container.querySelectorAll('.shift-row')].forEach((row, i) => {
                    row.querySelector('select').name = `shifts[${i}][shift_id]`;
                    row.querySelector('input').name = `shifts[${i}][role]`;

                    const rm = row.querySelector('.remove-shift');
                    rm.classList.toggle('d-none', i === 0);
                });
            }

            function addRow() {
                const first = container.querySelector('.shift-row');
                const clone = first.cloneNode(true);

                // reset value
                clone.querySelector('select').selectedIndex = 0;
                clone.querySelector('input').value = '';

                container.appendChild(clone);
                attachRemove(clone.querySelector('.remove-shift'));
                reindex();
            }

            function attachRemove(btn) {
                btn.onclick = () => {
                    btn.closest('.shift-row').remove();
                    reindex();
                };
            }

            container.querySelectorAll('.remove-shift').forEach(attachRemove);
            addBtn.addEventListener('click', addRow);
        });
    </script>
@endsection
