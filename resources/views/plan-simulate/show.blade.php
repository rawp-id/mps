@extends('layouts.app')

@section('title', 'Plan Simulation Detail')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Plan: {{ $plan->name }}</h1>
        <div class="d-flex">
            <a href="{{ route('plan-simulate.edit', $plan->id) }}" class="btn btn-warning me-2">Edit</a>
            <form id="generateForm" action="{{ route('plan-simulate.generate', $plan->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-info me-2">Generate</button>
            </form>
            <a href="{{ route('apply.schedule', $plan->id) }}" class="btn btn-primary me-2">Apply To Schedule</a>
            <a href="{{ route('plan-simulate.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    {{-- <p><strong>Slug:</strong> {{ $plan->slug }}</p> --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <strong>CO-Products:</strong>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCoModal">
            Add
        </button>

        <!-- Modal -->
        <div class="modal fade" id="addCoModal" tabindex="-1" aria-labelledby="addCoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCoModalLabel">Select CO-Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($cos->isEmpty())
                            <div class="alert alert-info">No CO-Products available.</div>
                        @else
                            <form id="addCoForm" method="POST"
                                action="{{ route('plan-simulate.addCoToPlan', $plan->id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="co_id" class="form-label">CO-Product</label>
                                    <input type="text" class="form-control mb-2" id="coSearch"
                                        placeholder="Search CO/Product...">
                                    <table class="table table-bordered table-sm align-middle" id="coTable">
                                        <thead>
                                            <tr>
                                                <th style="width:40px"></th>
                                                <th>Product</th>
                                                <th>Description</th>
                                                <th>CO</th>
                                                <th>Shipping Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cos as $co)
                                                <tr class="{{ collect(old('co_ids'))->contains($co->id) ? 'table-success' : '' }}"
                                                    data-product="{{ strtolower($co->product->name) }}">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="co_ids[]"
                                                                value="{{ $co->id }}" id="co_{{ $co->id }}"
                                                                {{ collect(old('co_ids'))->contains($co->id) ? 'checked' : '' }}
                                                                onchange="this.closest('tr').classList.toggle('table-success', this.checked)">
                                                            <label class="form-check-label" for="co_{{ $co->id }}">
                                                                {{ $co->code }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>{{ $co->product->name }}</td>
                                                    <td>{{ $co->description }}</td>
                                                    <td>{{ $co->co_user }}</td>
                                                    <td>{{ $co->shipping_date }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const searchInput = document.getElementById('coSearch');
                                            const productFilter = document.getElementById('productFilter');
                                            const table = document.getElementById('coTable');

                                            function filterTable() {
                                                const search = searchInput.value.toLowerCase();
                                                const product = productFilter.value;
                                                Array.from(table.tBodies[0].rows).forEach(row => {
                                                    const rowProduct = row.getAttribute('data-product');
                                                    const text = row.innerText.toLowerCase();
                                                    const matchProduct = !product || rowProduct === product;
                                                    const matchText = !search || text.includes(search);
                                                    row.style.display = (matchProduct && matchText) ? '' : 'none';
                                                });
                                            }
                                            searchInput.addEventListener('input', filterTable);
                                            productFilter.addEventListener('change', filterTable);
                                        });
                                    </script>
                                </div>
                                <!-- Add more fields if needed -->
                                <button type="submit" class="btn btn-primary">Add</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($plan->planProductCos->isEmpty())
        <div class="alert alert-info">No products associated with this plan.</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>CO Name</th>
                    <th>Product Name</th>
                    <th>Locked</th>
                    @if (!empty($plan->planProductCos->first()->shipment_date))
                        <th>Shipment Date</th>
                    @endif
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plan->planProductCos as $planProductCo)
                    <tr>
                        <td>{{ $planProductCo->co->name }}</td>
                        <td>{{ $planProductCo->co->product->name }}</td>
                        @if (!empty($planProductCo->shipment_date))
                            <td>{{ $planProductCo->shipment_date }}</td>
                        @endif
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="locked[{{ $planProductCo->id }}]"
                                    value="1" form="generateForm">
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <form action="{{ route('plan-simulate.destroyCoFromPlan', $planProductCo->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p><strong>Description:</strong> {{ $plan->description ?? '-' }}</p>

    <hr>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-3">Gantt Chart</h4>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                Filter
            </button>
            <!-- Filter Modal -->
            <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="filterModalLabel">Filter Schedules</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="filterForm">
                                <div class="mb-3">
                                    <label for="filterStartDate" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="filterStartDate" name="start_date"
                                        value="{{ $startDate }}">
                                </div>
                                <div class="mb-3">
                                    <label for="filterEndDate" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="filterEndDate" name="end_date"
                                        value="{{ $endDate }}">
                                </div>
                                <div class="mb-3">
                                    <label for="filterMachine" class="form-label">Machine</label>
                                    <input type="text" class="form-control mb-2" id="machineSearch"
                                        placeholder="Search machine...">
                                    <select class="form-select" id="filterMachine" name="machine_id">
                                        <option value="">All</option>
                                        @foreach ($machines as $machine)
                                            <option value="{{ $machine->id }}"
                                                {{ request('machine_id') == $machine->id ? 'selected' : '' }}>
                                                {{ $machine->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const searchInput = document.getElementById('machineSearch');
                                        const machineSelect = document.getElementById('filterMachine');
                                        const originalOptions = Array.from(machineSelect.options);

                                        searchInput.addEventListener('input', function() {
                                            const search = this.value.toLowerCase();
                                            machineSelect.innerHTML = '';
                                            originalOptions.forEach(option => {
                                                if (
                                                    option.value === '' ||
                                                    option.text.toLowerCase().includes(search)
                                                ) {
                                                    machineSelect.appendChild(option.cloneNode(true));
                                                }
                                            });
                                        });
                                    });
                                </script>
                                <div class="mb-3">
                                    <label for="filterProcess" class="form-label">Process</label>
                                    <input type="text" class="form-control mb-2" id="processSearch"
                                        placeholder="Search process...">
                                    <select class="form-select" id="filterProcess" name="process_id">
                                        <option value="">All</option>
                                        @foreach ($processes as $item)
                                            <option value="{{ $item->id }}"
                                                {{ request('process_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const processSearchInput = document.getElementById('processSearch');
                                        const processSelect = document.getElementById('filterProcess');
                                        const processOriginalOptions = Array.from(processSelect.options);

                                        processSearchInput.addEventListener('input', function() {
                                            const search = this.value.toLowerCase();
                                            processSelect.innerHTML = '';
                                            processOriginalOptions.forEach(option => {
                                                if (
                                                    option.value === '' ||
                                                    option.text.toLowerCase().includes(search)
                                                ) {
                                                    processSelect.appendChild(option.cloneNode(true));
                                                }
                                            });
                                        });
                                    });
                                </script>
                                <!-- Add more filters as needed -->
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-outline-secondary" id="clearFilterBtn">Clear
                                Filter</button>
                            <button type="button" class="btn btn-primary" id="applyFilterBtn">Apply</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Apply filter
                    document.getElementById('applyFilterBtn').addEventListener('click', function() {
                        const form = document.getElementById('filterForm');
                        const params = new URLSearchParams(new FormData(form));
                        const url = new URL(window.location.href);
                        // Remove old filter params
                        ['start_date', 'end_date', 'category', 'machine_id', 'process_id'].forEach(key => url
                            .searchParams.delete(key));
                        // Add new filter params
                        params.forEach((value, key) => {
                            if (value) url.searchParams.set(key, value);
                        });
                        window.location.href = url.toString();
                    });

                    // Clear filter
                    document.getElementById('clearFilterBtn').addEventListener('click', function() {
                        const url = new URL(window.location.href);
                        // Remove all filter params
                        ['start_date', 'end_date', 'category', 'machine_id', 'process_id'].forEach(key => url
                            .searchParams.delete(key));
                        window.location.href = url.toString();
                    });
                });
            </script>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('.dropdown-item[data-product]').forEach(function(item) {
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            const productId = this.getAttribute('data-product');
                            // TODO: Implement filtering logic for the timeline based on productId
                            // Example: filterTimeline(productId);
                        });
                    });
                });
            </script>
        </div>
        <div id="timeline" style="width: 100%; height: 400px; border: 1px solid #ccc;"></div>
    </div>

    {{-- vis.js --}}
    <link href="https://unpkg.com/vis-timeline@latest/dist/vis-timeline-graph2d.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/vis-timeline@latest/dist/vis-timeline-graph2d.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let schedules = @json($schedules);

            const groups = [];
            const items = [];

            // Kumpulkan mesin unik
            const productMap = new Map();
            schedules.forEach(s => {
                const id = s.product ? parseInt(s.product.id) : 0;
                let name = '';
                // Jika ada filter process_id atau machine_id di URL, tambahkan ke nama
                const urlParams = new URLSearchParams(window.location.search);
                const processId = urlParams.get('process_id');
                const machineId = urlParams.get('machine_id');

                if (s.operation?.process && processId && s.operation.process.id == processId) {
                    name = s.operation.process.name;
                } else if (s.machine && machineId && s.machine.id == machineId) {
                    name = s.machine.name;
                } else {
                    name = (s.product?.name?.length > 20) ?
                        s.product.name.substring(0, 20) + '…' :
                        (s.product?.name ?? 'Unknown Product');
                }
                productMap.set(id, name);
            });

            // Buat array groups terurut ID ASC
            const seen = new Set();
            schedules.slice().sort((a, b) => a.id - b.id).forEach(s => {
                const id = s.product ? parseInt(s.product.id) : 0;
                if (!seen.has(id)) {
                    groups.push({
                        id,
                        content: productMap.get(id)
                    });
                    seen.add(id);
                }
            });

            // Buat items
            schedules.forEach(s => {
                items.push({
                    id: s.id,
                    group: s.product ? parseInt(s.product.id) : 0,
                    content: (() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const processId = urlParams.get('process_id');
                        const machineId = urlParams.get('machine_id');
                        if (processId && s.operation?.process && s.operation.process.id ==
                            processId) {
                            return s.product?.name ?? 'Product';
                        } else if (machineId && s.machine && s.machine.id == machineId) {
                            return s.machine.name;
                        } else {
                            return s.operation?.name ?? 'Process';
                        }
                    })(),
                    start: s.start_time,
                    end: s.end_time,
                    locked: s.locked ?? false,
                    duration: s.duration ?? '',
                    plan_duration: s.plan_duration ?? '',
                });
            });

            // Render timeline
            const container = document.getElementById('timeline');
            const options = {
                stack: false,
                showMajorLabels: true,
                showCurrentTime: true,
                zoomMin: 1000 * 60 * 60, // 1 hour
                zoomMax: 1000 * 60 * 60 * 24 * 30, // 1 month
                orientation: 'top'
            };

            const timeline = new vis.Timeline(container);
            timeline.setOptions(options);
            timeline.setGroups(new vis.DataSet(groups));
            timeline.setItems(new vis.DataSet(items));

            // Modal HTML
            const modalHtml = `
            <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                <form id="editScheduleForm">
                  <div class="mb-3">
                    <label for="editDuration" class="form-label">Duration (Default - <span id="planDurationLabel"></span> Menit)</label>
                    <input type="text" class="form-control" id="editDuration" name="duration">
                  </div>
                  <input type="hidden" id="editScheduleId" name="id">
                </form>
                  </div>
                  <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveScheduleBtn">Save</button>
                  </div>
                </div>
              </div>
            </div>
            `;
            if (!document.getElementById('editScheduleModal')) {
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }

            timeline.on('select', function(properties) {
                if (properties.items.length > 0) {
                    const selectedItem = items.find(item => item.id === properties.items[0]);
                    // console.log('Selected item:', selectedItem);
                    // Isi modal dengan data
                    document.getElementById('editScheduleId').value = selectedItem.id;
                    document.getElementById('editDuration').value = selectedItem.duration;
                    document.getElementById('editLocked').checked = !!selectedItem.locked;
                    document.getElementById('planDurationLabel').innerText = selectedItem.plan_duration ||
                        'N/A';
                    // Tampilkan modal
                    const modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
                    modal.show();
                }
            });

            // Simpan perubahan
            document.getElementById('saveScheduleBtn').addEventListener('click', function() {
                const id = document.getElementById('editScheduleId').value;
                const duration = document.getElementById('editDuration').value;
                const locked = document.getElementById('editLocked').checked;
                // TODO: Kirim ke backend via AJAX/fetch jika ingin simpan ke server
                // Untuk demo, update di frontend saja
                const idx = items.findIndex(item => item.id == id);
                if (idx !== -1) {
                    items[idx].duration = duration;
                    items[idx].locked = locked;
                    timeline.setItems(new vis.DataSet(items));
                }
                // Tutup modal
                bootstrap.Modal.getInstance(document.getElementById('editScheduleModal')).hide();
            });
        });
    </script>
@endsection
