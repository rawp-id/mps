@extends('layouts.app')

@section('title', 'Schedules List')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h1>Schedules</h1>
        {{-- dropdown menu filter --}}
        <div class="dropdown">
            <input type="date" class="form-control" id="filterStartDate" name="start_date" placeholder="Start date"
                style="width: 150px; display: inline-block; margin-right: 10px;" value="{{ $startDate }}">
            <input type="date" class="form-control" id="filterEndDate" name="end_date" placeholder="End date"
                style="width: 150px; display: inline-block; margin-right: 10px;" value="{{ $endDate }}">
            <button type="button" class="btn btn-primary" id="filterDateSubmit" style="margin-right: 10px;">
                Apply
            </button>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('filterDateSubmit').addEventListener('click', function() {
                        const startDate = document.getElementById('filterStartDate').value;
                        const endDate = document.getElementById('filterEndDate').value;
                        // Redirect with query parameters for filtering
                        const url = new URL(window.location.href);
                        url.searchParams.set('start_date', startDate);
                        url.searchParams.set('end_date', endDate);
                        window.location.href = url.toString();
                    });
                });
            </script>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown"
                data-bs-toggle="dropdown" aria-expanded="false">
                Filter
            </button>
            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                <li><a class="dropdown-item"
                        href="{{ route('schedules.gantt', ['start_date' => $startDate, 'end_date' => $endDate]) }}">Products</a>
                </li>

                <li class="dropdown-submenu position-relative">
                    <button type="button" class="dropdown-item dropdown-toggle">
                        Machines
                    </button>
                    <ul class="dropdown-menu">
                        @foreach ($machines as $machine)
                            <li>
                                <button type="button" class="dropdown-item machine-link"
                                    data-url="{{ route('schedules.gantt.machine', ['id' => $machine->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                                    {{ $machine->name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </li>


                <li class="dropdown-submenu position-relative">
                    <button type="button" class="dropdown-item dropdown-toggle">
                        Processes
                    </button>
                    <ul class="dropdown-menu">
                        @foreach ($processes as $item)
                            <li>
                                <button type="button" class="dropdown-item machine-link"
                                    data-url="{{ route('schedules.gantt.process', ['id' => $item->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                                    {{ $item->name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    {{-- <div class="mt-5">
        {{ $products->links('pagination::bootstrap-5') }}
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
            const machineMap = new Map();
            schedules.forEach(s => {
                const id = s.machine ? parseInt(s.machine.id) : 0;
                const machineName = s.operation?.machine.name ?? 'Unknown Machine';
                machineMap.set(id, machineName);
            });

            // 2️⃣ Buat array groups terurut ID ASC
            const sortedProducts = Array.from(machineMap.entries())
                .sort((a, b) => a[0] - b[0]); // sort by numeric ID

            sortedProducts.forEach(([id, machineName]) => {
                groups.push({
                    id,
                    content: machineName
                });
            });

            // 3️⃣ Buat items
            schedules.forEach(s => {
                items.push({
                    id: s.id,
                    group: s.machine ? parseInt(s.machine.id) : 0,
                    content: s.product?.name ?? 'Unknown Product',
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

            document.getElementById('editScheduleForm').action = '/schedules/' + scheduleId;
            document.getElementById('edit_quantity').value = schedule.quantity;
            document.getElementById('edit_plan_speed').value = schedule.plan_speed;
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
                <form id="editScheduleForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_plan_speed" class="form-label">Plan Speed</label>
                            <input type="number" class="form-control" id="edit_plan_speed" name="plan_speed" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_start_time" class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control" id="edit_start_time" name="start_time"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_end_time" class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control" id="edit_end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
