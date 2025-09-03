@extends('layouts.app')

@section('title', 'Schedules List')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h1>Schedules</h1>
        {{-- dropdown menu filter --}}
        <!-- Filter Button to trigger modal -->
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                Filter
            </button>
        </div>

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
                                <label for="filterCategory" class="form-label">Kategori</label>
                                <select class="form-select" id="filterCategory" name="category">
                                    <option value="">All</option>
                                    <option value="plan">Plan</option>
                                    <option value="actual">Actual</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="filterMachine" class="form-label">Machine</label>
                                <input type="text" class="form-control mb-2" id="machineSearch"
                                    placeholder="Search machine...">
                                <select class="form-select" id="filterMachine" name="machine_id">
                                    <option value="">All</option>
                                    @foreach ($machines as $machine)
                                        <option value="{{ $machine->id }}">{{ $machine->name }}</option>
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
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
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
                        <button type="button" class="btn btn-primary" id="applyFilterBtn">Apply</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('applyFilterBtn').addEventListener('click', function() {
                    const form = document.getElementById('filterForm');
                    const params = new URLSearchParams(new FormData(form));
                    const machineId = params.get('machine_id');
                    const processId = params.get('process_id');
                    const startDate = params.get('start_date');
                    const endDate = params.get('end_date');

                    if (machineId) {
                        // Redirect to machine-specific gantt route
                        const url =
                            "{{ route('schedules.gantt.machine', ['id' => '__ID__']) }}"
                                .replace('__ID__', machineId) +
                            '?start_date=' + encodeURIComponent(startDate || '') +
                            '&end_date=' + encodeURIComponent(endDate || '');
                        window.location.href = url;
                    } else if (processId) {
                        // Redirect to process-specific gantt route
                        const url =
                            "{{ route('schedules.gantt.process', ['id' => '__ID__']) }}"
                                .replace('__ID__', processId) +
                            '?start_date=' + encodeURIComponent(startDate || '') +
                            '&end_date=' + encodeURIComponent(endDate || '');
                        window.location.href = url;
                    } else {
                        // Default filter logic
                        const url = new URL(window.location.href);
                        ['start_date', 'end_date', 'category', 'machine_id', 'process_id'].forEach(key => url
                            .searchParams.delete(key));
                        params.forEach((value, key) => {
                            if (value) url.searchParams.set(key, value);
                        });
                        window.location.href = url.toString();
                    }
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.dropdown-item[data-coProducts]').forEach(function(item) {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const coProductsId = this.getAttribute('data-coProducts');
                        // TODO: Implement filtering logic for the timeline based on coProductsId
                        // Example: filterTimeline(coProductsId);
                    });
                });
            });
        </script>
    </div>
    {{-- <div class="mt-5">
        {{ $coProductss->links('pagination::bootstrap-5') }}
    </div> --}}

    <div class="mt-3 mb-5">
        {{-- @dd($schedules) --}}
        {{-- <div class="" id="gantt_chart" style="width: 100%;"></div> --}}

        <div id="timeline" style="height: 100%; border: 1px solid #ccc;"></div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const schedules = @json($schedules);
            const groups = [];
            const items = [];

            // 1️⃣ Kumpulkan produk unik dengan ID numerik
            const coProductsMap = new Map();
            schedules.forEach(s => {
                const id = s.coProducts ? parseInt(s.coProducts.id) : 0;
                const name = (s.coProducts?.name?.length > 20) ? s.coProducts.name.substring(0, 20) + '…' : (s
                    .coProducts?.name ?? 'Unknown coProducts');
                coProductsMap.set(id, name);
            });

            // 2️⃣ Buat array groups terurut ID ASC
            // Urutkan produk sesuai urutan kemunculan pertama pada schedules (berdasarkan schedule id)
            const seen = new Set();
            // Sort schedules ascending by schedule id to get coProductss in order of first appearance
            schedules.slice().sort((a, b) => a.id - b.id).forEach(s => {
                const id = s.coProducts ? parseInt(s.coProducts.id) : 0;
                if (!seen.has(id)) {
                    groups.push({
                        id,
                        content: coProductsMap.get(id)
                    });
                    seen.add(id);
                }
            });

            // 3️⃣ Buat items
            schedules.forEach(s => {
                items.push({
                    id: s.id,
                    group: s.coProducts ? parseInt(s.coProducts.id) : 0,
                    content: s.operation?.name ?? 'Process',
                    start: s.start_time,
                    end: s.end_time
                });
            });

            // 4️⃣ Render timeline
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

            timeline.on('select', function(properties) {
                if (properties.items.length > 0) {
                    openEditModal(properties.items[0]);
                }
            });
        });

        function openEditModal(scheduleId) {
            const schedule = @json($schedules->keyBy('id'))[scheduleId];
            if (!schedule) return;

            // document.getElementById('editScheduleForm').action = '/schedules/' + scheduleId;
            document.getElementById('startTimeFields').action = '{{ route('schedules.min', ':id') }}'.replace(':id',
                scheduleId);
            document.getElementById('endTimeFields').action = '{{ route('schedules.add', ':id') }}'.replace(':id',
                scheduleId);
            document.getElementById('edit_start_time').value = schedule.start_time.replace(' ', 'T');
            document.getElementById('edit_end_time').value = schedule.end_time.replace(' ', 'T');

            const modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
            modal.show();
        }
    </script>

    <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{-- <form id="editScheduleForm" method="POST"> --}}
                {{-- @csrf --}}
                <div class="modal-body">

                    <!-- Section 2: Collapsible -->
                    <p class="gap-2">
                        <a class="btn btn-outline-secondary" data-bs-toggle="collapse" href="#collapseTimeFields"
                            role="button" aria-expanded="false" aria-controls="collapseTimeFields">
                            Start Time
                        </a>
                        <a class="btn btn-outline-secondary" data-bs-toggle="collapse" href="#endTimeFields"
                            role="button" aria-expanded="false" aria-controls="endTimeFields">
                            End Time
                        </a>
                    </p>
                    <div class="collapse" id="collapseTimeFields">
                        <div class="card card-body">
                            <form method="POST" id='startTimeFields'>
                                @csrf
                                <div class="mb-3">
                                    <label for="edit_start_time" class="form-label">Start Time</label>
                                    <input type="datetime-local" class="form-control" id="edit_start_time"
                                        name="start_time" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Start Time</button>
                            </form>
                        </div>
                    </div>
                    <div class="collapse" id="endTimeFields">
                        <div class="card card-body">
                            <form method="POST" id='endTimeFields'>
                                @csrf
                                <div class="mb-3">
                                    <label for="edit_end_time" class="form-label">End Time</label>
                                    <input type="datetime-local" class="form-control" id="edit_end_time" name="end_time"
                                        required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update End Time</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div> --}}
                {{-- </form> --}}
            </div>
        </div>
    </div>
@endsection
